<?php
/**
 * Base URL helper function
 * 
 * Returns the base URL for the site with optional path appended
 * 
 * @param string $uri URI segment to append to the base URL
 * @param bool $protocol Whether to include the protocol (http/https)
 * @return string The complete URL
 */

namespace Core;

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

/**
 * Site URL helper function
 * Alternative to base_url() that always includes index.php
 * Useful for frameworks that use index.php in URLs
 * 
 * @param string $uri URI segment to append to the site URL
 * @param bool $protocol Whether to include the protocol
 * @return string The complete URL including index.php
 */
function site_url($uri = '', $protocol = true) {
    return base_url('index.php/' . ltrim($uri, '/'), $protocol);
}

// If you need configuration-based URL rather than server-derived,
// you can set a constant or global variable like this:
// define('BASE_URL', 'https://example.com/');
// And then modify the base_url function to use this constant instead