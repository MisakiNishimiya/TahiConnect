<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class VirtualTryOnService
{
    private string $apiUrl;
    private int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.tryon.api_url');
        $this->timeout = config('services.tryon.timeout', 120);
    }

    /**
     * Generate a virtual try-on using OOTD model on Kaggle
     *
     * @param UploadedFile $personImage The person's photo (JPG, PNG, WebP)
     * @param UploadedFile $clothImage The garment image (JPG, PNG, WebP)
     * @param string $clothType Category: 'upper', 'lower', or 'overall'
     * @return string Public URL to the generated try-on image
     *
     * @throws Exception If API not configured or image generation fails
     */
    public function generateTryOn(
        UploadedFile $personImage,
        UploadedFile $clothImage,
        string $clothType = 'upper'
    ): string {
        // Validate API is configured
        if (empty($this->apiUrl) || $this->apiUrl === 'http://localhost:8000') {
            throw new Exception('Virtual try-on API not configured. Please set TRYON_API_URL in .env with your Kaggle Ngrok URL.');
        }

        // Encode images as Base64
        $personBase64 = base64_encode($personImage->get());
        $clothBase64 = base64_encode($clothImage->get());

        if ($personBase64 === false || $clothBase64 === false) {
            throw new Exception('Failed to encode images as Base64');
        }

        Log::info('Starting virtual try-on via Kaggle API', [
            'api_url' => $this->apiUrl,
            'cloth_type' => $clothType,
        ]);

        // Call Kaggle API
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'ngrok-skip-browser-warning' => 'true',
            ])
            ->post("{$this->apiUrl}/api/tryon", [
                'person_image_base64' => $personBase64,
                'cloth_image_base64' => $clothBase64,
                'cloth_type' => $clothType,
            ]);

        // Handle connection errors
        if (!$response->successful()) {
            $error = $response->json('error') ?? $response->body();
            Log::error('Kaggle API request failed', ['status' => $response->status(), 'error' => $error]);
            
            if (str_contains($error, 'Connection refused') || str_contains($error, 'Could not resolve')) {
                throw new Exception('Virtual try-on service is offline. Please ensure the Kaggle notebook is running.');
            }
            
            throw new Exception("API error: {$error}");
        }

        $result = $response->json();

        // Check if processing was successful
        if ($result['status'] !== 'success') {
            throw new Exception($result['error'] ?? 'Processing failed');
        }

        // Decode Base64 image
        $imageBase64 = $result['image_base64'] ?? null;
        if (empty($imageBase64)) {
            throw new Exception('No image data returned from API');
        }

        $imageData = base64_decode($imageBase64, true);
        if ($imageData === false) {
            throw new Exception('Failed to decode Base64 image data');
        }

        // Save the result image
        $filename = 'tryon_' . Str::uuid() . '.png';
        $path = 'tryons/' . $filename;

        if (!Storage::disk('public')->put($path, $imageData)) {
            throw new Exception('Failed to save generated image to storage');
        }

        $publicUrl = Storage::disk('public')->url($path);

        Log::info('Virtual try-on completed successfully', [
            'output_path' => $path,
            'processing_time' => $result['processing_time'] ?? null,
        ]);

        return $publicUrl;
    }

    /**
     * Check if the Kaggle API is online
     */
    public function checkApiHealth(): array
    {
        try {
            if (empty($this->apiUrl) || $this->apiUrl === 'http://localhost:8000') {
                return [
                    'online' => false,
                    'error' => 'API URL not configured',
                ];
            }

            $response = Http::timeout(10)
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                ])
                ->get("{$this->apiUrl}/health");

            if ($response->successful()) {
                return [
                    'online' => true,
                    'details' => $response->json(),
                ];
            }

            return [
                'online' => false,
                'error' => 'API returned error: ' . $response->status(),
            ];

        } catch (Exception $e) {
            return [
                'online' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
