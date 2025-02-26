<?php

namespace App\Services;

use App\Repositories\DocumentUpdateRepository;
use Illuminate\Support\Facades\DB;

class DocumentUpdateService extends BaseService
{
    public function __construct(DocumentUpdateRepository $documentUpdateRepository)
    {
        parent::__construct($documentUpdateRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'document_draft_id' => 'required|integer|exists:document_drafts,id',
            'document_action_id' => 'required|integer|exists:document_actions,id',
            'threads' => 'nullable|sometimes|array',
            'comment' => 'nullable|string|min:0',
            'document_reference_id' => 'sometimes|integer|min:0',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $documentUpdate = parent::store($data);

            if (!$documentUpdate) {
                return null;
            }

            if (isset($data['document_reference_id']) && $data['document_reference_id'] > 0) {
                $documentUpdate->draft->document->update([
                    'document_reference_id' => $data['document_reference_id']
                ]);
            }

            return $documentUpdate;
        });
    }
}
