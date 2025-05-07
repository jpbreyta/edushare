<?php
// Clear the cache by deleting cache files or resetting cache settings
// For example, you can delete files in a cache directory or reset cache headers

// Example: Delete cache files in a specific directory
$cache_dir = '../cache/';
if (is_dir($cache_dir)) {
    $files = glob($cache_dir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

// Redirect back to the homepage
header("Location: index.php");
exit();
?> 