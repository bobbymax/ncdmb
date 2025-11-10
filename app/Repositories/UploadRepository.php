<?php

namespace App\Repositories;

use App\Models\Upload;
use App\Traits\DocumentFlow;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadRepository extends BaseRepository
{
    use DocumentFlow;

    public function __construct(Upload $upload) {
        parent::__construct($upload);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id
        ];
    }

    public function encodeFile(UploadedFile $file): array
    {
        $path = $file->getRealPath() ?: $file->getPathname();
        $content = file_get_contents($path);
        $hash = hash('sha256', $content);
        return [
            'encoded' => $this->scramble($content, Auth::user()),
            'hash'    => $hash,
        ];
    }

    public function prepareUpload(
        UploadedFile $file,
        string $encoded,
        int $uploadableId,
        string $uploadableType,
        ?string $hash = null
    ): array {
        return [
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'name' => $file->getClientOriginalName(),
            'file_path' => $encoded,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'uploadable_id' => $uploadableId,
            'uploadable_type' => $uploadableType,
            'created_at' => now(),
            'updated_at' => now(),
            'path' => 'some-string',
            'file_hash' => $hash,
        ];
    }

    public function normalizeFile($file): ?UploadedFile
    {
        // Case 1: Already an UploadedFile
        if ($file instanceof UploadedFile) {
            return $file;
        }

        // Case 2: Base64 / Data URL
        if (is_string($file) && preg_match('/^data:.*;base64,/', $file)) {
            [$meta, $content] = explode(',', $file);

            // Decode the base64 string
            $binaryData = base64_decode($content);

            // Detect MIME type from the binary data
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($binaryData);

            // Restrict to allowed types
            $allowed = [
                'application/pdf' => 'pdf',
                'image/jpeg'      => 'jpg',
                'image/png'       => 'png',
            ];

            if (!array_key_exists($mimeType, $allowed)) {
                return null;
            }

            $extension = $allowed[$mimeType];

            // Create temporary file
//            $tmpFile = tmpfile();
//            $tmpPath = stream_get_meta_data($tmpFile)['uri'];

            // Create a persistent temp file path
            $tmpPath = tempnam(sys_get_temp_dir(), 'upload_');
            if ($tmpPath === false) return null;

            // Write the binary content
            file_put_contents($tmpPath, $binaryData);

            return new UploadedFile(
                $tmpPath,
                'upload.' . $extension,
                $mimeType,
                null,
                true // mark as "test" file
            );
        }

        return null;
    }

    protected function extractHashFromPayload(string $payload): ?string
    {
        $chunks = json_decode($payload, true);
        if (!is_array($chunks)) {
            return null;
        }

        foreach ($chunks as $chunk) {
            if (is_string($chunk) && str_starts_with($chunk, 'hash:')) {
                return substr($chunk, 5);
            }
        }

        return null;
    }

    public function uploadMany(
        array $files,
        int $uploadableId,
        string $uploadableType
    ): void {
        $uploads = [];

        if (!empty($files)) {
            $existingHashes = $this->model->newQuery()
                ->where('uploadable_id', $uploadableId)
                ->where('uploadable_type', $uploadableType)
                ->get(['file_path'])
                ->map(fn($upload) => $this->extractHashFromPayload($upload->file_path))
                ->filter()
                ->values()
                ->all();

            foreach ($files as $file) {

                $normalized = $this->normalizeFile($file);
                if (!$normalized) continue;

                try {
                    $encodedPayload = $this->encodeFile($normalized);
                    $encoded = $encodedPayload['encoded'];
                    $hash = $encodedPayload['hash'];

                    if ($hash && in_array($hash, $existingHashes, true)) {
                        continue;
                    }

                    $uploads[] = $this->prepareUpload(
                        $normalized,
                        $encoded,
                        $uploadableId,
                        $uploadableType,
                        $hash
                    );

                    if ($hash) {
                        $existingHashes[] = $hash;
                    }
                } finally {
                    // If normalizeFile() produced a tempnam file, clean it up
                    // Heuristic: only unlink if it's in the system temp dir and still exists
                    $p = $normalized->getRealPath() ?: $normalized->getPathname();
                    if ($p && str_starts_with($p, sys_get_temp_dir()) && is_file($p)) {
                        @unlink($p);
                    }
                }
            }

            if (!empty($uploads)) {
                $this->insert($uploads);
            }
        }
    }
}
