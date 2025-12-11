<?php
// Simple deployment script for Hostinger
echo "Deployment started...\n";

// Skip composer install on production
if (isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'bkgreenenergy.com') !== false ||
    strpos($_SERVER['HTTP_HOST'], 'athenas.co.in') !== false
)) {
    echo "Production environment detected - skipping composer\n";
    exit(0);
}

echo "Deployment completed\n";
?>