<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\VirtualTryOnService;

class CheckKaggleSystemCommand extends Command
{
    protected $signature = 'kaggle:check';
    protected $description = 'Check Kaggle Virtual Try-On system configuration and status';

    public function handle(): int
    {
        $this->info('========================================');
        $this->info('  Kaggle Virtual Try-On System Check');
        $this->info('========================================');
        $this->newLine();

        // 1. Check Environment Variables
        $this->info('1. Environment Configuration');
        $this->checkEnv('TRYON_API_URL', 'Kaggle API URL');
        $this->checkEnv('TRYON_API_TIMEOUT', 'API Timeout');
        $this->newLine();

        // 2. Check Config Values
        $this->info('2. Laravel Config Values');
        $apiUrl = config('services.tryon.api_url');
        $timeout = config('services.tryon.timeout');
        
        if ($apiUrl) {
            $this->line('   ✓ API URL: ' . $apiUrl);
        } else {
            $this->error('   ✗ API URL NOT configured');
        }
        
        if ($timeout) {
            $this->line('   ✓ Timeout: ' . $timeout . ' seconds');
        }
        $this->newLine();

        // 3. Check Database
        $this->info('3. Database Status');
        try {
            $tryonsCount = DB::table('virtual_tryons')->count();
            $pendingCount = DB::table('virtual_tryons')->where('processing_status', 'pending')->count();
            $processingCount = DB::table('virtual_tryons')->where('processing_status', 'processing')->count();
            $completedCount = DB::table('virtual_tryons')->where('processing_status', 'completed')->count();
            $failedCount = DB::table('virtual_tryons')->where('processing_status', 'failed')->count();
            
            $this->line("   ✓ Database accessible");
            $this->line("   Total try-ons: {$tryonsCount}");
            $this->line("   Pending: {$pendingCount}");
            $this->line("   Processing: {$processingCount}");
            $this->line("   Completed: {$completedCount}");
            $this->line("   Failed: {$failedCount}");
        } catch (\Exception $e) {
            $this->error('   ✗ Database error: ' . $e->getMessage());
        }
        $this->newLine();

        // 4. Check Queue Status
        $this->info('4. Queue System');
        try {
            $jobsCount = DB::table('jobs')->count();
            $failedJobsCount = DB::table('failed_jobs')->count();
            
            $this->line("   ✓ Queue table accessible");
            $this->line("   Pending jobs: {$jobsCount}");
            $this->line("   Failed jobs: {$failedJobsCount}");
            
            if ($jobsCount > 0) {
                $this->warn("   ⚠ You have {$jobsCount} pending jobs. Make sure queue worker is running!");
                $this->line("   Run: php artisan queue:work");
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Queue check error: ' . $e->getMessage());
        }
        $this->newLine();

        // 5. Check Storage Directory
        $this->info('5. Storage Configuration');
        $publicPath = storage_path('app/public');
        $uploadsPath = storage_path('app/public/uploads');
        $tryonsPath = storage_path('app/public/tryons');
        
        $this->checkDirectory($publicPath, 'Public storage');
        $this->checkDirectory($uploadsPath, 'Uploads directory');
        $this->checkDirectory($tryonsPath, 'Try-ons directory');
        $this->newLine();

        // 6. Test Kaggle API Connection
        if ($apiUrl && $apiUrl !== 'https://your-domain.ngrok-free.dev') {
            $this->info('6. Kaggle API Health Check');
            try {
                $service = app(VirtualTryOnService::class);
                $health = $service->checkApiHealth();
                
                if ($health['online']) {
                    $this->line('   ✓ Kaggle API is online');
                    $this->line('   Status: ' . $service->getApiStatus());
                    
                    if (isset($health['details'])) {
                        $details = $health['details'];
                        $this->line('   Device: ' . ($details['device'] ?? 'unknown'));
                        $this->line('   GPU: ' . ($details['gpu_available'] ? 'Available' : 'Not available'));
                    }
                } else {
                    $this->error('   ✗ Kaggle API is offline');
                    $this->line('   Error: ' . ($health['error'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $this->error('   ✗ API health check failed: ' . $e->getMessage());
            }
            $this->newLine();
        } else {
            $this->info('6. Kaggle API Health Check');
            $this->warn('   ⚠ Skipped (API URL not configured)');
            $this->newLine();
        }

        // Summary
        $this->info('========================================');
        $this->info('  Summary');
        $this->info('========================================');
        
        if ($apiUrl && $apiUrl !== 'https://your-domain.ngrok-free.dev') {
            $this->info('✓ System is configured for Kaggle Virtual Try-On');
            $this->line('  Using FREE Kaggle T4 GPU via ngrok tunnel');
        } else {
            $this->warn('⚠ Kaggle API not configured');
            $this->line('  To enable Kaggle virtual try-on:');
            $this->line('  1. Run Kaggle notebook (kaggle_catvton_no_auth.py)');
            $this->line('  2. Copy ngrok URL to TRYON_API_URL in .env');
            $this->line('  3. Run: php artisan config:clear');
            $this->line('  4. Restart queue worker');
        }
        
        $this->newLine();
        $this->info('To start processing try-ons, run:');
        $this->line('  php artisan queue:work');
        
        return 0;
    }

    private function checkEnv(string $key, string $label): void
    {
        $value = env($key);
        if ($value) {
            $this->line("   ✓ {$label}: {$value}");
        } else {
            $this->error("   ✗ {$label}: NOT SET");
        }
    }

    private function checkDirectory(string $path, string $label): void
    {
        if (is_dir($path)) {
            $writable = is_writable($path);
            if ($writable) {
                $this->line("   ✓ {$label}: {$path} (writable)");
            } else {
                $this->error("   ✗ {$label}: {$path} (NOT writable)");
            }
        } else {
            $this->warn("   ⚠ {$label}: {$path} (doesn't exist, will be created)");
        }
    }
}
