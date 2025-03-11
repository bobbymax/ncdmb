<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, File};
use Illuminate\Support\Str;

class ApiServiceController extends Controller
{
    use ApiResponse;

    public function index(): \Illuminate\Http\JsonResponse
    {
        $services = Cache::remember('services_list', 3600, function () {
            $servicesPath = app_path('Services');
            $services = [];

            if (File::isDirectory($servicesPath)) {
                $files = File::files($servicesPath);

                foreach ($files as $file) {
                    $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                    if (str_ends_with($filename, 'Service')) {
                        $serviceName = str_replace('Service', '', $filename);
                        $services[] = Str::snake($serviceName);
                    }
                }
            }

            return $services;
        });

        return $this->success($services);
    }
}
