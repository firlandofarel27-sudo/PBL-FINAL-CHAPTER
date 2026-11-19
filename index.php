<?php

declare(strict_types=1);

/**
 * Pengalih untuk document root proyek.
 *
 * Berkas asli aplikasi berada di public/ agar app/, config/, dan views/
 * tidak bisa diakses langsung dari browser. Berkas ini hanya meneruskan
 * request ke sana sehingga URL akar tetap bisa dibuka.
 */

$base = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'))), '/');
$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($base !== '' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}

$path = (string) preg_replace('#^/public(?=/|$)#', '', $path);

header('Location: ' . $base . '/public/' . ltrim($path, '/'), true, 302);

exit;
