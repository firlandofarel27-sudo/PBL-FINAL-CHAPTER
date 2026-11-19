<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Laporan kerusakan fasilitas kampus.
 *
 * Status mengikuti enum PostgreSQL `status_perbaikan`: Dilaporkan,
 * Proses Perbaikan, Selesai.
 */
final class Fasilitas extends Model
{
    private const SELECT = "
        SELECT f.*, u.nama_lengkap AS nama_pelapor,
               k.nama_kategori, l.nama_lokasi, l.gedung
        FROM kerusakan_fasilitas f
        LEFT JOIN users u ON f.pelapor_id = u.nim
        LEFT JOIN kategori k ON f.kategori_id = k.id_kategori
        LEFT JOIN lokasi l ON f.lokasi_id = l.id_lokasi
    ";

    /**
     * @param  array{status?: string, pelapor?: string, q?: string, lokasi?: string, kategori?: string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public static function all(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'f.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['pelapor'])) {
            $where[]  = 'f.pelapor_id = ?';
            $params[] = $filters['pelapor'];
        }

        if (!empty($filters['lokasi'])) {
            $where[]  = 'f.lokasi_id = ?';
            $params[] = $filters['lokasi'];
        }

        if (!empty($filters['kategori'])) {
            $where[]  = 'f.kategori_id = ?';
            $params[] = $filters['kategori'];
        }

        if (!empty($filters['q'])) {
            $where[]  = '(f.nama_fasilitas ILIKE ? OR f.deskripsi_kerusakan ILIKE ? OR f.id_kerusakan::text = ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = $filters['q'];
        }

        return self::rows(
            self::SELECT . self::where($where) . ' ORDER BY f.tanggal_lapor DESC NULLS LAST, f.id_kerusakan DESC',
            $params
        );
    }

    public static function find(int $id): ?array
    {
        return self::row(self::SELECT . ' WHERE f.id_kerusakan = ?', [$id]);
    }

    public static function create(array $data): int
    {
        return (int) self::scalar(
            'INSERT INTO kerusakan_fasilitas
                (nama_fasilitas, kategori_id, lokasi_id, lokasi_detail, waktu_kejadian, deskripsi_kerusakan, foto, tanggal_lapor, status, pelapor_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
             RETURNING id_kerusakan',
            [
                $data['nama_fasilitas'],
                $data['kategori_id'] ?: null,
                $data['lokasi_id'] ?: null,
                $data['lokasi_detail'] ?: null,
                $data['waktu_kejadian'] ?: null,
                $data['deskripsi_kerusakan'],
                $data['foto'] ?? null,
                $data['tanggal_lapor'],
                'Dilaporkan',
                $data['pelapor_id'],
            ]
        );
    }

    public static function updateStatus(int $id, string $status, string $catatan = ''): void
    {
        self::execute(
            'UPDATE kerusakan_fasilitas SET status = ?, catatan_teknisi = ? WHERE id_kerusakan = ?',
            [$status, $catatan === '' ? null : $catatan, $id]
        );
    }

    public static function delete(int $id): ?string
    {
        return self::scalar(
            'DELETE FROM kerusakan_fasilitas WHERE id_kerusakan = ? RETURNING foto',
            [$id]
        ) ?: null;
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
                    count(*) FILTER (WHERE status = 'Dilaporkan') AS dilaporkan,
                    count(*) FILTER (WHERE status = 'Proses Perbaikan') AS proses,
                    count(*) FILTER (WHERE status = 'Selesai') AS selesai
             FROM kerusakan_fasilitas" . $filter,
            $params
        ) ?? [];

        return array_map('intval', $row);
    }
}
