<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private static ?array $user = null;

    private static bool $loaded = false;

    /**
     * Data pengguna yang sedang login, atau null bila belum login.
     */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }

        self::$loaded = true;

        $nim = Session::get('user_nim');

        if (!is_string($nim) || $nim === '') {
            return null;
        }

        $stmt = Database::pdo()->prepare(
            'SELECT nim, username, nama_lengkap, email, role, jurusan, kelas, no_telepon, foto_profil, status_akun
             FROM users
             WHERE nim = ?'
        );
        $stmt->execute([$nim]);

        $user = $stmt->fetch();

        if (!$user || $user['status_akun'] === 'Ditangguhkan') {
            Session::forget('user_nim');

            return null;
        }

        self::$user = $user;

        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?string
    {
        return self::user()['nim'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    /**
     * Memeriksa apakah pengguna memiliki salah satu peran yang diminta.
     */
    public static function is(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    /**
     * Menyimpan identitas ke sesi setelah kredensial terbukti benar.
     */
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_nim', $user['nim']);

        self::$user   = $user;
        self::$loaded = true;
    }

    /**
     * Menghapus sesi login sepenuhnya.
     */
    public static function logout(): void
    {
        Session::forget('user_nim');
        Session::regenerate();

        self::$user   = null;
        self::$loaded = true;
    }
}
