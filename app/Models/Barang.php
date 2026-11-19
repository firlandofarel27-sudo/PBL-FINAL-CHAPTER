<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Laporan barang hilang (Lost) dan barang temuan (Found).
 *
 * Status mengikuti enum PostgreSQL `status_barang`: Menunggu Verifikasi,
 * Tersedia, Selesai. Kolom `prioritas` menandai laporan yang perlu
 * ditangani lebih dulu tanpa menambah nilai enum baru.
 */
final class Barang extends Model
{
    private const SELECT = "
        SELECT b.*, u.nama_lengkap AS nama_pelapor,
               k.nama_kategori, l.nama_lokasi, l.gedung
        FROM barang b
        LEFT JOIN users u ON b.pelapor_id = u.nim
        LEFT JOIN kategori k ON b.kategori_id = k.id_kategori
        LEFT JOIN lokasi l ON b.lokasi_id = l.id_lokasi
    ";

    /**
     * Daftar barang dengan filter opsional.
     *
     * @param  array{jenis?: string, status?: string, pelapor?: string, q?: string, lokasi?: string, kategori?: string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public static function all(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['jenis'])) {
            $where[]  = 'b.jenis = ?';
            $params[] = $filters['jenis'];
        }

        if (!empty($filters['status'])) {
            $where[]  = 'b.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['pelapor'])) {
            $where[]  = 'b.pelapor_id = ?';
            $params[] = $filters['pelapor'];
        }

        if (!empty($filters['lokasi'])) {
            $where[]  = 'b.lokasi_id = ?';
            $params[] = $filters['lokasi'];
        }

        if (!empty($filters['kategori'])) {
            $where[]  = 'b.kategori_id = ?';
            $params[] = $filters['kategori'];
        }

        if (!empty($filters['q'])) {
            $where[]  = '(b.nama_barang ILIKE ? OR b.deskripsi ILIKE ? OR b.id_barang::text = ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = $filters['q'];
        }

        return self::rows(
            self::SELECT . self::where($where) . ' ORDER BY b.tanggal DESC NULLS LAST, b.id_barang DESC',
            $params
        );
    }

    public static function find(int $id): ?array
    {
        return self::row(self::SELECT . ' WHERE b.id_barang = ?', [$id]);
    }

    public static function create(array $data): int
    {
        return (int) self::scalar(
            'INSERT INTO barang
                (nama_barang, kategori_id, lokasi_id, lokasi_detail, waktu_kejadian, jenis, deskripsi, tanggal, foto, status, pelapor_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             RETURNING id_barang',
            [
                $data['nama_barang'],
                $data['kategori_id'] ?: null,
                $data['lokasi_id'] ?: null,
                $data['lokasi_detail'] ?: null,
                $data['waktu_kejadian'] ?: null,
                $data['jenis'],
                $data['deskripsi'] ?: null,
                $data['tanggal'],
                $data['foto'] ?? null,
                'Menunggu Verifikasi',
                $data['pelapor_id'],
            ]
        );
    }

    public static function updateStatus(int $id, string $status): void
    {
        self::execute('UPDATE barang SET status = ? WHERE id_barang = ?', [$status, $id]);
    }

    public static function setPrioritas(int $id, bool $prioritas): void
    {
        self::execute('UPDATE barang SET prioritas = ? WHERE id_barang = ?', [$prioritas, $id]);
    }

    /**
     * Menghapus barang dan mengembalikan nama berkas fotonya agar
     * pemanggil bisa menghapus file fisik.
     */
    public static function delete(int $id): ?string
    {
        return self::scalar('DELETE FROM barang WHERE id_barang = ? RETURNING foto', [$id]) ?: null;
    }

    /**
     * Barang temuan yang siap diklaim.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function tersedia(int $limit = 6): array
    {
        return self::rows(
            self::SELECT . " WHERE b.jenis = 'Found' AND b.status = 'Tersedia' ORDER BY b.tanggal DESC NULLS LAST, b.id_barang DESC LIMIT ?",
            [$limit]
        );
    }

    /**
     * @return array<string, int>
     */
    public static function stats(?string $pelapor = null): array
    {
        $filter = $pelapor === null ? '' : ' WHERE pelapor_id = ?';
        $params = $pelapor === null ? [] : [$pelapor];

        $row = self::row(
            "SELECT count(*) AS total,
                    count(*) FILTER (WHERE status = 'Menunggu Verifikasi') AS menunggu,
                    count(*) FILTER (WHERE status = 'Tersedia') AS tersedia,
                    count(*) FILTER (WHERE status = 'Selesai') AS selesai,
                    count(*) FILTER (WHERE jenis = 'Lost') AS hilang,
                    count(*) FILTER (WHERE jenis = 'Found') AS temuan,
                    count(*) FILTER (WHERE prioritas) AS prioritas
             FROM barang" . $filter,
            $params
        ) ?? [];

        return array_map('intval', $row);
    }
}
