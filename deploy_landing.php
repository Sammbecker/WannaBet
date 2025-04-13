<?php
// Source and destination paths
$sourceFile = __DIR__ . '/app/views/landing.php';
$destFile = __DIR__ . '/public/index.php';

// Copy the file
if (copy($sourceFile, $destFile)) {
    echo "Landing page deployed successfully to public/index.php\n";
} else {
    echo "Error deploying landing page\n";
}
?> 