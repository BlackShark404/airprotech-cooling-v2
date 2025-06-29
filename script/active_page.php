<?php
function base_url($uri = '', $protocol = true) {

    // Get the protocol
    $base_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    
    // Get the server name and any potential subfolder the application is in
    $base_domain = $_SERVER['HTTP_HOST'];
    
    // Application subfolder - adjust this if your application is in a subfolder
    $base_folder = dirname($_SERVER['SCRIPT_NAME']);
    $base_folder = ($base_folder === '/' || $base_folder === '\\') ? '' : $base_folder;
    
    // Combine to create base URL
    $base_url = $protocol ? $base_protocol . $base_domain . $base_folder : $base_domain . $base_folder;
    
    // Clean up base URL (ensure single trailing slash)
    $base_url = rtrim($base_url, '/') . '/';
    
    // Add URI if provided
    if ($uri) {
        // Remove leading slashes from URI
        $uri = ltrim($uri, '/');
        $base_url .= $uri;
    }
    
    return $base_url;
    }