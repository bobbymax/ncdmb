<?php

namespace App\Http\Controllers;

use App\Http\Resources\InboundResource;
use App\Jobs\AnalyzeInboundDocumentJob;
use App\Services\InboundService;
use Illuminate\Http\Request;

class InboundController extends BaseController
{
    public function __construct(InboundService $inboundService) {
        parent::__construct($inboundService, 'Inbound', InboundResource::class);
    }

    /**
     * Display the specified inbound document
     * Triggers AI analysis if not already done
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        $inbound = $this->service->show($id);

        // If no analysis exists and uploads are available, dispatch analysis job
        if (!$inbound->analysis && $inbound->uploads && $inbound->uploads->count() > 0) {
            $provider = request()->query('ai_provider', config('services.ai.default_provider', 'openai'));

            AnalyzeInboundDocumentJob::dispatch($inbound->id, $provider);

            \Illuminate\Support\Facades\Log::info('AI Analysis job dispatched', [
                'inbound_id' => $inbound->id,
                'provider' => $provider,
            ]);
        }

        return $this->success(new $this->jsonResource($inbound));
    }

    /**
     * Trigger manual AI analysis
     */
    public function analyze(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'provider' => 'nullable|string|in:openai,huggingface',
        ]);

        $inbound = $this->service->show($id);

        if (!$inbound->uploads || $inbound->uploads->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No uploads found for analysis',
            ], 400);
        }

        $provider = $validated['provider'] ?? config('services.ai.default_provider', 'openai');

        // Dispatch the job
        AnalyzeInboundDocumentJob::dispatch($inbound->id, $provider);

        return $this->success([
            'success' => true,
            'message' => 'AI analysis job queued successfully',
            'inbound_id' => $inbound->id,
            'provider' => $provider,
        ], 'AI analysis job queued successfully');
    }
}
