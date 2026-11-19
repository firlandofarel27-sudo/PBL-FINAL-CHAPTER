<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\AppException;
use App\Core\Database;
use App\Models\Barang;
use App\Models\Klaim;
use PDO;

/**
 * Aturan bisnis klaim barang temuan.
 *
 * Seluruh keputusan penting (barang harus temuan dan tersedia, satu klaim
 * aktif per pengguna per barang, bukti wajib, kode redeem ber-hash dengan
 * masa berlaku 24 jam) berada di kelas ini agar tidak terduplikasi di
 * controller maupun view.
 */
final class ClaimService
{
    /**
     * Mengajukan klaim baru dan mengembalikan kode redeem sekali pakai.
     *
     * @param  array{name: string, type: string, tmp_name: string, error: int, size: int}  $file
     * @return array{klaim: array<string, mixed>, redeem_code: string}
     *
     * @throws AppException Bila klaim tidak memenuhi aturan.
     */
    public static function create(int $itemId, string $claimerNim, array $file, string $ciriKhusus = ''): array
    {
        Klaim::expire();

        $barang = self::availableItem($itemId, $claimerNim);

        if (Klaim::hasActiveClaim($itemId, $claimerNim)) {
            throw new AppException('Kamu sudah punya klaim aktif untuk barang ini.');
        }

        $bukti = UploadService::store($file, 'bukti', 'klaim');

        $pdo = Database::pdo();

        try {
            $pdo->beginTransaction();

            /* Kunci baris barang agar dua pengguna tidak bisa mengklaim
               barang yang sama pada saat bersamaan. */
            $stmt = $pdo->prepare(
                "SELECT id_barang, jenis, status, pelapor_id FROM barang WHERE id_barang = ? FOR UPDATE"
            );
            $stmt->execute([$itemId]);
            $locked = $stmt->fetch();

            if (!$locked || $locked['jenis'] !== 'Found' || $locked['status'] !== 'Tersedia') {
                throw new AppException('Barang tidak tersedia untuk diklaim saat ini.');
            }

            if ($locked['pelapor_id'] === $claimerNim) {
                throw new AppException('Kamu tidak dapat mengklaim barang yang kamu laporkan sendiri.');
            }

            if (Klaim::hasActiveClaim($itemId, $claimerNim)) {
                throw new AppException('Kamu sudah punya klaim aktif untuk barang ini.');
            }

            $redeemCode = self::generateRedeemCode();
            $kodeTiket  = 'KLM-' . strtoupper(bin2hex(random_bytes(4)));

            $insert = $pdo->prepare(
                "INSERT INTO klaim
                    (kode_tiket, item_id, claimer_id, bukti_kepemilikan, ciri_khusus, status_klaim, redeem_code_hash, redeem_expires_at)
                 VALUES (?, ?, ?, ?, ?, 'Diajukan', ?, NOW() + make_interval(hours => ?))
                 RETURNING id_klaim, kode_tiket, redeem_expires_at"
            );
            $insert->execute([
                $kodeTiket,
                $itemId,
                $claimerNim,
                $bukti,
                $ciriKhusus === '' ? null : $ciriKhusus,
                password_hash($redeemCode, PASSWORD_DEFAULT),
                (int) config('app.redeem.ttl_hours', 24),
            ]);

            $klaim = $insert->fetch();

            $pdo->commit();

            return [
                'klaim'       => $klaim,
                'redeem_code' => $redeemCode,
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            UploadService::delete($bukti);

            if ($e instanceof AppException) {
                throw $e;
            }

            if (str_contains($e->getMessage(), 'klaim_satu_pending_per_user_barang')) {
                throw new AppException('Kamu sudah punya klaim aktif untuk barang ini.');
            }

            throw $e;
        }
    }

    /**
     * Menyetujui klaim dengan memverifikasi kode redeem dari pengklaim.
     *
     * Barang langsung ditandai Selesai dan klaim pending lain pada barang
     * yang sama otomatis ditolak.
     *
     * @return array<string, mixed>
     *
     * @throws AppException Bila kode salah, kedaluwarsa, atau barang sudah diambil.
     */
    public static function redeem(int $claimId, string $code, string $adminNim): array
    {
        Klaim::expire();

        $code = self::normalizeCode($code);

        if (!self::isValidCodeFormat($code)) {
            throw new AppException('Kode redeem harus 5 karakter huruf atau angka.');
        }

        $pdo = Database::pdo();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT k.*, b.nama_barang, b.status AS status_barang,
                        CASE WHEN k.redeem_expires_at IS NULL OR k.redeem_expires_at <= NOW() THEN 1 ELSE 0 END AS redeem_expired
                 FROM klaim k
                 JOIN barang b ON b.id_barang = k.item_id
                 WHERE k.id_klaim = ?
                 FOR UPDATE OF k, b'
            );
            $stmt->execute([$claimId]);
            $klaim = $stmt->fetch();

            if (!$klaim) {
                throw new AppException('Klaim tidak ditemukan.');
            }

            if ($klaim['status_klaim'] === 'Disetujui') {
                throw new AppException('Klaim ini sudah disetujui dan tidak bisa diredeem lagi.');
            }

            if ($klaim['status_klaim'] === 'Ditolak') {
                throw new AppException(
                    'Klaim tidak dapat diproses: ' . ($klaim['alasan_penolakan'] ?: 'klaim sudah ditolak.')
                );
            }

            if ((int) $klaim['redeem_expired'] === 1) {
                Klaim::reject($claimId, 'Kode redeem kedaluwarsa (24 jam).');
                $pdo->commit();

                throw new AppException('Kode redeem sudah kedaluwarsa. Klaim otomatis ditolak.');
            }

            if (empty($klaim['redeem_code_hash'])) {
                throw new AppException('Klaim ini tidak memiliki kode redeem.');
            }

            if (!password_verify($code, (string) $klaim['redeem_code_hash'])) {
                throw new AppException('Kode redeem tidak valid.');
            }

            if (empty($klaim['bukti_kepemilikan'])) {
                throw new AppException('Bukti kepemilikan belum diunggah. Klaim tidak dapat disetujui.');
            }

            if ($klaim['status_barang'] !== 'Tersedia') {
                if ($klaim['status_barang'] === 'Selesai') {
                    Klaim::reject($claimId, 'Barang sudah diklaim pengguna lain.');
                    $pdo->commit();

                    throw new AppException('Barang sudah diklaim pengguna lain. Klaim ini otomatis ditolak.');
                }

                throw new AppException(
                    'Barang tidak tersedia untuk diserahkan. Status saat ini: ' . $klaim['status_barang'] . '.'
                );
            }

            $update = $pdo->prepare(
                "UPDATE klaim
                 SET status_klaim = 'Disetujui', admin_id = ?, redeem_used_at = NOW(), alasan_penolakan = NULL
                 WHERE id_klaim = ?"
            );
            $update->execute([$adminNim, $claimId]);

            $close = $pdo->prepare(
                "UPDATE barang SET status = 'Selesai' WHERE id_barang = ? AND status = 'Tersedia'"
            );
            $close->execute([$klaim['item_id']]);

            if ($close->rowCount() !== 1) {
                throw new AppException('Barang gagal ditutup karena statusnya sudah berubah.');
            }

            Klaim::rejectOthers((int) $klaim['item_id'], $claimId, 'Barang sudah diklaim pengguna lain.');

            $pdo->commit();

            return $klaim;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Kode redeem acak 5 karakter tanpa huruf/angka yang mudah tertukar.
     */
    public static function generateRedeemCode(): string
    {
        $alphabet = (string) config('app.redeem.alphabet');
        $length   = (int) config('app.redeem.length', 5);
        $max      = strlen($alphabet) - 1;
        $code     = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }

        return $code;
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    public static function isValidCodeFormat(string $code): bool
    {
        $alphabet = (string) config('app.redeem.alphabet');

        return strlen($code) === (int) config('app.redeem.length', 5)
            && strspn($code, $alphabet) === strlen($code);
    }

    /**
     * Memastikan barang boleh diklaim oleh pengguna ini.
     *
     * @return array<string, mixed>
     */
    private static function availableItem(int $itemId, string $claimerNim): array
    {
        $barang = Barang::find($itemId);

        if ($barang === null) {
            throw new AppException('Barang tidak ditemukan.');
        }

        if ($barang['jenis'] !== 'Found') {
            throw new AppException('Hanya barang temuan yang dapat diklaim.');
        }

        if ($barang['status'] !== 'Tersedia') {
            throw new AppException(
                'Barang belum bisa diklaim. Status saat ini: ' . $barang['status'] . '.'
            );
        }

        if ($barang['pelapor_id'] === $claimerNim) {
            throw new AppException('Kamu tidak dapat mengklaim barang yang kamu laporkan sendiri.');
        }

        return $barang;
    }
}
