<?php

namespace App\Services;

use App\Repositories\DocumentUpdateRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'message' => 'nullable|sometimes|string|min:0'
        ];
    }

    public function store(array $data)
    {
        Log::info('Adding Document Update!!');
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

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data, $parsed) {
            $documentUpdate = $this->show($id);
            $message = $data['message'] ?? null;
            if (!$documentUpdate || !$message) {
                return null;
            }

            $threads = json_decode($documentUpdate->threads, true);
            $user = Auth::user();

            $newThread = [
                'document_update_id' => $documentUpdate->id,
                'staff' => "{$user->surname}, {$user->firstname}",
                'user_id' => $user->id,
                'department' => $user->department->abv,
                'response' => $message,
                'responded_at' => now()
            ];

            $threads[] = $newThread;

            $documentUpdate->update([
                'threads' => json_encode($threads)
            ]);

            $documentUpdate->draft->update([
                'type' => 'response',
                'status' => 'responded'
            ]);

            return $documentUpdate;
        });
    }
}
