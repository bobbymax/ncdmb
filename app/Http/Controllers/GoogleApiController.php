<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentDraftResource;
use App\Services\DocumentDraftService;
use App\Services\ThreadService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class GoogleApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        public DocumentDraftService $documentDraftService,
        public ThreadService $threadService,
    ) {}

    /**
     *  Calculates Distance of two states in km
     *  @return float
     */
    public function getDistanceInKm(Request $request): \Illuminate\Http\JsonResponse
    {
        $origin = $request->query('origin');
        $destination = $request->query('destination');
        $key = env('GOOGLE_API_KEY');

        $response = Http::get("https://maps.googleapis.com/maps/api/distancematrix/json", [
            'origins' => $origin,
            'destinations' => $destination,
            'key' => $key,
            'units' => 'metric',
        ]);

        return $this->success($response->json());
    }

    public function assign(Request $request): \Illuminate\Http\JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'action' => 'required|string|max:255',
            'document_draft_id' => 'required|integer|exists:document_drafts,id',
            'document_id' => 'required|integer|exists:documents,id',
            'pointer_identifier' => 'required|string|max:255',
            'priority' => 'required|string|max:255|in:low,medium,high',
            'progress_tracker_id' => 'required|integer|exists:progress_trackers,id',
            'recipients' => 'required|array',
            'recipients.*' => 'required|integer|exists:users,id',
        ]);

        if ($validation->fails()) {
            return $this->error($validation->errors(), 'Please fix the following errors: ', 500);
        }

        $currentDraft = $this->documentDraftService->show($request->input('document_draft_id'));


        if ($currentDraft) {
            $currentDraft->update([
                'operator_id' => Auth::id()
            ]);

            $currentDraft->document->update([
                'status' => 'processing'
            ]);

            $this->threadService->startConversation($request->all());
        }

        return $this->success(new DocumentDraftResource($currentDraft), 'Document handler activated!');
    }
}
