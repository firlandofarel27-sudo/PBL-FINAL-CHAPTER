<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Perender template PHP dengan layout.
 *
 * Template halaman dirender lebih dulu menjadi string, lalu disisipkan ke
 * layout sebagai $content. Dengan begitu tidak ada view yang perlu tahu
 * struktur shell aplikasi.
 */
final class View
{
    public static function render(string $viewName, array $variables = [], string $layout = 'layouts/app'): never
    {
        $content = self::capture($viewName, $variables);

        $body = self::capture($layout, $variables + [
            'content' => $content,
            'title'   => $variables['title'] ?? config('app.name'),
        ]);

        /* Data lama dan pesan validasi hanya berlaku untuk satu tampilan. */
        Session::forgetFlash('_errors');
        Session::forgetFlash('_old');

        Response::html($body);
    }

    /**
     * Merender satu berkas view menjadi string.
     *
     * Nama parameter sengaja tidak memakai $data/$view agar variabel view
     * dengan nama umum tidak terlewat saat extract().
     */
    public static function capture(string $viewName, array $variables = []): string
    {
        $file = App::root() . '/views/' . $viewName . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException('View tidak ditemukan: ' . $viewName);
        }

        extract($variables, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}
