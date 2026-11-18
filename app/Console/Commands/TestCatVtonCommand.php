<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestCatVtonCommand extends Command
{
    protected $signature = 'test:catvton';
    protected $description = 'Test CatVTON API connection';

    public function handle()
    {
        $apiUrl = config('services.tryon.api_url') ?? env('TRYON_API_URL');
        
        if (empty($apiUrl)) {
            $this->error('❌ TRYON_API_URL not configured in .env file');
            $this->info('');
            $this->info('Please add to your .env:');
            $this->info('TRYON_API_URL=https://your-ngrok-url.ngrok-free.dev');
            return 1;
        }

        $this->info('🔍 Testing CatVTON API connection...');
        $this->info('URL: ' . $apiUrl);
        $this->info('');

        // Test root endpoint
        $this->line('Testing root endpoint (/)...');
        try {
            $response = Http::timeout(10)->get($apiUrl);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Root endpoint responding');
                $this->line('   Service: ' . ($data['service'] ?? 'unknown'));
                $this->line('   Status: ' . ($data['status'] ?? 'unknown'));
                $this->line('   GPU: ' . ($data['gpu'] ? 'Available' : 'Not available'));
            } else {
                $this->error('❌ Root endpoint returned status: ' . $response->status());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to connect: ' . $e->getMessage());
            $this->info('');
            $this->info('Make sure:');
            $this->info('1. Kaggle notebook is running');
            $this->info('2. GPU is enabled in notebook settings');
            $this->info('3. Ngrok tunnel is active');
            $this->info('4. URL in .env matches the ngrok URL');
            return 1;
        }

        // Test health endpoint
        $this->info('');
        $this->line('Testing health endpoint (/health)...');
        try {
            $response = Http::timeout(10)->get($apiUrl . '/health');
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Health endpoint responding');
                $this->line('   Status: ' . ($data['status'] ?? 'unknown'));
                $this->line('   Model loaded: ' . ($data['model_loaded'] ? 'Yes' : 'No'));
                $this->line('   GPU available: ' . ($data['gpu_available'] ? 'Yes' : 'No'));
                $this->line('   Device: ' . ($data['device'] ?? 'unknown'));
                
                if (isset($data['gpu_memory_used'])) {
                    $this->line('   GPU memory: ' . $data['gpu_memory_used']);
                }
            } else {
                $this->error('❌ Health endpoint returned status: ' . $response->status());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Health check failed: ' . $e->getMessage());
            return 1;
        }

        // Success!
        $this->info('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('🎉 CatVTON API is ready to use!');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Run: php artisan queue:work');
        $this->info('2. Upload a person photo and garment design');
        $this->info('3. Click "Generate Preview"');
        $this->info('4. Wait ~30-60 seconds for AI processing');
        $this->info('');
        $this->info('📖 Documentation: ' . $apiUrl . '/docs');
        
        return 0;
    }
}
