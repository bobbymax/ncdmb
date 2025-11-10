<?php

namespace App\Services;

use App\Repositories\ProjectRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectService extends BaseService
{
    public function __construct(ProjectRepository $projectRepository)
    {
        parent::__construct($projectRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            // Existing rules
            'user_id' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'threshold_id' => 'required|integer|exists:thresholds,id',
            'project_category_id' => 'required|integer|exists:project_categories,id',
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'total_proposed_amount' => 'nullable|numeric|min:0',
            'sub_total_amount' => 'nullable|numeric|min:0',
            'service_charge_percentage' => 'nullable|integer|min:0|max:100',
            'markup_amount' => 'nullable|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
            'proposed_start_date' => 'nullable|date',
            'proposed_end_date' => 'nullable|date|after_or_equal:proposed_start_date',
            'type' => 'required|string|in:staff,third-party',
            'status' => 'required|string|in:pending,registered,approved,denied,kiv,discussed',

            // New rules - Classification
            'project_type' => 'required|string|in:capital,operational,maintenance,research,infrastructure',
            'priority' => 'required|string|in:critical,high,medium,low',
            'strategic_alignment' => 'nullable|string|max:1000',

            // New rules - Financial
            'fund_id' => 'nullable|integer',
            'budget_year' => 'nullable|string|max:20',

            // Existing but enhanced
            'variation_amount' => 'nullable|numeric',
            'total_approved_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function store(array $data)
    {
        return parent::store([
            ...$data,
            'code' => $this->generate('code', 'PROJ')
        ]);
    }

    public function resolveDocumentAmount(int $resourceId)
    {
        $project = $this->repository->find($resourceId);

        Log::info(
            'project_name: ' . $project->title,
        );

        if (!$project) {
            return null;
        }

        return $this->updateDocumentAmount($project, 'total_revised_amount', [
            'sub_total_amount' => $project->sub_total_amount,
            'markup_amount' => $project->markup_amount,
            'vat_amount' => $project->vat_amount,
        ]);
    }

    public function resolveContent(array $data): array
    {
        $resolvedContents = [
            'total_revised_amount' => 0,
            'sub_total_amount' => 0,
            'vat_amount' => 0,
            'markup_amount' => 0,
            'admin_fee_revised_markup' => 0
        ];

        foreach ($data['content'] as $item) {
            if (isset($item['content']['invoice'])) {
                $totals = $item['content']['invoice']['totals'];
                $settings = $item['content']['invoice']['settings'];

                $resolvedContents['total_revised_amount'] = $totals['grandTotal'];
                $resolvedContents['sub_total_amount'] = $totals['subTotal'];
                $resolvedContents['vat_amount'] = $totals['vat'];
                $resolvedContents['markup_amount'] = $totals['adminFee'];
                $resolvedContents['admin_fee_revised_markup'] = $settings['adminFee'];
            }
        }

        return $resolvedContents;
    }

    public function buildDocumentFromTemplate(array $data, bool $isUpdate = false)
    {
        return DB::transaction(function () use ($data, $isUpdate) {
            $project = parent::show($data['model']['id']);

            if (!$project) {
                return null;
            }

            $resolvedContents = $this->resolveContent($data);

            $project->update([
                'status' => 'registered',
                'lifecycle_stage' => 'feasibility',
                'fund_id' => $data['fund_id'] ?? $project->fund_id,
                'total_revised_amount' => $resolvedContents['total_revised_amount'] > 0 ? $resolvedContents['total_revised_amount'] : $project->total_proposed_amount,
                'sub_total_amount' => $resolvedContents['sub_total_amount'] > 0 ? $resolvedContents['sub_total_amount'] : $project->sub_total_amount,
                'vat_amount' => $resolvedContents['vat_amount'] > 0 ? $resolvedContents['vat_amount'] : $project->vat_amount,
                'markup_amount' => $resolvedContents['markup_amount'] > 0 ? $resolvedContents['markup_amount'] : $project->markup_amount,
                'service_charge_percentage' => $resolvedContents['admin_fee_revised_markup'] > 0 ? $resolvedContents['admin_fee_revised_markup'] : $project->service_charge_percentage,
            ]);

            return $project;
        });
    }
}
