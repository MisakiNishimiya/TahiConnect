<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "\n=== Testing Decart Token Generation ===\n\n";
    
    // Create a test user context
    $user = \App\Models\User::first();
    if ($user) {
        auth()->login($user);
        echo "Authenticated as: {$user->email}\n";
    }
    
    // Test service
    $service = new \App\Services\DecartVirtualTryOnService();
    
    echo "\nTesting token creation...\n";
    $token = $service->createClientToken();
    
    echo "✅ Token created successfully!\n";
    echo "Response: " . json_encode($token, JSON_PRETTY_PRINT) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}
