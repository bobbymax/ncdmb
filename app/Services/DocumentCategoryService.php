<?php

namespace App\Services;

use App\Repositories\BlockRepository;
use App\Repositories\DocumentCategoryRepository;
use App\Repositories\DocumentRequirementRepository;
use Illuminate\Support\Facades\DB;

class DocumentCategoryService extends BaseService
{
    protected DocumentRequirementRepository $documentRequirementRepository;
    protected BlockRepository $blockRepository;
    public function __construct(
        DocumentCategoryRepository $documentCategoryRepository,
        DocumentRequirementRepository $documentRequirementRepository,
        BlockRepository $blockRepository
    ) {
        parent::__construct($documentCategoryRepository);
        $this->documentRequirementRepository = $documentRequirementRepository;
        $this->blockRepository = $blockRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'document_type_id' => 'required|integer|exists:document_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
            'icon' => 'nullable|sometimes|string',
            'selectedRequirements' => 'nullable|sometimes|array',
            'selectedBlocks' => 'nullable|sometimes|array',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $category = parent::store($data);

            if ($category) {
                if (!empty($data['selectedRequirements'])) {
                    $this->updateRequirements($category, $data['selectedRequirements']);
                }

                if (!empty($data['selectedBlocks'])) {
                    $this->updateBlocks($category, $data['selectedBlocks']);
                }
            }

            return $category;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data) {
            $category = parent::update($id, $data);

            if ($category) {
                if (!empty($data['selectedRequirements'])) {
                    $this->updateRequirements($category, $data['selectedRequirements']);
                }

                if (!empty($data['selectedBlocks'])) {
                    $this->updateBlocks($category, $data['selectedBlocks']);
                }
            }

            return $category;
        });
    }

    private function updateBlocks($category, array $blocks): void
    {
        // Fetch all requirements in a single query
        $blockIds = array_column($blocks, 'value');
        $blocks = $this->blockRepository->whereIn('id', $blockIds);

        if ($blocks->isNotEmpty()) {
            $ids = $blocks->pluck('id')->toArray();
            $category->blocks()->sync($ids);
        }
    }

    private function updateRequirements($category, array $requestRequirements): void
    {
        // Fetch all requirements in a single query
        $requirementIds = array_column($requestRequirements, 'value');
        $requirements = $this->documentRequirementRepository
            ->whereIn('id', $requirementIds);

        if ($requirements->isNotEmpty()) {
            $ids = $requirements->pluck('id')->toArray();
            $category->requirements()->sync($ids);
        }
    }
}
