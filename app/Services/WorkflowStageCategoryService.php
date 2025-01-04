<?php

namespace App\Services;

use App\Models\WorkflowStageCategory;
use App\Repositories\UploadRepository;
use App\Repositories\WorkflowStageCategoryRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkflowStageCategoryService extends BaseService
{
    protected UploadRepository $uploadRepository;
    public function __construct(
        WorkflowStageCategoryRepository $workflowStageCategoryRepository,
        UploadRepository $uploadRepository
    ) {
        parent::__construct($workflowStageCategoryRepository);
        $this->uploadRepository = $uploadRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string|min:5',
            'icon_path_blob' => 'required|mimes:jpeg,jpg,png|max:1024',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $workflowStageCategory = parent::store($data);

            if (!$workflowStageCategory) {
                return null;
            }

            if (!$data['icon_path_blob'] instanceof UploadedFile) {
                return null;
            }

            $path = $this->processUpload($data['icon_path_blob'], $workflowStageCategory->id);

            $workflowStageCategory->icon_path = $path;
            $workflowStageCategory->save();

            return $workflowStageCategory;
        });
    }

    /**
     * @throws \Exception
     */
    protected function processUpload($file, $categoryId)
    {
        $uniqueFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('documents/stage-categories', $uniqueFileName, 'public');

        $this->uploadRepository->create([
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'name' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'uploadable_id' => $categoryId,
            'uploadable_type' => WorkflowStageCategory::class,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $storedPath;
    }
}
