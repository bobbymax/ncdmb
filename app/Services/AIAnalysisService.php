<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIAnalysisService
{
    /**
     * Analyze document using configured AI provider
     */
    public function analyzeDocument(
        string $documentText,
        string $boardDescription,
        array $senderInfo,
        string $provider = 'openai',
        array $config = []
    ): array {
        if ($provider === 'huggingface') {
            return $this->analyzeWithHuggingFace($documentText, $boardDescription, $senderInfo, $config);
        }

        return $this->analyzeWithOpenAI($documentText, $boardDescription, $senderInfo, $config);
    }

    /**
     * Analyze using OpenAI
     */
    private function analyzeWithOpenAI(
        string $documentText,
        string $boardDescription,
        array $senderInfo,
        array $config = []
    ): array {
        $apiKey = config('services.openai.api_key');
        $model = $config['model'] ?? config('services.openai.model');
        $maxTokens = $config['maxTokens'] ?? config('services.openai.max_tokens');

        if (!$apiKey) {
            throw new \RuntimeException('OpenAI API key not configured');
        }

        $prompt = $this->buildPrompt($documentText, $boardDescription, $senderInfo);

        // GPT-5 models don't support custom temperature
        $isGpt5 = str_contains(strtolower($model), 'gpt-5') || str_contains(strtolower($model), 'o3');
        $temperature = $isGpt5 ? null : ($config['temperature'] ?? config('services.openai.temperature'));

        $requestPayload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert document analyst for the Nigerian Content Development and Monitoring Board (NCDMB). Your role is to analyze inbound documents from external sources and provide insights on how they align with the board\'s mission of promoting Nigerian content in the oil and gas industry. CRITICAL: You MUST respond with ONLY valid JSON - no markdown, no code blocks, no explanatory text. Start directly with { and end with }.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_completion_tokens' => $maxTokens,
            'user' => (string)(auth()->id() ?? 'system'),
        ];

        if ($temperature !== null) {
            $requestPayload['temperature'] = $temperature;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout(120)
        ->post('https://api.openai.com/v1/chat/completions', $requestPayload);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            // Check if content is empty due to token limit
            if ($content === '' && ($data['choices'][0]['finish_reason'] ?? '') === 'length') {
                Log::error('OpenAI hit token limit - empty content', [
                    'reasoning_tokens' => $data['usage']['completion_tokens_details']['reasoning_tokens'] ?? 0,
                    'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                    'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                ]);
                
                throw new \RuntimeException("AI analysis exceeded token limits.\n\nThe document is too long or complex. Try:\n• Uploading a shorter document\n• Splitting into multiple documents\n• Reducing document size\n\nTechnical: All {$data['usage']['completion_tokens']} tokens were used for reasoning, leaving no tokens for output.");
            }

            if ($content) {
                // Strip potential markdown code blocks
                $content = preg_replace('/```json\s*|\s*```/', '', $content);
                $content = trim($content);
                
                // Try to extract JSON if wrapped in text
                if (!str_starts_with($content, '{')) {
                    // Try to find JSON in the response
                    preg_match('/\{[\s\S]*\}/', $content, $matches);
                    if (!empty($matches[0])) {
                        $content = $matches[0];
                    }
                }
                
                $analysis = json_decode($content, true);
                
                // Check if JSON parsing succeeded
                if ($analysis === null && json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('OpenAI JSON Parse Error', [
                        'error' => json_last_error_msg(),
                        'raw_content' => substr($content, 0, 500),
                    ]);
                    throw new \RuntimeException('Failed to parse OpenAI response as JSON: ' . json_last_error_msg());
                }
                
                // Validate required fields
                $requiredFields = ['summary', 'keyFeatures', 'organizationalBenefits'];
                foreach ($requiredFields as $field) {
                    if (!isset($analysis[$field])) {
                        Log::error('OpenAI Response Missing Field', [
                            'missing_field' => $field,
                            'response' => $analysis,
                        ]);
                        throw new \RuntimeException("OpenAI response missing required field: {$field}");
                    }
                }

                Log::info('OpenAI Analysis Completed', [
                    'model' => $model,
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                ]);

                return $analysis;
            }

            // Log the actual response for debugging
            Log::error('OpenAI returned empty content', [
                'response' => $data,
            ]);

            throw new \RuntimeException('Invalid response from OpenAI: no content returned');
        }

        throw new \RuntimeException('OpenAI request failed: ' . $response->body());
    }

    /**
     * Analyze using HuggingFace
     */
    private function analyzeWithHuggingFace(
        string $documentText,
        string $boardDescription,
        array $senderInfo,
        array $config = []
    ): array {
        $apiKey = config('services.huggingface.api_key');
        $model = $config['model'] ?? config('services.huggingface.model');
        $maxTokens = $config['maxTokens'] ?? config('services.huggingface.max_tokens');
        $temperature = $config['temperature'] ?? config('services.huggingface.temperature');

        if (!$apiKey) {
            throw new \RuntimeException('HuggingFace API key not configured');
        }

        $prompt = $this->buildPrompt($documentText, $boardDescription, $senderInfo);
        $fullPrompt = "<|system|>\nYou are an expert document analyst for the Nigerian Content Development and Monitoring Board (NCDMB). CRITICAL: You MUST respond with ONLY valid JSON.\n<|user|>\n{$prompt}\n<|assistant|>\n";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout(180)
        ->post("https://api-inference.huggingface.co/models/{$model}", [
            'inputs' => $fullPrompt,
            'parameters' => [
                'max_new_tokens' => $maxTokens,
                'temperature' => $temperature,
                'return_full_text' => false,
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data[0]['generated_text'] ?? null;

            if ($content) {
                $content = preg_replace('/```json\s*|\s*```/', '', $content);
                $content = trim($content);
                
                // Try to extract JSON if wrapped in text
                if (!str_starts_with($content, '{')) {
                    preg_match('/\{[\s\S]*\}/', $content, $matches);
                    if (!empty($matches[0])) {
                        $content = $matches[0];
                    }
                }
                
                $analysis = json_decode($content, true);

                if ($analysis === null && json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('HuggingFace JSON Parse Error', [
                        'error' => json_last_error_msg(),
                        'raw_content' => substr($content, 0, 500),
                    ]);
                    throw new \RuntimeException('Failed to parse HuggingFace response as JSON: ' . json_last_error_msg());
                }
                
                // Validate required fields
                $requiredFields = ['summary', 'keyFeatures', 'organizationalBenefits'];
                foreach ($requiredFields as $field) {
                    if (!isset($analysis[$field])) {
                        Log::error('HuggingFace Response Missing Field', [
                            'missing_field' => $field,
                            'response' => $analysis,
                        ]);
                        throw new \RuntimeException("HuggingFace response missing required field: {$field}");
                    }
                }

                Log::info('HuggingFace Analysis Completed', ['model' => $model]);
                return $analysis;
            }

            Log::error('HuggingFace returned empty content', [
                'response' => $data,
            ]);

            throw new \RuntimeException('Invalid response from HuggingFace: no content returned');
        }

        throw new \RuntimeException('HuggingFace request failed: ' . $response->body());
    }

    /**
     * Build analysis prompt
     */
    private function buildPrompt(string $documentText, string $boardDescription, array $senderInfo): string
    {
        return "
            Analyze the following inbound document from an external source:

            SENDER INFORMATION:
            - Name: {$senderInfo['name']}
            - Email: {$senderInfo['email']}
            - Phone: {$senderInfo['phone']}

            DOCUMENT CONTENT:
            {$documentText}

            BOARD CONTEXT:
            {$boardDescription}

            Please provide a comprehensive analysis in JSON format:
            {
                \"summary\": \"A concise 2-3 sentence summary of the document content\",
                \"keyFeatures\": [\"List the 3-5 major purposes or objectives of this document\"],
                \"organizationalBenefits\": [\"List 3-5 specific benefits this document/request would bring to the board based on the board's context\"],
                \"documentType\": \"The type of document (e.g., proposal, request, inquiry, complaint, etc.)\",
                \"confidence\": 0.95,
                \"urgency\": \"low|medium|high\",
                \"suggestedActions\": [\"Recommended next steps for processing this document\"]
            }

            Ensure the organizationalBenefits are specifically tailored to the board's mission and operations.
        ";
    }
}

