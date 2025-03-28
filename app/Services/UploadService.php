<?php

namespace App\Services;

use App\Engine\Puzzle;
use App\Models\Upload;
use App\Repositories\UploadRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadService extends BaseService
{
    public function __construct(UploadRepository $uploadRepository)
    {
        parent::__construct($uploadRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'size' => 'required|integer|between:1,4096',
            'mime_type' => 'required|string|max:255',
            'extension' => 'required|string|max:255',
            'uploadable_id' => 'required|integer|min:1',
            'uploadable_type' => 'required|string|max:255',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $upload = parent::store([
                ...$data,
                'file_path' => Puzzle::scramble($data['file_path'], Auth::user()->staff_no),
                'uploadable_type' => $this->getModelClass($data['uploadable_type']),
            ]);

            if (!$upload) {
                return null;
            }

            return $upload;
        });
    }

    private function getModelClass(string $type): string
    {
        $modelClass = 'App\\Models\\' . $type;

        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} not found");
        }

        return $modelClass;
    }
}
