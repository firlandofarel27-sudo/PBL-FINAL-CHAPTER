<?php

declare(strict_types=1);

/**
 * Titik masuk aplikasi: autoloader, helper global, dan pembacaan konfigurasi.
 *
 * Tidak ada Composer di proyek ini, jadi autoloader PSR-4 ditulis manual
 * untuk namespace App\ yang dipetakan ke folder app/.
 */

define('TITIPIN_ROOT', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file     = TITIPIN_ROOT . '/app/' . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require TITIPIN_ROOT . '/app/helpers.php';
