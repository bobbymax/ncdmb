<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, File, Log};
use Illuminate\Support\Str;

class ApiServiceController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->getCachedClassList('Services', 'Service', 'services_list'));
    }

    public function imports(): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->getCachedClassList('Imports', 'Import', 'imports_list'));
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
