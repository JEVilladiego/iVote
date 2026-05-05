<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if (is_file($file)) {
    return false;
}

if (is_file($file . '.php')) {
    require $file . '.php';
    return true;
}

if (is_dir($file) && is_file($file . '/index.php')) {
    require $file . '/index.php';
    return true;
}

// Default to index.php
require __DIR__ . '/index.php';