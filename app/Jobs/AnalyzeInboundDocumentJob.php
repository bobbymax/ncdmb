<?php

namespace App\Jobs;

use App\Events\InboundAnalysisCompleted;
use App\Events\InboundAnalysisFailed;
use App\Models\Inbound;
use App\Services\AIAnalysisService;
use App\Services\PdfTextExtractorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeInboundDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutes
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $inboundId,
        public string $provider = 'openai'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        PdfTextExtractorService $pdfExtractor,
        AIAnalysisService $aiService
    ): void {
        try {
            Log::info('Starting AI analysis for inbound', [
                'inbound_id' => $this->inboundId,
                'provider' => $this->provider,
            ]);

            // Load inbound with uploads
            $inbound = Inbound::with('uploads')->findOrFail($this->inboundId);

            if (!$inbound->uploads || $inbound->uploads->count() === 0) {
                throw new \RuntimeException('No uploads found for inbound document');
            }

            // Step 1: Extract text from all PDF uploads
            $extractionResults = $pdfExtractor->extractFromMultiple($inbound->uploads);
            $documentText = $extractionResults['combined_text'];

            if (strlen(trim($documentText)) < 50) {
                throw new \RuntimeException("Unable to extract sufficient text from the PDF document.\n\nThis can happen if:\n• The PDF is a scanned image without OCR\n• The PDF is password protected\n• The document is empty or corrupted\n\nPlease upload a text-based PDF or use an OCR tool to convert scanned images to searchable text.");
            }

            Log::info('Text extraction completed', [
                'inbound_id' => $this->inboundId,
                'total_length' => $extractionResults['total_length'],
                'upload_count' => count($extractionResults['individual_results']),
            ]);

            // Step 2: Check if OCR was used for any upload
            $ocrUsed = false;
            foreach ($extractionResults['individual_results'] as $result) {
                if (isset($result['extraction']['method']) && $result['extraction']['method'] === 'ocr') {
                    $ocrUsed = true;
                    break;
                }
            }

            Log::info('OCR detection completed', [
                'inbound_id' => $this->inboundId,
                'ocr_used' => $ocrUsed,
            ]);

            // Step 3: Get board description
            $boardDescription = config('app.board_description', 
                "The Nigerian Content Development and Monitoring Board (NCDMB) is responsible for monitoring and coordinating Nigerian content development in the oil and gas industry."
            );

            // Step 4: Call AI for analysis
            Log::info('Sending to AI for analysis...', [
                'provider' => $this->provider,
                'text_length' => strlen($documentText),
            ]);

            $analysis = $aiService->analyzeDocument(
                documentText: $documentText,
                boardDescription: $boardDescription,
                senderInfo: [
                    'name' => $inbound->from_name,
                    'email' => $inbound->from_email,
                    'phone' => $inbound->from_phone,
                ],
                provider: $this->provider,
                config: []
            );

            // Step 5: Save analysis to database
            $inbound->update([
                'analysis' => $analysis,
                'ocr_available' => $ocrUsed,
                'ocr_index_version' => $ocrUsed ? 1 : 0,
            ]);

            Log::info('AI analysis completed and saved', [
                'inbound_id' => $this->inboundId,
                'document_type' => $analysis['documentType'] ?? 'unknown',
            ]);

            // Step 6: Broadcast to frontend
            broadcast(new InboundAnalysisCompleted($inbound, $analysis));

        } catch (\Exception $e) {
            Log::error('Inbound analysis job failed', [
                'inbound_id' => $this->inboundId,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Broadcast error to frontend
            try {
                $inbound = Inbound::find($this->inboundId);
                if ($inbound) {
                    broadcast(new InboundAnalysisFailed($inbound, $e->getMessage()));
                }
            } catch (\Exception $broadcastError) {
                Log::error('Failed to broadcast analysis error', [
                    'error' => $broadcastError->getMessage(),
                ]);
            }

            throw $e; // Re-throw for job retry
        }
    }
}

