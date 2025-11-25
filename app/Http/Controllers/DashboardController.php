<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard analytics data
     */
    public function index(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getDashboardData();
            return $this->success($data, 'Dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}

