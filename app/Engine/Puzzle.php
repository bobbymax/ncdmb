<?php

namespace App\Engine;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class Puzzle
{
    protected const VERSION = 'v1';
    protected const EXPIRY_MINUTES = 10;

    /**
     * Encrypt and obfuscate content with a hidden dynamic tag prefix.
     */
    public static function scramble(string $fileContent, string|int $uploader): string
    {
        $prefix = Str::random(5); // Dynamic prefix e.g., 'a9x0c'

        $encoded = base64_encode($fileContent);
        $encrypted = Crypt::encryptString($encoded);
        $encryptedBase64 = base64_encode($encrypted);

        $chunks = self::splitIntoChunks($encryptedBase64, 4);
        $taggedChunks = collect($chunks)->map(fn($chunk, $i) => "{$prefix}{$i}_{$chunk}")->toArray();

        $hash = hash('sha256', $fileContent);
        $decoys = self::generateDecoys($uploader, $hash, $prefix);

        $mixed = collect($taggedChunks)
            ->merge($decoys)
            ->shuffle()
            ->values()
            ->toArray();

//        Log::info('🎭 Puzzle created with hidden prefix.', ['prefix' => $prefix, 'count' => count($mixed)]);

        return json_encode($mixed);
    }

    /**
     * Rebuild and decrypt the payload by extracting the tag prefix internally.
     */
    public static function resolve(string $dataString): string
    {
        $chunks = json_decode($dataString, true);

        if (!is_array($chunks)) {
            throw new RuntimeException('Invalid or corrupted JSON payload.');
        }

        self::checkVersion($chunks);
//        self::checkExpiry($chunks);

        $prefix = self::extractPrefix($chunks);
        if (!$prefix) {
            throw new RuntimeException('Missing prefix in payload.');
        }

        $hashValue = self::extractHash($chunks);
        if (!$hashValue) {
            throw new RuntimeException('Missing integrity hash.');
        }

        $encryptedBase64 = self::extractEncryptedString($chunks, $prefix);
        $encryptedString = base64_decode($encryptedBase64);

        if ($encryptedString === false) {
            throw new RuntimeException('Encrypted base64 payload is corrupted.');
        }

        try {
            $decodedBase64 = Crypt::decryptString($encryptedString);
        } catch (\Exception $e) {
            throw new RuntimeException('Decryption failed. Payload is invalid or corrupted.');
        }

        $originalFile = base64_decode($decodedBase64);

        if ($originalFile === false) {
            throw new RuntimeException('Final base64 decoding failed.');
        }

        if (hash('sha256', $originalFile) !== $hashValue) {
            throw new RuntimeException('Integrity check failed. Payload may be tampered.');
        }

        return $originalFile;
    }

    protected static function splitIntoChunks(string $string, int $chunkCount): array
    {
        $chunkSize = ceil(strlen($string) / $chunkCount);
        return str_split($string, $chunkSize);
    }

    protected static function generateDecoys(string|int $uploader, string $hash, string $prefix): array
    {
        return [
            "version:" . self::VERSION,
            "expires:" . now()->addMinutes(self::EXPIRY_MINUTES)->timestamp,
            "uploaded-by:{$uploader}",
            Str::uuid(),
            'timestamp:' . now()->timestamp,
            'file-type:secure',
            "hash:{$hash}",
            "prefix:{$prefix}",
        ];
    }

    protected static function extractHash(array $chunks): ?string
    {
        foreach ($chunks as $chunk) {
            if (str_starts_with($chunk, 'hash:')) {
                return Str::after($chunk, 'hash:');
            }
        }
        return null;
    }

    protected static function extractPrefix(array $chunks): ?string
    {
        foreach ($chunks as $chunk) {
            if (str_starts_with($chunk, 'prefix:')) {
                return Str::after($chunk, 'prefix:');
            }
        }
        return null;
    }

    protected static function extractEncryptedString(array $chunks, string $prefix): string
    {
        return collect($chunks)
            ->filter(fn($chunk) => str_starts_with($chunk, $prefix))
            ->mapWithKeys(function ($chunk) use ($prefix) {
                preg_match("/^{$prefix}(\d+)_/", $chunk, $matches);
                $index = (int) ($matches[1] ?? 0);
                $data = Str::after($chunk, "{$prefix}{$index}_");
                return [$index => $data];
            })
            ->sortKeys()
            ->implode('');
    }

    protected static function checkExpiry(array $chunks): void
    {
        foreach ($chunks as $chunk) {
            if (str_starts_with($chunk, 'expires:')) {
                $expiresAt = (int) Str::after($chunk, 'expires:');
                if (now()->timestamp > $expiresAt) {
                    throw new RuntimeException('Payload has expired.');
                }
                return;
            }
        }
    }

    protected static function checkVersion(array $chunks): void
    {
        foreach ($chunks as $chunk) {
            if (str_starts_with($chunk, 'version:')) {
                $version = Str::after($chunk, 'version:');
                if ($version !== self::VERSION) {
                    throw new RuntimeException("Unsupported version: {$version}");
                }
                return;
            }
        }
    }
}
