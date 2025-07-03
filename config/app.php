<?php

// Configuration for application settings

// Environment detection
$environment = 'production'; // Default to production

// Check if we're in a development environment
if ($_SERVER['SERVER_NAME'] === 'localhost' || 
    strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
    strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) {
    $environment = 'development';
}

// Enable query profiler in development mode
if ($environment === 'development') {
    require_once __DIR__ . '/../core/QueryProfiler.php';
    Core\QueryProfiler::disable();
}

// Debug mode (shows detailed error messages)
$debugMode = $environment === 'development';

// Application configuration
return [
    'environment' => $environment,
    'debug' => $debugMode,
    'timezone' => 'Asia/Manila',
    'query_profiling' => $environment === 'development'
]; 