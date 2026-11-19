<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Pembungkus sesi PHP: penyimpanan nilai, flash message, dan token CSRF.
 *
 * Cookie sesi dipasang HttpOnly + SameSite=Lax agar tidak bisa dibaca
 * JavaScript dan tidak ikut terkirim pada request lintas situs.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        /* Pilihan "Ingat saya" disimpan di cookie terpisah karena cookie
           sesi harus dipasang sebelum session_start() dipanggil. */
        $ingat    = ($_COOKIE['titipin_ingat'] ?? '') === '1';
        $lifetime = $ingat ? 60 * 60 * 24 * 30 : 0;

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        if ($ingat) {
            /* Perpanjang juga cookie sesinya, bukan hanya penandanya,
               supaya sesi benar-benar bertahan 30 hari. */
            setcookie(session_name(), session_id(), [
                'expires'  => time() + $lifetime,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            setcookie('titipin_ingat', '1', [
                'expires'  => time() + $lifetime,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Menyimpan pilihan "Ingat saya" selama 30 hari.
     */
    public static function remember(bool $ingat): void
    {
        if (!$ingat) {
            setcookie('titipin_ingat', '', ['expires' => time() - 3600, 'path' => '/']);

            return;
        }

        setcookie('titipin_ingat', '1', [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Menyimpan pesan sekali pakai untuk request berikutnya.
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Mengambil pesan flash lalu menghapusnya.
     */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;

        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    /**
     * Membaca pesan flash tanpa menghapusnya.
     *
     * Dipakai saat merender satu halaman yang membutuhkan beberapa nilai
     * flash sekaligus (misalnya daftar error validasi dan input lama);
     * penghapusan dilakukan sekali di akhir render.
     */
    public static function flashed(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_flash'][$key] ?? $default;
    }

    /**
     * Menghapus satu nilai flash tanpa mengembalikannya.
     */
    public static function forgetFlash(string $key): void
    {
        unset($_SESSION['_flash'][$key]);
    }

    /**
     * Token CSRF untuk sesi ini.
     */
    public static function csrfToken(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'];
    }

    /**
     * Memverifikasi token CSRF dengan perbandingan waktu tetap.
     */
    public static function verifyCsrf(?string $token): bool
    {
        $expected = $_SESSION['_csrf'] ?? null;

        return is_string($token)
            && is_string($expected)
            && hash_equals($expected, $token);
    }

    /**
     * Memulai ulang id sesi setelah login untuk mencegah session fixation.
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
