<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Barang;
use App\Models\Fasilitas;
use App\Models\Klaim;

/**
 * Daftar notifikasi untuk lonceng pada topbar.
 *
 * Notifikasi dihitung dari keadaan data saat ini, bukan dari tabel
 * terpisah, sehingga tidak ada penghitung yang bisa tertinggal:
 *
 * - Mahasiswa: perubahan status laporan dan klaim miliknya.
 * - Admin/Super Admin: pekerjaan yang menunggu tindakan.
 */
final class NotifikasiService
{
    private const MAKSIMAL = 6;

    /**
     * @return array<int, array{judul: string, keterangan: string, tautan: string, nada: string}>
     */
    public static function untuk(?array $user): array
    {
        if ($user === null) {
            return [];
        }

        return match ($user['role']) {
            'Mahasiswa'             => self::mahasiswa((string) $user['nim']),
            'Admin', 'Super Admin'  => self::petugas(),
            default                 => [],
        };
    }

    /**
     * Notifikasi untuk pemilik laporan dan klaim.
     *
     * @return array<int, array{judul: string, keterangan: string, tautan: string, nada: string}>
     */
    private static function mahasiswa(string $nim): array
    {
        Klaim::expire();

        $items = [];

        foreach (Klaim::forClaimer($nim) as $klaim) {
            $barang = (string) $klaim['nama_barang'];

            if ($klaim['status_klaim'] === 'Disetujui') {
                $items[] = [
                    'judul'      => 'Klaim disetujui',
                    'keterangan' => $barang . ' sudah bisa diambil di posko.',
                    'tautan'     => '/klaim/' . (int) $klaim['id_klaim'],
                    'nada'       => 'green',
                ];

                continue;
            }

            if ($klaim['status_klaim'] === 'Ditolak') {
                $items[] = [
                    'judul'      => 'Klaim ditolak',
                    'keterangan' => $barang . ' — ' . ((string) ($klaim['alasan_penolakan'] ?: 'ditolak petugas.')),
                    'tautan'     => '/klaim/' . (int) $klaim['id_klaim'],
                    'nada'       => 'red',
                ];

                continue;
            }

            $items[] = [
                'judul'      => 'Klaim menunggu verifikasi',
                'keterangan' => $barang . ' · kode berlaku ' . sisa_waktu((string) $klaim['redeem_expires_at']) . '.',
                'tautan'     => '/klaim/' . (int) $klaim['id_klaim'],
                'nada'       => 'blue',
            ];
        }

        foreach (Barang::all(['pelapor' => $nim]) as $barang) {
            if ($barang['status'] === 'Tersedia') {
                $items[] = [
                    'judul'      => 'Laporan diverifikasi',
                    'keterangan' => (string) $barang['nama_barang'] . ' sudah tampil di katalog.',
                    'tautan'     => '/laporan/barang/' . (int) $barang['id_barang'],
                    'nada'       => 'blue',
                ];
            }

            if ($barang['status'] === 'Selesai') {
                $items[] = [
                    'judul'      => 'Laporan selesai',
                    'keterangan' => (string) $barang['nama_barang'] . ' sudah diserahterimakan.',
                    'tautan'     => '/laporan/barang/' . (int) $barang['id_barang'],
                    'nada'       => 'green',
                ];
            }
        }

        return array_slice($items, 0, self::MAKSIMAL);
    }

    /**
     * Notifikasi pekerjaan yang menunggu petugas.
     *
     * @return array<int, array{judul: string, keterangan: string, tautan: string, nada: string}>
     */
    private static function petugas(): array
    {
        Klaim::expire();

        $items = [];

        $barang    = Barang::stats();
        $fasilitas = Fasilitas::stats();
        $klaim     = Klaim::pendingCount();

        if ($barang['menunggu'] > 0) {
            $items[] = [
                'judul'      => $barang['menunggu'] . ' laporan barang menunggu verifikasi',
                'keterangan' => 'Ada laporan baru dari mahasiswa yang perlu diperiksa.',
                'tautan'     => '/admin/barang?status=Menunggu+Verifikasi',
                'nada'       => 'blue',
            ];
        }

        if ($klaim > 0) {
            $items[] = [
                'judul'      => $klaim . ' klaim menunggu verifikasi',
                'keterangan' => 'Minta kode redeem 5 karakter sebelum menyerahkan barang.',
                'tautan'     => '/verifikasi',
                'nada'       => 'blue',
            ];
        }

        if ($fasilitas['dilaporkan'] > 0) {
            $items[] = [
                'judul'      => $fasilitas['dilaporkan'] . ' laporan kerusakan baru',
                'keterangan' => 'Perbarui status perbaikan langsung dari panel fasilitas.',
                'tautan'     => '/admin/fasilitas?status=Dilaporkan',
                'nada'       => 'red',
            ];
        }

        if ($fasilitas['proses'] > 0) {
            $items[] = [
                'judul'      => $fasilitas['proses'] . ' perbaikan sedang berjalan',
                'keterangan' => 'Tandai selesai setelah teknisi menyelesaikan pekerjaan.',
                'tautan'     => '/admin/fasilitas?status=Proses+Perbaikan',
                'nada'       => 'blue',
            ];
        }

        if ($items === []) {
            $items[] = [
                'judul'      => 'Tidak ada pekerjaan tertunda',
                'keterangan' => 'Semua laporan dan klaim sudah ditangani.',
                'tautan'     => '/admin/barang',
                'nada'       => 'green',
            ];
        }

        return array_slice($items, 0, self::MAKSIMAL);
    }
}
