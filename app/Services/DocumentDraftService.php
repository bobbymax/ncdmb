<?php

namespace App\Services;

use App\Repositories\DocumentDraftRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentDraftService extends BaseService
{
    public function __construct(DocumentDraftRepository $documentDraftRepository)
    {
        parent::__construct($documentDraftRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'document_id' => 'required|integer|exists:documents,id',
            'document_type_id' => 'required|integer|exists:document_types,id',
            'group_id' => 'required|integer|exists:groups,id',
            'progress_tracker_id' => 'required|integer|exists:progress_trackers,id',
            'created_by_user_id' => 'required|integer|exists:users,id',
            'current_workflow_stage_id' => 'required|integer|exists:workflow_stages,id',
            'department_id' => 'required|integer|exists:departments,id',
            'document_draftable_id' => 'required|integer',
            'document_draftable_type' => 'required|string',
            'document_action_id' => 'required|integer|exists:document_actions,id',
            'signature' => 'nullable|sometimes|string'
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $documentDraft = parent::store($data);

            if (!$documentDraft) {
                return null;
            }

            if (isset($data['signature']) && $data['signature'] !== "") {
                $dataUrl = $data['signature'];
                $fileData = explode(',', $dataUrl);
                $decodedFileData = base64_decode($fileData[1]);
                $fileName = uniqid() . '.png';
                Storage::disk('public')->put('signatures/' . $fileName, $decodedFileData);

                $documentDraft->signature = "signatures/{$fileName}";
                $documentDraft->save();
            }

            return $documentDraft;
        });
    }
}
