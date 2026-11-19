<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Pengaturan sistem berbentuk pasangan kunci-nilai.
 *
 * Halaman Pengaturan hanya menyunting tabel ini, sehingga menambah opsi
 * baru tidak memerlukan perubahan skema.
 */
final class Pengaturan extends Model
{
    /**
     * Nilai bawaan bila baris belum ada di database.
     */
    public const DEFAULTS = [
        'nama_aplikasi'    => 'TITIPIN Campus Care',
        'retensi_hari'     => '90',
        'target_selesai'   => '85',
        'notifikasi_email' => 'Aktif',
    ];

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        $values = self::DEFAULTS;

        foreach (self::rows('SELECT kunci, nilai FROM pengaturan') as $row) {
            $values[$row['kunci']] = (string) $row['nilai'];
        }

        return $values;
    }

    public static function get(string $key): string
    {
        $value = self::scalar('SELECT nilai FROM pengaturan WHERE kunci = ?', [$key]);

        return $value === false ? (self::DEFAULTS[$key] ?? '') : (string) $value;
    }

    /**
     * Menyimpan banyak nilai sekaligus dalam satu transaksi.
     *
     * @param  array<string, string>  $values
     */
    public static function put(array $values): void
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?)
                 ON CONFLICT (kunci) DO UPDATE SET nilai = EXCLUDED.nilai'
            );

            foreach ($values as $key => $value) {
                $stmt->execute([$key, $value]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }
}
