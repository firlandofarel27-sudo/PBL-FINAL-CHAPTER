<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Barang;
use App\Models\Fasilitas;
use App\Models\Klaim;
use App\Models\Lokasi;
use App\Models\Model;
use App\Models\Pengaturan;
use App\Models\User;

/**
 * Angka ringkasan untuk beranda dan halaman analitik.
 *
 * Semua hitungan dilakukan di database dengan agregat, bukan dengan
 * mengirim seluruh baris ke PHP lalu menghitungnya.
 */
final class StatistikService
{
    /**
     * Ringkasan beranda mahasiswa.
     *
     * @return array<string, int|float>
     */
    public static function mahasiswa(string $nim): array
    {
        $barang    = Barang::stats($nim);
        $fasilitas = Fasilitas::stats($nim);
        $klaim     = Klaim::stats($nim);

        return [
            'laporan'    => $barang['total'] + $fasilitas['total'],
            'diproses'   => $barang['menunggu'] + $fasilitas['dilaporkan'] + $fasilitas['proses'],
            'selesai'    => $barang['selesai'] + $fasilitas['selesai'],
            'klaim'      => $klaim['total'],
            'klaim_aktif' => $klaim['diajukan'],
        ];
    }

    /**
     * Ringkasan beranda admin.
     *
     * @return array<string, int>
     */
    public static function admin(): array
    {
        $barang    = Barang::stats();
        $fasilitas = Fasilitas::stats();

        return [
            'laporan'          => $barang['total'],
            'menunggu'         => $barang['menunggu'],
            'klaim_pending'    => Klaim::pendingCount(),
            'fasilitas_aktif'  => $fasilitas['dilaporkan'] + $fasilitas['proses'],
            'selesai'          => $barang['selesai'],
            'prioritas'        => $barang['prioritas'],
        ];
    }

    /**
     * Ringkasan beranda super admin.
     *
     * @return array<string, int|float>
     */
    public static function superAdmin(): array
    {
        $barang    = Barang::stats();
        $fasilitas = Fasilitas::stats();
        $selesai   = $barang['selesai'] + $fasilitas['selesai'];
        $total     = $barang['total'] + $fasilitas['total'];

        return [
            'pengguna'  => User::countAll(),
            'laporan'   => $total,
            'selesai'   => $selesai,
            'persen'    => $total === 0 ? 0.0 : round($selesai / $total * 100, 1),
            'lokasi'    => Lokasi::countAll(),
            'klaim'     => Klaim::pendingCount(),
        ];
    }

    /**
     * Data halaman analitik: total, capaian, kategori, dan tren bulanan.
     *
     * @return array<string, mixed>
     */
    public static function analitik(): array
    {
        $barang    = Barang::stats();
        $fasilitas = Fasilitas::stats();
        $total     = $barang['total'] + $fasilitas['total'];
        $selesai   = $barang['selesai'] + $fasilitas['selesai'];
        $persen    = $total === 0 ? 0.0 : round($selesai / $total * 100, 1);

        return [
            'total'       => $total,
            'selesai'     => $selesai,
            'proses'      => $total - $selesai,
            'persen'      => $persen,
            'target'      => (int) Pengaturan::get('target_selesai'),
            'pengguna'    => User::countAll(),
            'pengguna_aktif' => User::countActive(),
            'barang'      => $barang,
            'fasilitas'   => $fasilitas,
            'kategori'    => self::perKategori(),
            'tren'        => self::trenBulanan(),
        ];
    }

    /**
     * Jumlah laporan per kategori, digabung dari barang dan fasilitas.
     *
     * @return array<int, array{nama: string, jumlah: int}>
     */
    private static function perKategori(): array
    {
        $rows = Model::query(
            "SELECT coalesce(k.nama_kategori, 'Tanpa kategori') AS nama, count(*) AS jumlah
             FROM (
                 SELECT kategori_id FROM barang
                 UNION ALL
                 SELECT kategori_id FROM kerusakan_fasilitas
             ) AS laporan
             LEFT JOIN kategori k ON k.id_kategori = laporan.kategori_id
             GROUP BY 1
             ORDER BY 2 DESC"
        );

        return array_map(
            static fn (array $row): array => ['nama' => $row['nama'], 'jumlah' => (int) $row['jumlah']],
            $rows
        );
    }

    /**
     * Jumlah laporan masuk per bulan selama enam bulan terakhir.
     *
     * @return array<int, array{label: string, jumlah: int}>
     */
    private static function trenBulanan(): array
    {
        $rows = Model::query(
            "SELECT to_char(bulan, 'Mon YYYY') AS label, jumlah
             FROM (
                 SELECT date_trunc('month', tanggal) AS bulan, count(*) AS jumlah
                 FROM barang
                 WHERE tanggal IS NOT NULL
                 GROUP BY 1
                 UNION ALL
                 SELECT date_trunc('month', tanggal_lapor) AS bulan, count(*) AS jumlah
                 FROM kerusakan_fasilitas
                 GROUP BY 1
             ) AS per_bulan
             GROUP BY bulan, jumlah
             ORDER BY bulan DESC
             LIMIT 6"
        );

        return array_reverse(array_map(
            static fn (array $row): array => ['label' => $row['label'], 'jumlah' => (int) $row['jumlah']],
            $rows
        ));
    }
}
