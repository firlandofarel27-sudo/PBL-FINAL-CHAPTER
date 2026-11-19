<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Koneksi PDO PostgreSQL tunggal untuk seluruh aplikasi.
 *
 * Kredensial dibaca dari .env; bila koneksi gagal, pesan asli hanya
 * ditulis ke log agar detail server tidak bocor ke pengguna.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = (string) env('DB_HOST', 'localhost');
        $port = (string) env('DB_PORT', '5432');
        $name = (string) env('DB_NAME', 'titipin');
        $user = (string) env('DB_USER', 'postgres');
        $pass = (string) env('DB_PASS', '');

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (\PDOException $e) {
            error_log('[TITIPIN] koneksi database gagal: ' . $e->getMessage());

            Response::html('Koneksi database gagal. Periksa berkas .env.', 500);
        }

        return self::$pdo;
    }
}
