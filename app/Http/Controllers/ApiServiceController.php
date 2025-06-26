<?php

namespace App\Http\Controllers;

use App\Models\ResourceEditor;
use App\Services\ProgressTrackerService;
use App\Services\ResourceEditorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, File, Log};
use Illuminate\Support\Str;

class ApiServiceController extends Controller
{
    public function __construct(
        protected ResourceEditorService $resourceEditorService,
        protected ProgressTrackerService $progressTrackerService,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->getCachedClassList('Services', 'Service', 'services_list'));
    }

    public function imports(): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->getCachedClassList('Imports', 'Import', 'imports_list'));
    }

    public function fetchEditor($resource, $trackerId): \Illuminate\Http\JsonResponse
    {
        $tracker = $this->progressTrackerService->show($trackerId);

        if (!$tracker) {
            return $this->error(null, 'This tracker does not exist.', 500);
        }

        $editor = ResourceEditor::where('group_id', $tracker->group_id)
            ->where('service_name', $resource)
            ->where('workflow_id', $tracker->workflow_id)
            ->where('workflow_stage_id', $tracker->workflow_stage_id)
            ->first();

        return $this->success($editor);
    }

    public function records(
        $status,
        $access_level,
        $user_column = "user_id",
        $draftScope = "linked"
    ): \Illuminate\Http\JsonResponse {
        return $this->success(
            $this->resolveResource(
                $status,
                $access_level,
                $user_column,
                $draftScope
            )
        );
    }

    /**
     * Fetch cached list of class names in a given directory.
     */
    private function getCachedClassList(string $folder, string $suffix, string $cacheKey): array
    {
        return Cache::remember($cacheKey, 3600, function () use ($folder, $suffix) {
            $path = app_path($folder);
            $result = [];

            if (!File::isDirectory($path)) {
                return $result;
            }

            $files = File::files($path);

            foreach ($files as $file) {
                $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                if (str_ends_with($filename, $suffix)) {
                    $baseName = str_replace($suffix, '', $filename);
                    $result[] = Str::snake($baseName);
                }
            }

            return $result;
        });
    }
}
