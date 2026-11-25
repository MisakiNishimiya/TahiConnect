<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DecartVirtualTryOnService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.decart.api_key');
        $this->model = config('services.decart.model', 'lucy-vton-latest');

        if (!$this->apiKey) {
            throw new Exception('Decart API key not configured. Set DECART_API_KEY in .env');
        }
    }

    /**
     * Create a short-lived client token for secure browser connection
     * Tokens expire in 60 seconds and are used to connect to the realtime model
     *
     * @return array { apiKey: string, expiresAt: string }
     */
    public function createClientToken(): array
    {
        try {
            Log::info('Creating Decart client token');

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->send('POST', 'https://api.decart.ai/v1/client/tokens', [
                'body' => json_encode(null)
            ]);

            if (!$response->successful()) {
                throw new Exception("Token creation failed: {$response->body()}");
            }

            $data = $response->json();
            Log::info('Client token created successfully', [
                'expires_at' => $data['expiresAt'] ?? null,
            ]);

            return [
                'clientToken' => $data['apiKey'],
                'expiresAt' => $data['expiresAt'],
            ];
        } catch (Exception $e) {
            Log::error('Failed to create client token', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate a try-on image using batch API (for static photo uploads)
     * This is useful if you want to support photo upload mode in addition to realtime
     *
     * @param string $personImageBase64 Person image in base64
     * @param string $garmentImageBase64 Garment image in base64
     * @param string $prompt Detailed description of the garment
     * @return array { taskId: string, status: string }
     */
    public function submitBatchTryOn(
        string $personImageBase64,
        string $garmentImageBase64,
        string $prompt
    ): array {
        try {
            Log::info('Submitting batch try-on request', [
                'prompt_length' => strlen($prompt),
            ]);

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.decart.ai/v1/lucy/vton-latest/process', [
                'person' => "data:image/jpeg;base64,{$personImageBase64}",
                'garment' => "data:image/jpeg;base64,{$garmentImageBase64}",
                'prompt' => $prompt,
            ]);

            if (!$response->successful()) {
                throw new Exception("Batch submission failed: {$response->body()}");
            }

            $data = $response->json();
            Log::info('Batch try-on submitted', [
                'task_id' => $data['taskId'] ?? null,
            ]);

            return [
                'taskId' => $data['taskId'],
                'status' => 'submitted',
            ];
        } catch (Exception $e) {
            Log::error('Failed to submit batch try-on', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check the status of a batch try-on task
     *
     * @param string $taskId Task ID from submitBatchTryOn
     * @return array { taskId: string, status: string, output?: string }
     */
    public function getBatchTryOnStatus(string $taskId): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->get("https://api.decart.ai/v1/tasks/{$taskId}");

            if (!$response->successful()) {
                throw new Exception("Status check failed: {$response->body()}");
            }

            $data = $response->json();

            return [
                'taskId' => $data['taskId'],
                'status' => $data['status'],
                'output' => $data['output'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get batch status', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate a prompt description for a garment image using Claude
     * This helps with batch API when users upload custom garments
     *
     * @param string $garmentImageBase64 Garment image in base64
     * @return string Generated prompt
     */
    public function generatePromptFromImage(string $garmentImageBase64): string
    {
        try {
            // Using Claude to generate prompts (you'll need Anthropic API key)
            // For now, return a generic prompt
            $prompt = "Substitute the current top with a stylish garment. Include detailed description of color, material, texture, pattern, and fit.";

            Log::info('Generated prompt for garment image');

            return $prompt;
        } catch (Exception $e) {
            Log::error('Failed to generate prompt', ['error' => $e->getMessage()]);
            // Return default prompt on error
            return "Replace the current outfit with the provided garment image, matching the style and fit appropriately.";
        }
    }

    /**
     * Verify API connectivity and key validity
     *
     * @return array { online: bool, error?: string, details?: array }
     */
    public function checkApiHealth(): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])
                ->timeout(10)
                ->get('https://api.decart.ai/v1/models');

            if ($response->successful()) {
                return [
                    'online' => true,
                    'details' => [
                        'models' => $response->json(),
                    ],
                ];
            }

            return [
                'online' => false,
                'error' => "API returned status {$response->status()}",
            ];
        } catch (Exception $e) {
            return [
                'online' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
