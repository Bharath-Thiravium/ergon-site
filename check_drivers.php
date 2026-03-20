<?php
echo "PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "pdo_pgsql loaded: " . (extension_loaded('pdo_pgsql') ? 'YES' : 'NO') . "\n";
echo "pgsql loaded: " . (extension_loaded('pgsql') ? 'YES' : 'NO') . "\n";
?>