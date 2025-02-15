<?php

namespace App\Repositories;

use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadRepository extends BaseRepository
{
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

    public function uploadSignature(string $dataUrl): string
    {
        if ($dataUrl === "") {
            return "";
        }

        $fileData = explode(',', $dataUrl);
        $decodedData = base64_decode($fileData[1]);
        $fileName = uniqid() . '.png';
        Storage::disk('public')->put("signatures/$fileName", $decodedData);

        return "signatures/$fileName";
    }

    public function removeFile(string $filePath): bool
    {
        return Storage::disk('public')->delete($filePath);
    }
}
