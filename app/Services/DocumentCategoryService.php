<?php

namespace App\Services;

use App\Repositories\BlockRepository;
use App\Repositories\DocumentCategoryRepository;
use App\Repositories\DocumentRequirementRepository;
use App\Repositories\SignatoryRepository;
use Illuminate\Support\Facades\DB;

class DocumentCategoryService extends BaseService
{
    protected DocumentRequirementRepository $documentRequirementRepository;
    protected BlockRepository $blockRepository;
    protected SignatoryRepository $signatoryRepository;
    public function __construct(
        DocumentCategoryRepository $documentCategoryRepository,
        DocumentRequirementRepository $documentRequirementRepository,
        BlockRepository $blockRepository,
        SignatoryRepository $signatoryRepository
    ) {
        parent::__construct($documentCategoryRepository);
        $this->documentRequirementRepository = $documentRequirementRepository;
        $this->blockRepository = $blockRepository;
        $this->signatoryRepository = $signatoryRepository;
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
            'service' => 'nullable|sometimes|string|max:255',
            'workflow_id' => 'nullable|integer|min:0',
            'config' => 'nullable|sometimes|array',
            'meta_data' => 'nullable|sometimes|array',
            'content' => 'nullable|sometimes|array',
            'signature_type' => 'required|in:none,flex,boxed,flushed,stacked',
            'with_date' => 'nullable|sometimes|boolean',
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

    public function addSignatories(array $data)
    {
        return DB::transaction(function () use ($data) {
            $category = parent::show($data['document_category_id']);

            if (!$category) {
                return null;
            }

            foreach ($data['signatories'] as $obj) {

                if ($obj['id'] > 0) {
                    $signatory = $this->signatoryRepository->find($obj['id']);
                    if ($signatory) {
                        $this->signatoryRepository->update($signatory->id, [
                            ...$obj,
                            'user_id' => $obj['user_id'] > 0 ? $obj['user_id'] : null,
                        ]);
                    }
                } else {
                    unset($obj['id']);

                    $this->signatoryRepository->create([
                        ...$obj,
                        'document_category_id' => $category->id,
                        'user_id' => $obj['user_id'] > 0 ? $obj['user_id'] : null,
                    ]);
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
