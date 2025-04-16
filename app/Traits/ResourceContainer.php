<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ResourceContainer
{
    /**
     * Resolves documents and includes only the latest draft per draftable_type.
     *
     * @param string $status
     * @param string $accessLevel   'processing' or 'search'
     * @param string $userColumn    column in the drafts table to match auth()->id()
     * @param string $draftScope    'linked' or 'all'
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|null
     */
    public function resolveResource(
        string $status,
        string $accessLevel,
        string $userColumn = 'user_id',
        string $draftScope = 'linked'
    ): ?\Illuminate\Http\Resources\Json\AnonymousResourceCollection {
        $modelClass = \App\Models\Document::class;
        $resourceClass = \App\Http\Resources\DocumentResource::class;

        // Step 1: Load documents and both draft relations
        $documents = $modelClass::query()
            ->where('status', $status)
            ->with(['drafts', 'linkedDrafts'])
            ->get();

        // Step 2: Filter if access level is "processing"
        if ($accessLevel === 'processing') {
            $documents = $documents->filter(function ($doc) use ($userColumn) {
                // 🔥 Only use drafts (not linkedDrafts)
                $lastDraft = $doc->drafts
                    ->sortByDesc('id')
                    ->first();

                if (!$lastDraft) return false;

                // Attach for use in resource if needed
//                $doc->lastDraft = $lastDraft;

                return data_get($lastDraft, $userColumn) == Auth::id();
            });
        }

        // Step 3: For included documents, attach draft collections based on scope
        $documents->each(function ($doc) use ($draftScope) {
            if ($draftScope === 'all') {
                $merged = $doc->drafts->merge($doc->linkedDrafts);

                $doc->complete_or_linked_drafts = $merged
                    ->groupBy('document_draftable_type')
                    ->map(fn($group) => $group->sortByDesc('version_number')->first())
                    ->values();
            } else {
                $doc->complete_or_linked_drafts = $doc->linkedDrafts;
            }
        });

        return $resourceClass::collection($documents);
    }
}
