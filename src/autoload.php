<?php
/**
 * Autoloader sederhana (PSR-4 style) untuk namespace project.
 * Panggil require_once sekali di setiap entry point.
 */

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    // Map namespace ke direktori
    $namespaceMap = [
        'Config\\'     => BASE_PATH . '/src/Config/',
        'Models\\'     => BASE_PATH . '/src/Models/',
        'Services\\'   => BASE_PATH . '/src/Services/',
        'Middleware\\' => BASE_PATH . '/src/Middleware/',
        'Helpers\\'    => BASE_PATH . '/src/Helpers/',
    ];

    foreach ($namespaceMap as $namespace => $dir) {
        if (str_starts_with($class, $namespace)) {
            $relative = substr($class, strlen($namespace));
            $file     = $dir . str_replace('\\', '/', $relative) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});
