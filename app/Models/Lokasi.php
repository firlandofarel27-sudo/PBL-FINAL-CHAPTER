<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Master lokasi kampus.
 *
 * Satu baris = satu ruangan. Kolom `gedung` menyimpan lantai (misalnya
 * "Lantai 5 Barat"), sehingga formulir laporan bisa menampilkan dua
 * pilihan bertingkat: lantai lalu ruangan.
 *
 * Baris lama yang sudah tidak dipakai tidak dihapus, hanya dinonaktifkan,
 * supaya laporan lama tetap menunjuk lokasi yang benar.
 */
final class Lokasi extends Model
{
    /**
     * Lokasi yang masih aktif, diurutkan menurut lantai lalu nama ruangan.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::rows(
            'SELECT id_lokasi, nama_lokasi, gedung
             FROM lokasi
             WHERE aktif
             ORDER BY gedung, nama_lokasi'
        );
    }

    /**
     * Lokasi aktif dikelompokkan per lantai untuk dropdown bertingkat.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function grouped(): array
    {
        $groups = [];

        foreach (self::all() as $lokasi) {
            $groups[(string) $lokasi['gedung']][] = $lokasi;
        }

        return $groups;
    }

    public static function create(string $nama, string $gedung): void
    {
        self::execute(
            'INSERT INTO lokasi (nama_lokasi, gedung, aktif) VALUES (?, ?, true)',
            [$nama, $gedung === '' ? null : $gedung]
        );
    }

    /**
     * Satu lokasi aktif berdasarkan id.
     *
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        return self::row(
            'SELECT id_lokasi, nama_lokasi, gedung FROM lokasi WHERE id_lokasi = ? AND aktif',
            [$id]
        );
    }

    /**
     * Memperbarui nama ruangan dan lantainya.
     *
     * Status `aktif` sengaja tidak disentuh agar penyuntingan tidak
     * diam-diam mengaktifkan kembali lokasi yang sudah dinonaktifkan.
     */
    public static function update(int $id, string $nama, string $gedung): void
    {
        self::execute(
            'UPDATE lokasi SET nama_lokasi = ?, gedung = ? WHERE id_lokasi = ?',
            [$nama, $gedung === '' ? null : $gedung, $id]
        );
    }

    /**
     * Pencarian lokasi aktif untuk halaman manajemen.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function search(string $keyword, string $gedung): array
    {
        $where  = ['aktif'];
        $params = [];

        if ($keyword !== '') {
            $where[]  = '(nama_lokasi ILIKE ? OR gedung ILIKE ?)';
            $params[] = '%' . $keyword . '%';
            $params[] = '%' . $keyword . '%';
        }

        if ($gedung !== '') {
            $where[]  = 'gedung = ?';
            $params[] = $gedung;
        }

        return self::rows(
            'SELECT id_lokasi, nama_lokasi, gedung FROM lokasi'
            . self::where($where)
            . ' ORDER BY gedung, nama_lokasi',
            $params
        );
    }

    /**
     * Jumlah laporan yang memakai setiap lokasi, dikunci berdasarkan id.
     *
     * Satu query untuk seluruh baris agar halaman tidak menjalankan
     * query per baris tabel.
     *
     * @return array<int, int>
     */
    public static function usageMap(): array
    {
        $rows = self::rows(
            'SELECT lokasi_id, count(*) AS jumlah FROM (
                 SELECT lokasi_id FROM barang WHERE lokasi_id IS NOT NULL
                 UNION ALL
                 SELECT lokasi_id FROM kerusakan_fasilitas WHERE lokasi_id IS NOT NULL
             ) AS pakai
             GROUP BY lokasi_id'
        );

        $map = [];

        foreach ($rows as $row) {
            $map[(int) $row['lokasi_id']] = (int) $row['jumlah'];
        }

        return $map;
    }

    /**
     * Jumlah barang temuan yang tercatat di setiap lokasi.
     *
     * Angka ini dipakai untuk mengetahui ruangan mana yang paling sering
     * menjadi tempat barang tertinggal, sehingga pengawasan bisa
     * diprioritaskan ke ruangan tersebut.
     *
     * @return array<int, int>
     */
    public static function foundMap(): array
    {
        $rows = self::rows(
            "SELECT lokasi_id, count(*) AS jumlah
             FROM barang
             WHERE jenis = 'Found' AND lokasi_id IS NOT NULL
             GROUP BY lokasi_id"
        );

        $map = [];

        foreach ($rows as $row) {
            $map[(int) $row['lokasi_id']] = (int) $row['jumlah'];
        }

        return $map;
    }

    /**
     * Jumlah lokasi aktif per lantai.
     *
     * @return array<string, int>
     */
    public static function countPerFloor(): array
    {
        $rows = self::rows(
            'SELECT coalesce(gedung, \'Tanpa lantai\') AS lantai, count(*) AS jumlah
             FROM lokasi WHERE aktif
             GROUP BY 1 ORDER BY 1'
        );

        $map = [];

        foreach ($rows as $row) {
            $map[(string) $row['lantai']] = (int) $row['jumlah'];
        }

        return $map;
    }

    /**
     * Menonaktifkan lokasi agar tidak muncul lagi di formulir, tanpa
     * merusak riwayat laporan yang memakainya.
     */
    public static function deactivate(int $id): void
    {
        self::execute('UPDATE lokasi SET aktif = false WHERE id_lokasi = ?', [$id]);
    }

    public static function exists(int $id): bool
    {
        return self::scalar('SELECT 1 FROM lokasi WHERE id_lokasi = ? AND aktif', [$id]) !== false;
    }

    /**
     * Jumlah laporan barang dan fasilitas yang memakai satu lokasi.
     */
    public static function usage(int $id): int
    {
        return (int) self::scalar(
            'SELECT (SELECT count(*) FROM barang WHERE lokasi_id = ?)
                  + (SELECT count(*) FROM kerusakan_fasilitas WHERE lokasi_id = ?)',
            [$id, $id]
        );
    }

    public static function countAll(): int
    {
        return (int) self::scalar('SELECT count(*) FROM lokasi WHERE aktif');
    }

    /**
     * Daftar lantai aktif, dipakai untuk pilihan pertama pada formulir.
     *
     * @return array<int, string>
     */
    public static function floors(): array
    {
        return array_keys(self::grouped());
    }
}
