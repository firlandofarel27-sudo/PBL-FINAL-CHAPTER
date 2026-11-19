<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Mengatur siklus hidup aplikasi: konfigurasi runtime, sesi, dan routing.
 *
 * Seluruh request masuk lewat public/index.php lalu diteruskan ke kelas ini,
 * sehingga tidak ada berkas PHP lain yang bisa dipanggil langsung dari URL.
 */
final class App
{
    private static string $root = '';

    private static string $basePath = '';

    public function __construct(string $root)
    {
        self::$root = rtrim(str_replace('\\', '/', $root), '/');
    }

    /**
     * Lokasi akar proyek.
     */
    public static function root(): string
    {
        return self::$root;
    }

    /**
     * Awalan URL aplikasi, dihitung dari lokasi index.php.
     *
     * Aplikasi tetap berjalan baik dipasang di akar domain maupun di
     * subfolder seperti /PBL-FINAL-CHAPTER/public.
     */
    public static function basePath(): string
    {
        if (self::$basePath === '') {
            $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
            $dir    = rtrim(dirname($script), '/');

            self::$basePath = $dir === '' || $dir === '.' ? '' : $dir;
        }

        return self::$basePath;
    }

    /**
     * Menjalankan aplikasi: siapkan runtime lalu teruskan request ke router.
     */
    public function run(): void
    {
        date_default_timezone_set((string) config('app.timezone', 'Asia/Jakarta'));

        error_reporting(E_ALL);
        ini_set('display_errors', env('APP_DEBUG', false) ? '1' : '0');

        Session::start();

        $router = require self::$root . '/routes/web.php';

        try {
            $router->dispatch(Request::capture());
        } catch (Throwable $e) {
            error_log('[TITIPIN] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

            if (env('APP_DEBUG', false)) {
                Response::html(
                    '<pre style="padding:24px;font:13px/1.6 monospace">'
                    . e($e->getMessage()) . "\n\n" . e($e->getTraceAsString())
                    . '</pre>',
                    500
                );
            }

            Response::html('Terjadi kesalahan pada server.', 500);
        }
    }
}
