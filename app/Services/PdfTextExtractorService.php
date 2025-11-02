<?php

namespace App\Services;

use App\Engine\Puzzle;
use Illuminate\Database\Eloquent\Collection;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PdfTextExtractorService
{
    protected Parser $parser;
    protected float $lowDensityThreshold = 0.05; // 5% text coverage

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extract text from encrypted PDF content
     */
    public function extractText(string $encryptedContent, string $mime): array
    {
        try {
            // 1. Decrypt the encrypted data URL using Puzzle
            $snapshot = Puzzle::resolve($encryptedContent);
            $dataUrl = 'data:' . $mime . ';base64,' . base64_encode($snapshot);

            // 2. Extract base64 content from data URL
            // Format: data:application/pdf;base64,<base64_content>
            if (!str_starts_with($dataUrl, 'data:')) {
                throw new \RuntimeException('Expected data URL after decryption, got: ' . substr($dataUrl, 0, 50));
            }

            $parts = explode(',', $dataUrl, 2);
            if (count($parts) !== 2) {
                throw new \RuntimeException('Invalid data URL format');
            }

            // 3. Decode base64 to get actual PDF binary
            $pdfBinary = base64_decode($parts[1]);

            if ($pdfBinary === false) {
                throw new \RuntimeException('Failed to decode base64 content');
            }

            // 4. Extract text using PDF parser
            $text = $this->extractWithParser($pdfBinary);

            // 3. Calculate text density
            $density = $this->calculateTextDensity($text, $pdfBinary);

            Log::info('PDF text extraction completed', [
                'textLength' => strlen($text),
                'density' => $density,
                'method' => 'parser',
            ]);

            // 4. If density is too low, try OCR
            if ($density < $this->lowDensityThreshold && strlen(trim($text)) < 100) {
                Log::info('Low text density detected, attempting OCR...');

                try {
                    $ocrText = $this->extractWithOCR($pdfBinary);

                    if (strlen($ocrText) > strlen($text)) {
                        Log::info('OCR extraction successful', [
                            'ocrTextLength' => strlen($ocrText),
                        ]);

                        return [
                            'text' => $ocrText,
                            'method' => 'ocr',
                            'density' => $density,
                            'length' => strlen($ocrText),
                            'pages' => $this->getPageCount($pdfBinary),
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('OCR extraction failed, using parser result', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return [
                'text' => $text,
                'method' => 'parser',
                'density' => $density,
                'length' => strlen($text),
                'pages' => $this->getPageCount($pdfBinary),
            ];

        } catch (\Exception $e) {
            Log::error('PDF text extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \RuntimeException('Failed to extract text from PDF: ' . $e->getMessage());
        }
    }

    /**
     * Extract text using PDF parser library
     */
    private function extractWithParser(string $pdfBinary): string
    {
        try {
            $pdf = $this->parser->parseContent($pdfBinary);
            $text = $pdf->getText();

            return $this->cleanText($text);
        } catch (\Exception $e) {
            Log::warning('PDF parser failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Extract text using OCR (for scanned PDFs)
     */
    private function extractWithOCR(string $pdfBinary): string
    {
        try {
            // Save PDF to temporary file
            $tempPdfPath = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($tempPdfPath, $pdfBinary);

            // Convert PDF to images and run OCR
            // Note: This requires ImageMagick and Tesseract installed on server
            $ocr = new TesseractOCR($tempPdfPath);
            $text = $ocr->run();

            // Cleanup
            unlink($tempPdfPath);

            return $this->cleanText($text);

        } catch (\Exception $e) {
            Log::error('OCR extraction failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Calculate text density as a ratio of text length to file size
     */
    private function calculateTextDensity(string $text, string $pdfBinary): float
    {
        $textLength = strlen(trim($text));
        $fileSize = strlen($pdfBinary);

        if ($fileSize === 0) {
            return 0;
        }

        // Normalize by typical text-to-binary ratio for PDFs
        // A typical text PDF has ratio around 0.2-0.4
        return ($textLength / $fileSize) * 2.5;
    }

    /**
     * Get page count from PDF
     */
    private function getPageCount(string $pdfBinary): int
    {
        try {
            $pdf = $this->parser->parseContent($pdfBinary);
            $pages = $pdf->getPages();
            return count($pages);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clean extracted text
     */
    private function cleanText(string $text): string
    {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove null bytes
        $text = str_replace("\0", '', $text);

        // Trim
        return trim($text);
    }

    /**
     * Extract text from multiple uploads
     */
    public function extractFromMultiple(array|Collection $uploads): array
    {
        $results = [];
        $combinedText = '';

        foreach ($uploads as $index => $upload) {
            try {
                $extraction = $this->extractText($upload->file_path, $upload->mime_type);

                $results[] = [
                    'upload_id' => $upload->id,
                    'name' => $upload->name,
                    'extraction' => $extraction,
                ];

                $combinedText .= "\n\n========== Document " . ($index + 1) . ": {$upload->name} ==========\n";
                $combinedText .= $extraction['text'];

            } catch (\Exception $e) {
                Log::error('Failed to extract text from upload', [
                    'upload_id' => $upload->id,
                    'error' => $e->getMessage(),
                ]);

                $results[] = [
                    'upload_id' => $upload->id,
                    'name' => $upload->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'combined_text' => trim($combinedText),
            'individual_results' => $results,
            'total_length' => strlen($combinedText),
        ];
    }
}

