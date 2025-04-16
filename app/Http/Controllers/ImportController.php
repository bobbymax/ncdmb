<?php

namespace App\Http\Controllers;

use App\Jobs\ImportResourcesJob;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    use ApiResponse;

    protected const __BASE_IMPORT_DIR__ = "App\\Imports\\";
    protected const __JOB_PREFIX__ = "Import";

    public function getResource(Request $request, $resource): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Please fix the following errors', 422);
        }

        $resource = Str::studly($resource);
        $importClass = $this->resolveImport($resource);

        if (!$importClass) {
            return $this->error(null, "Import class not found", 500);
        }

        $chunks = array_chunk($request->data, 500);

        foreach ($chunks as $chunk) {
            ImportResourcesJob::dispatch($importClass, $chunk);
        }

        return $this->success("Sorting");
    }

    public function resolveImport(string $resource)
    {
        $importClass = self::__BASE_IMPORT_DIR__ . $resource . self::__JOB_PREFIX__;

        if (!class_exists($importClass)) {
            return null;
        }

        return app($importClass);
    }
}
