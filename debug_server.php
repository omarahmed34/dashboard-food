<?php
header('Content-Type: text/plain');
echo "SERVER INFO:\n";
print_r($_SERVER);
echo "\nDB.PHP LOGIC TEST:\n";
$is_local = (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) || (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '127.0.0.1');
echo "Is Local: " . ($is_local ? "YES" : "NO") . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? 'NOT SET') . "\n";
?>
