<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Data pengguna kampus beserta perannya.
 *
 * Primary key memakai NIM (varchar) mengikuti skema PostgreSQL yang sudah
 * dipakai backend sebelumnya.
 */
final class User extends Model
{
    private const COLUMNS = 'nim, username, nama_lengkap, email, role, jurusan, kelas, no_telepon, foto_profil, status_akun';

    public static function findByEmail(string $email): ?array
    {
        return self::row(
            'SELECT ' . self::COLUMNS . ', password FROM users WHERE lower(email) = lower(?)',
            [$email]
        );
    }

    public static function findByNim(string $nim): ?array
    {
        return self::row('SELECT ' . self::COLUMNS . ' FROM users WHERE nim = ?', [$nim]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::rows('SELECT ' . self::COLUMNS . ' FROM users ORDER BY role, nama_lengkap');
    }

    /**
     * Daftar pengguna dengan pencarian nama/email/NIM.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function search(string $keyword, string $role = ''): array
    {
        $params = [];
        $where  = [];

        if ($keyword !== '') {
            $where[]  = '(nama_lengkap ILIKE ? OR email ILIKE ? OR nim ILIKE ?)';
            $params[] = '%' . $keyword . '%';
            $params[] = '%' . $keyword . '%';
            $params[] = '%' . $keyword . '%';
        }

        if ($role !== '') {
            $where[]  = 'role = ?';
            $params[] = $role;
        }

        return self::rows(
            'SELECT ' . self::COLUMNS . ' FROM users'
            . self::where($where)
            . ' ORDER BY role, nama_lengkap',
            $params
        );
    }

    public static function create(array $data): void
    {
        self::execute(
            'INSERT INTO users (nim, username, nama_lengkap, email, password, role, jurusan, kelas, no_telepon, status_akun)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['nim'],
                $data['username'],
                $data['nama_lengkap'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $data['jurusan'] ?: null,
                $data['kelas'] ?: null,
                $data['no_telepon'] ?: null,
                $data['status_akun'] ?? 'Aktif',
            ]
        );
    }

    /**
     * Memperbarui kolom yang dikirim saja.
     */
    public static function update(string $nim, array $data): void
    {
        $set    = [];
        $params = [];

        foreach (['username', 'nama_lengkap', 'email', 'role', 'status_akun'] as $column) {
            if (array_key_exists($column, $data)) {
                $set[]    = $column . ' = ?';
                $params[] = $data[$column];
            }
        }

        foreach (['jurusan', 'kelas', 'no_telepon', 'foto_profil'] as $column) {
            if (array_key_exists($column, $data)) {
                $set[]    = $column . ' = ?';
                $params[] = $data[$column] === '' ? null : $data[$column];
            }
        }

        if (!empty($data['password'])) {
            $set[]    = 'password = ?';
            $params[] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }

        if ($set === []) {
            return;
        }

        $params[] = $nim;

        self::execute('UPDATE users SET ' . implode(', ', $set) . ' WHERE nim = ?', $params);
    }

    public static function delete(string $nim): void
    {
        self::execute('DELETE FROM users WHERE nim = ?', [$nim]);
    }

    public static function countByRole(string $role): int
    {
        return (int) self::scalar('SELECT count(*) FROM users WHERE role = ?', [$role]);
    }

    /**
     * Jumlah akun dengan status tertentu, misalnya "Ditangguhkan".
     */
    public static function countByStatus(string $status): int
    {
        return (int) self::scalar('SELECT count(*) FROM users WHERE status_akun = ?', [$status]);
    }

    public static function countAll(): int
    {
        return (int) self::scalar('SELECT count(*) FROM users');
    }

    /**
     * Jumlah pengguna aktif dalam 30 hari terakhir (dianggap aktif bila
     * punya laporan atau klaim).
     */
    public static function countActive(): int
    {
        return (int) self::scalar(
            "SELECT count(DISTINCT pelapor_id) FROM (
                SELECT pelapor_id FROM barang WHERE pelapor_id IS NOT NULL
                UNION
                SELECT pelapor_id FROM kerusakan_fasilitas WHERE pelapor_id IS NOT NULL
                UNION
                SELECT claimer_id FROM klaim
            ) AS aktif"
        );
    }

    public static function existsNim(string $nim): bool
    {
        return self::scalar('SELECT 1 FROM users WHERE nim = ?', [$nim]) !== false;
    }

    public static function existsEmail(string $email, ?string $exceptNim = null): bool
    {
        $sql    = 'SELECT 1 FROM users WHERE lower(email) = lower(?)';
        $params = [$email];

        if ($exceptNim !== null) {
            $sql .= ' AND nim <> ?';
            $params[] = $exceptNim;
        }

        return self::scalar($sql, $params) !== false;
    }
}
