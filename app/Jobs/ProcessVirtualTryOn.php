<?php

namespace App\Jobs;

use App\Models\VirtualTryon;
use App\Services\VirtualTryOnService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessVirtualTryOn implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(
        public int $tryonId,
        public string $photoPath,
        public string $garmentPath,
        public string $clothType = 'upper'
    ) {}

    public function handle(): void
    {
        try {
            $tryon = VirtualTryon::findOrFail($this->tryonId);
            
            Log::info("Processing virtual try-on: {$tryon->id}", [
                'photo_path' => $this->photoPath,
                'garment_path' => $this->garmentPath,
            ]);

            // Mark as processing
            $tryon->update([
                'processing_status' => 'processing',
                'processing_started_at' => now(),
            ]);

            // Create UploadedFile objects from the stored paths
            $photoFile = new UploadedFile(
                $this->photoPath,
                basename($this->photoPath),
                mime_content_type($this->photoPath),
                null,
                true
            );
            
            $garmentFile = new UploadedFile(
                $this->garmentPath,
                basename($this->garmentPath),
                mime_content_type($this->garmentPath),
                null,
                true
            );

            // Generate try-on using CatVTON API
            $service = new VirtualTryOnService();
            $resultUrl = $service->generateTryOn($photoFile, $garmentFile, $this->clothType);

            Log::info("API returned result URL for tryon {$tryon->id}", [
                'result_url' => $resultUrl,
            ]);

            // Update record with success
            $tryon->update([
                'processing_status' => 'completed',
                'processing_completed_at' => now(),
                'preview_data' => $resultUrl,
            ]);

            Log::info("Virtual try-on processed successfully: {$tryon->id}", [
                'preview_data' => $resultUrl,
            ]);

            // Dispatch event for real-time UI update
            \event(new \App\Events\VirtualTryOnProcessed($tryon));

        } catch (\Exception $e) {
            Log::error("Virtual try-on processing failed for {$this->tryonId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark as failed
            $tryon = VirtualTryon::find($this->tryonId);
            if ($tryon) {
                $tryon->update([
                    'processing_status' => 'failed',
                    'processing_completed_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Virtual try-on job permanently failed for {$this->tryonId}", [
            'error' => $exception->getMessage(),
        ]);

        $tryon = VirtualTryon::find($this->tryonId);
        if ($tryon) {
            $tryon->update([
                'processing_status' => 'failed',
                'error_message' => 'Processing failed: ' . $exception->getMessage(),
            ]);
        }
    }
}
