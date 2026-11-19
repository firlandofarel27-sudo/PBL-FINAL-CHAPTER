<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Mengirim respons HTTP lalu menghentikan eksekusi.
 *
 * Semua controller memakai kelas ini agar tidak ada controller yang
 * menulis header sendiri.
 */
final class Response
{
    /**
     * Mengirim halaman HTML.
     */
    public static function html(string $body, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=UTF-8');
        echo $body;

        exit;
    }

    /**
     * Mengirim payload JSON.
     */
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }

    /**
     * Mengalihkan ke URL internal aplikasi.
     */
    public static function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . url($path), true, $status);

        exit;
    }

    /**
     * Mengalihkan ke halaman sebelumnya, atau ke beranda bila tidak diketahui.
     */
    public static function back(string $fallback = '/beranda'): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $base    = App::basePath();
        $target  = $fallback;

        if ($referer !== '') {
            $path = (string) parse_url($referer, PHP_URL_PATH);

            if ($base !== '' && str_starts_with($path, $base)) {
                $path = substr($path, strlen($base));
            }

            if ($path !== '' && str_starts_with($path, '/')) {
                $target = $path;
            }
        }

        self::redirect($target);
    }
}
