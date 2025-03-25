<?php

namespace App\Repositories;

use App\Models\Upload;
use App\Traits\DocumentFlow;
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

    public function uploadMany(
        array $files,
        int $uploadableId,
        string $uploadableType
    ): void {
        $uploads = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $scrambled = $this->encodeFile($file);
                $uploads[] = $this->prepareUpload(
                    $file,
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
