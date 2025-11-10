<?php

namespace App\Http\Controllers;


use App\Http\Resources\ProjectProgramResource;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectProgramService;
use App\Models\ProjectProgram;
use Illuminate\Http\JsonResponse;

class ProjectProgramController extends BaseController
{
    public function __construct(ProjectProgramService $projectProgramService) {
        parent::__construct($projectProgramService, 'ProjectProgram', ProjectProgramResource::class);
    }

    /**
     * Get all phases (projects) for a program
     */
    public function phases($id): JsonResponse
    {
        $program = ProjectProgram::with('phases')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => ProjectResource::collection($program->phases),
            'meta' => [
                'total_phases' => $program->total_phases,
                'active_phases' => $program->active_phases,
                'completed_phases' => $program->completed_phases,
            ]
        ]);
    }

    /**
     * Recalculate program financials and progress
     */
    public function recalculate($id): JsonResponse
    {
        $program = ProjectProgram::findOrFail($id);
        
        $program->recalculateFinancials();
        $program->recalculateProgress();
        
        return response()->json([
            'success' => true,
            'message' => 'Program financials and progress recalculated successfully',
            'data' => new ProjectProgramResource($program->fresh())
        ]);
    }
}
