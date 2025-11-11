<?php
echo "PHP is working\n";
echo "Current directory: " . getcwd() . "\n";

// Test basic Laravel bootstrap
try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "Laravel bootstrap: OK\n";
    
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "Laravel kernel bootstrap: OK\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}