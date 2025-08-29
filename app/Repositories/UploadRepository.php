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

    public function encodeFile(UploadedFile $file): string
    {
        $content = file_get_contents($file->getRealPath());
        return $this->scramble($content, Auth::user());
    }

    public function prepareUpload(
        UploadedFile $file,
        string $encoded,
        int $uploadableId,
        string $uploadableType
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
            'path' => 'some-string'
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
            $tmpFile = tmpfile();
            $tmpPath = stream_get_meta_data($tmpFile)['uri'];

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

    public function uploadMany(
        array $files,
        int $uploadableId,
        string $uploadableType
    ): void {
        $uploads = [];

        foreach ($files as $file) {

            $normalized = $this->normalizeFile($file);

            if (!$normalized) {
                continue; // skip invalid file
            }

            if ($normalized instanceof UploadedFile) {
                $scrambled = $this->encodeFile($normalized);
                $uploads[] = $this->prepareUpload(
                    $normalized,
                    $scrambled,
                    $uploadableId,
                    $uploadableType
                );
            }
        }

        if (!empty($uploads)) {
            $this->insert($uploads);
        }
    }
}
