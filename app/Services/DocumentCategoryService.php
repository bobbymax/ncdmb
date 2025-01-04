<?php

namespace App\Services;

use App\Repositories\DocumentCategoryRepository;
use App\Repositories\DocumentRequirementRepository;
use Illuminate\Support\Facades\DB;

class DocumentCategoryService extends BaseService
{
    protected DocumentRequirementRepository $documentRequirementRepository;
    public function __construct(DocumentCategoryRepository $documentCategoryRepository, DocumentRequirementRepository $documentRequirementRepository)
    {
        parent::__construct($documentCategoryRepository);
        $this->documentRequirementRepository = $documentRequirementRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'document_type_id' => 'required|integer|exists:document_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|sometimes|string|min:3',
            'icon' => 'nullable|sometimes|string',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $category = parent::store($data);

            if ($category && !empty($data['requirements'])) {
                // Fetch all requirements in a single query
                $requirementIds = array_column($data['requirements'], 'value');
                $requirements = $this->documentRequirementRepository
                    ->whereIn('id', $requirementIds)
                    ->get();

                // Prepare new relationships
                $newRequirements = $requirements->filter(function ($requirement) use ($category) {
                    return !$requirement->categories->contains('id', $category->id);
                });

                // Attach new relationships
                if ($newRequirements->isNotEmpty()) {
                    $category->requirements()->saveMany($newRequirements);
                }
            }

            return $category;
        });
    }
}
