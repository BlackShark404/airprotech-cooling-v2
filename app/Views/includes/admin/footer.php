<?php 
// Check if we're in a development environment
$isDevelopment = ($_SERVER['SERVER_NAME'] === 'localhost' || 
                  strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
                  strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false);
?>

<?php if ($isDevelopment): ?>
    <!-- Debug Panel for development only -->
    <?php
    // Only include and show if the QueryProfiler class exists
    if (class_exists('Core\\QueryProfiler') && Core\QueryProfiler::isEnabled()) {
        echo Core\QueryProfiler::renderSummary();
    }
    ?>
<?php endif; ?>