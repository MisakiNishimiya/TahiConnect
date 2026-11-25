<?php

namespace App\Console\Commands;

use App\Services\DecartVirtualTryOnService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestDecartConnectionCommand extends Command
{
    protected $signature = 'decart:test';
    protected $description = 'Test Decart API connection and diagnose issues';

    public function handle(): int
    {
        $this->info('Testing Decart API Connection...');
        $this->newLine();

        // Test 1: Check DNS resolution
        $this->info('1. Testing DNS resolution for api.decart.ai...');
        $dnsTest = @gethostbyname('api.decart.ai');
        if ($dnsTest === 'api.decart.ai') {
            $this->error('   ✗ DNS resolution failed - cannot resolve hostname');
            $this->warn('   This indicates a network/DNS issue on this server');
        } else {
            $this->info("   ✓ DNS resolved to: {$dnsTest}");
        }
        $this->newLine();

        // Test 2: Check API key configuration
        $this->info('2. Checking API key configuration...');
        $apiKey = config('services.decart.api_key');
        if (!$apiKey) {
            $this->error('   ✗ DECART_API_KEY not set in .env');
            return 1;
        }
        $this->info('   ✓ API key configured: ' . substr($apiKey, 0, 20) . '...');
        $this->newLine();

        // Test 3: Test HTTP connectivity
        $this->info('3. Testing HTTP connectivity to Decart API...');
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                ])
                ->get('https://api.decart.ai/v1/models');

            if ($response->successful()) {
                $this->info('   ✓ Successfully connected to Decart API');
                $this->info('   Available models: ' . json_encode($response->json()));
            } else {
                $this->error("   ✗ API returned status: {$response->status()}");
                $this->warn('   Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Connection failed: ' . $e->getMessage());
            $this->warn('   This indicates the server cannot reach Decart API');
        }
        $this->newLine();

        // Test 4: Test token creation
        $this->info('4. Testing client token creation...');
        try {
            $service = new DecartVirtualTryOnService();
            $token = $service->createClientToken();
            $this->info('   ✓ Client token created successfully');
            $this->info('   Expires at: ' . $token['expiresAt']);
        } catch (\Exception $e) {
            $this->error('   ✗ Token creation failed: ' . $e->getMessage());
        }
        $this->newLine();

        $this->info('Test complete!');
        return 0;
    }
}
