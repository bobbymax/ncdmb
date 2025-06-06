<?php

namespace App\Observers;

use App\Models\DocumentDraft;
use Illuminate\Support\Facades\DB;

class DocumentDraftObserver
{
    /**
     * @throws \Throwable
     */
    public function creating(DocumentDraft $documentDraft): void
    {
        if (! $documentDraft->version_number && $documentDraft->document_id && $documentDraft->document_type_id) {
            $version = retry(3, function () use ($documentDraft) {
                return DB::transaction(function () use ($documentDraft) {
                    $maxVersion = DocumentDraft::withTrashed() // 👈 Include soft-deleted drafts
                    ->where('document_id', $documentDraft->document_id)
                        ->where('document_type_id', $documentDraft->document_type_id)
                        ->lockForUpdate()
                        ->max('version_number') ?? 0;

                    return $maxVersion + 1;
                });
            }, 100); // retry up to 3 times with 100ms wait

            $documentDraft->version_number = $version;
        }
    }
}
