<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Models\Barang;
use App\Models\Fasilitas;
use App\Models\Klaim;

/**
 * Riwayat dan detail laporan barang milik mahasiswa.
 *
 * Detail barang bisa dibuka publik; nama pelapor hanya ditampilkan
 * kepada pengguna yang sudah masuk.
 */
final class BarangController extends Controller
{
    /**
     * Riwayat laporan: barang dan fasilitas milik pengguna yang login.
     */
    public function index(Request $request): void
    {
        $nim     = (string) Auth::id();
        $keyword = (string) $request->query('q', '');
        $status  = (string) $request->query('status', '');

        $filters = array_filter([
            'q'       => $keyword,
            'status'  => $status,
            'pelapor' => $nim,
        ], static fn (string $value): bool => $value !== '');

        $this->view('barang/index', [
            'title'     => 'Riwayat laporan · TITIPIN',
            'active'    => 'riwayat',
            'keyword'   => $keyword,
            'status'    => $status,
            'barang'    => Barang::all($filters),
            'fasilitas' => Fasilitas::all($filters),
        ]);
    }

    public function show(string $id): void
    {
        $barang = Barang::find($this->id($id));

        if ($barang === null) {
            $this->error('Laporan tidak ditemukan.');
            $this->redirect('/katalog');
        }

        Klaim::expire();

        $nim = Auth::id();

        $this->view('barang/show', [
            'title'     => $barang['nama_barang'] . ' · TITIPIN',
            'active'    => 'katalog',
            'barang'    => $barang,
            'klaim'     => $barang['jenis'] === 'Found' ? Klaim::pendingForItem((int) $barang['id_barang']) : [],
            'bisaKlaim' => $this->bisaKlaim($barang, $nim),
        ], $this->layout());
    }

    /**
     * Barang temuan yang masih tersedia dan bukan laporan pengguna sendiri.
     *
     * Pengunjung yang belum masuk juga melihat tombol klaim; tombol itu
     * mengarah ke formulir klaim yang akan meminta login lebih dulu.
     */
    private function bisaKlaim(array $barang, ?string $nim): bool
    {
        if ($barang['jenis'] !== 'Found' || $barang['status'] !== 'Tersedia') {
            return false;
        }

        if ($nim === null) {
            return true;
        }

        return $barang['pelapor_id'] !== $nim
            && !Klaim::hasActiveClaim((int) $barang['id_barang'], $nim);
    }
}
