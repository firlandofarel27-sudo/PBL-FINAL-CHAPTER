<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Services\StatistikService;
use App\Services\UploadService;


final class BarangAdminController extends Controller
{
    public function index(Request $request): void
    {
        $keyword  = (string) $request->query('q', '');
        $status   = (string) $request->query('status', '');
        $jenis    = (string) $request->query('jenis', '');
        $lokasi   = (string) $request->query('lokasi', '');

        $filters = array_filter([
            'q'        => $keyword,
            'status'   => $status,
            'jenis'    => $jenis,
            'lokasi'   => $lokasi,
        ], static fn (string $value): bool => $value !== '');

        $this->view('admin/barang', [
            'title'     => 'Manajemen laporan · TITIPIN',
            'active'    => 'laporan',
            'keyword'   => $keyword,
            'status'    => $status,
            'jenis'     => $jenis,
            'lokasi'    => $lokasi,
            'items'     => Barang::all($filters),
            'stat'      => StatistikService::admin(),
            'kategoris' => Kategori::all(),
            'lokasis'   => Lokasi::all(),
        ]);
    }

    /**
     * Memverifikasi laporan atau menutup laporan barang hilang.
     */
    public function updateStatus(string $id, Request $request): void
    {
        $barang = Barang::find($this->id($id));
        $status = (string) $request->input('status', '');

        if ($barang === null) {
            $this->error('Laporan tidak ditemukan.');
            $this->redirect('/admin/barang');
        }

        if (!in_array($status, ['Menunggu Verifikasi', 'Tersedia', 'Selesai'], true)) {
            $this->error('Status barang tidak valid.');
            $this->redirect('/admin/barang');
        }

        if ($status === 'Selesai' && $barang['jenis'] === 'Found') {
            $this->error('Barang temuan hanya bisa diselesaikan lewat verifikasi kode redeem pada klaim.');
            $this->redirect('/admin/barang');
        }

        Barang::updateStatus((int) $barang['id_barang'], $status);

        $this->success('Status "' . $barang['nama_barang'] . '" diubah menjadi ' . $status . '.');

        $this->redirect('/admin/barang');
    }

    /**
     * Menandai laporan sebagai prioritas atau melepas tanda prioritas.
     */
    public function prioritas(string $id, Request $request): void
    {
        $barang = Barang::find($this->id($id));

        if ($barang === null) {
            $this->error('Laporan tidak ditemukan.');
            $this->redirect('/admin/barang');
        }

        $nilai = $request->input('prioritas') === '1';

        Barang::setPrioritas((int) $barang['id_barang'], $nilai);

        $this->success(
            $nilai
                ? 'Laporan ditandai prioritas.'
                : 'Tanda prioritas dilepas.'
        );

        $this->redirect('/admin/barang');
    }

    public function destroy(string $id): void
    {
        $barang = Barang::find($this->id($id));

        if ($barang === null) {
            $this->error('Laporan tidak ditemukan.');
            $this->redirect('/admin/barang');
        }

        $foto = Barang::delete((int) $barang['id_barang']);

        UploadService::delete($foto);

        $this->success('Laporan "' . $barang['nama_barang'] . '" dihapus.');

        $this->redirect('/admin/barang');
    }

    /**
     * Mengunduh seluruh laporan barang sebagai CSV.
     *
     * Nilai dibungkus tanda kutip dan tanda kutip di dalamnya digandakan
     * agar aman dari penyisipan rumus spreadsheet (CSV injection).
     */
    public function export(Request $request): void
    {
        $status = (string) $request->query('status', '');
        $jenis  = (string) $request->query('jenis', '');

        $filters = array_filter([
            'q'      => (string) $request->query('q', ''),
            'status' => $status,
            'jenis'  => $jenis,
        ], static fn (string $value): bool => $value !== '');

        $berkas = 'laporan-titipin-' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $berkas . '"');

        $keluaran = fopen('php://output', 'wb');

        if ($keluaran === false) {
            $this->error('Gagal membuat berkas ekspor.');
            $this->redirect('/admin/barang');
        }

        fputcsv($keluaran, ['ID', 'Nama', 'Jenis', 'Kategori', 'Lokasi', 'Status', 'Tanggal', 'Pelapor']);

        foreach (Barang::all($filters) as $item) {
            $baris = [
                'TP-' . $item['id_barang'],
                $item['nama_barang'],
                jenis_label((string) $item['jenis']),
                (string) ($item['nama_kategori'] ?? ''),
                trim((string) ($item['nama_lokasi'] ?? '') . ' ' . (string) ($item['lokasi_detail'] ?? '')),
                (string) $item['status'],
                (string) ($item['tanggal'] ?? ''),
                (string) ($item['nama_pelapor'] ?? ''),
            ];

            fputcsv($keluaran, array_map(
                static fn (string $nilai): string => preg_match('/^[=+\-@\t\r]/', $nilai) === 1
                    ? "'" . $nilai
                    : $nilai,
                $baris
            ));
        }

        fclose($keluaran);

        exit;
    }
}
