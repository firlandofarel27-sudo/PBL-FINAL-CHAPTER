<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Barang;
use App\Models\Fasilitas;
use App\Models\Klaim;
use App\Services\StatistikService;

/**
 * Beranda: satu alamat untuk pengunjung dan seluruh peran.
 *
 * Pengunjung yang belum masuk langsung melihat katalog publik, sedangkan
 * mahasiswa, admin, dan super admin melihat ringkasan sesuai perannya.
 */
final class DashboardController extends Controller
{
    public function index(): void
    {
        if (!Auth::check()) {
            $this->publik();

            return;
        }

        $role = Auth::role();

        if ($role === 'Mahasiswa') {
            $this->mahasiswa();

            return;
        }

        if ($role === 'Admin') {
            $this->admin();

            return;
        }

        $this->superAdmin();
    }

    /**
     * Halaman depan untuk pengunjung: ringkasan layanan dan barang temuan.
     */
    private function publik(): void
    {
        $barang    = Barang::stats();
        $fasilitas = Fasilitas::stats();

        $this->view('beranda/publik', [
            'title'     => config('app.name') . ' · ' . config('app.tagline'),
            'active'    => 'beranda',
            'temuan'    => Barang::tersedia(6),
            'statistik' => [
                'temuan'    => $barang['tersedia'],
                'selesai'   => $barang['selesai'] + $fasilitas['selesai'],
                'perbaikan' => $fasilitas['dilaporkan'] + $fasilitas['proses'],
            ],
        ], 'layouts/public');
    }

    private function mahasiswa(): void
    {
        $nim = (string) Auth::id();

        Klaim::expire();

        $this->view('dashboard/mahasiswa', [
            'title'    => 'Beranda · TITIPIN',
            'active'   => 'beranda',
            'stat'     => StatistikService::mahasiswa($nim),
            'temuan'   => Barang::tersedia(3),
            'laporan'  => Barang::all(['pelapor' => $nim]),
            'klaim'    => Klaim::forClaimer($nim),
        ]);
    }

    private function admin(): void
    {
        Klaim::expire();

        $this->view('dashboard/admin', [
            'title'      => 'Beranda · TITIPIN',
            'active'     => 'beranda',
            'stat'       => StatistikService::admin(),
            'menunggu'   => Barang::all(['status' => 'Menunggu Verifikasi']),
            'fasilitas'  => Fasilitas::all(['status' => 'Dilaporkan']),
        ]);
    }

    private function superAdmin(): void
    {
        Klaim::expire();

        $this->view('dashboard/super', [
            'title'     => 'Dashboard · TITIPIN',
            'active'    => 'beranda',
            'stat'      => StatistikService::superAdmin(),
            'terbaru'   => Barang::all(),
            'fasilitas' => Fasilitas::all(),
        ]);
    }
}
