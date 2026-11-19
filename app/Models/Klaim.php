<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Tiket klaim barang temuan.
 *
 * Satu klaim menyimpan kode tiket (untuk ditunjukkan ke petugas) dan hash
 * kode redeem 5 karakter (untuk diserahkan ke admin saat serah terima).
 */
final class Klaim extends Model
{
    private const SELECT = "
        SELECT k.*, b.nama_barang, b.foto AS foto_barang, b.jenis,
               b.status AS status_barang, b.lokasi_id, b.pelapor_id,
               u.nama_lengkap AS nama_claimer,
               a.nama_lengkap AS nama_admin
        FROM klaim k
        JOIN barang b ON k.item_id = b.id_barang
        JOIN users u ON k.claimer_id = u.nim
        LEFT JOIN users a ON k.admin_id = a.nim
    ";

    /**
     * Klaim milik satu pengguna.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function forClaimer(string $nim): array
    {
        return self::rows(
            self::SELECT . ' WHERE k.claimer_id = ? ORDER BY k.id_klaim DESC',
            [$nim]
        );
    }

    /**
     * Seluruh klaim untuk admin.
     *
     * @param  array{status?: string, q?: string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public static function all(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'k.status_klaim = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $where[]  = '(k.kode_tiket ILIKE ? OR b.nama_barang ILIKE ? OR u.nama_lengkap ILIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        return self::rows(
            self::SELECT . self::where($where) . ' ORDER BY k.id_klaim DESC',
            $params
        );
    }

    public static function find(int $id): ?array
    {
        return self::row(self::SELECT . ' WHERE k.id_klaim = ?', [$id]);
    }

    public static function findByTicket(string $ticket): ?array
    {
        return self::row(self::SELECT . ' WHERE upper(k.kode_tiket) = upper(?)', [$ticket]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function pendingForItem(int $itemId): array
    {
        return self::rows(
            "SELECT id_klaim, claimer_id FROM klaim WHERE item_id = ? AND status_klaim = 'Diajukan'",
            [$itemId]
        );
    }

    public static function hasActiveClaim(int $itemId, ?string $nim): bool
    {
        if ($nim === null || $nim === '') {
            return false;
        }

        return self::scalar(
            "SELECT 1 FROM klaim WHERE item_id = ? AND claimer_id = ? AND status_klaim = 'Diajukan'",
            [$itemId, $nim]
        ) !== false;
    }

    /**
     * Menutup klaim yang kode redeem-nya sudah lewat masa berlaku.
     *
     * Dijalankan malas (lazy) pada setiap request yang menyentuh klaim,
     * sehingga tidak memerlukan cron.
     */
    public static function expire(): void
    {
        self::execute(
            "UPDATE klaim
             SET status_klaim = 'Ditolak',
                 alasan_penolakan = 'Kode redeem kedaluwarsa (24 jam).'
             WHERE status_klaim = 'Diajukan'
               AND redeem_expires_at IS NOT NULL
               AND redeem_expires_at <= NOW()"
        );
    }

    /**
     * Menolak klaim lain pada barang yang sama setelah satu klaim disetujui.
     */
    public static function rejectOthers(int $itemId, int $exceptClaimId, string $reason): void
    {
        self::execute(
            "UPDATE klaim
             SET status_klaim = 'Ditolak', alasan_penolakan = ?
             WHERE item_id = ? AND id_klaim <> ? AND status_klaim = 'Diajukan'",
            [$reason, $itemId, $exceptClaimId]
        );
    }

    public static function reject(int $id, string $reason): void
    {
        self::execute(
            "UPDATE klaim SET status_klaim = 'Ditolak', alasan_penolakan = ? WHERE id_klaim = ?",
            [$reason, $id]
        );
    }

    /**
     * Menghapus klaim dan mengembalikan nama berkas bukti untuk dibersihkan.
     *
     * @return array<int, string>
     */
    public static function delete(int $id): array
    {
        $row = self::row(
            'DELETE FROM klaim WHERE id_klaim = ? RETURNING bukti_kepemilikan, bukti_serah_terima',
            [$id]
        );

        if ($row === null) {
            return [];
        }

        return array_values(array_filter([
            $row['bukti_kepemilikan'],
            $row['bukti_serah_terima'],
        ]));
    }

    public static function pendingCount(): int
    {
        return (int) self::scalar("SELECT count(*) FROM klaim WHERE status_klaim = 'Diajukan'");
    }

    /**
     * @return array<string, int>
     */
    public static function stats(?string $claimer = null): array
    {
        $filter = $claimer === null ? '' : ' WHERE claimer_id = ?';
        $params = $claimer === null ? [] : [$claimer];

        $row = self::row(
            "SELECT count(*) AS total,
                    count(*) FILTER (WHERE status_klaim = 'Diajukan') AS diajukan,
                    count(*) FILTER (WHERE status_klaim = 'Disetujui') AS disetujui,
                    count(*) FILTER (WHERE status_klaim = 'Ditolak') AS ditolak
             FROM klaim" . $filter,
            $params
        ) ?? [];

        return array_map('intval', $row);
    }
}
