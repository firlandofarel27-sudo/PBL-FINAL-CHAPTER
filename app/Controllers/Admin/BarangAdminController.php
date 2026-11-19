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
use App\Services\XlsxService;

final class BarangAdminController extends Controller
{
    /**
     * Judul kolom ekspor, dipakai bersama oleh CSV dan Excel.
     *
     * @var list<string>
     */
    private const KOLOM_EKSPOR = ['ID', 'Nama', 'Jenis', 'Kategori', 'Lokasi', 'Status', 'Tanggal', 'Pelapor'];

    /**
     * Lebar kolom Excel (dalam karakter) untuk setiap kolom di atas.
     *
     * @var list<int>
     */
    private const LEBAR_KOLOM = [12, 32, 18, 34, 36, 22, 14, 26];

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
     * Mengunduh laporan barang sesuai filter aktif.
     *
     * Format ditentukan parameter `format`:
     * - `csv`  (bawaan) → teks CSV, aman dari CSV injection
     * - `xlsx`          → berkas Excel asli (lihat XlsxService)
     */
    public function export(Request $request): void
    {
        $format  = (string) $request->query('format', 'csv');
        $filters = $this->exportFilters($request);
        $baris   = $this->exportRows($filters);
        $tanggal = date('Y-m-d');

        if ($format === 'xlsx') {
            XlsxService::download(
                'laporan-titipin-' . $tanggal . '.xlsx',
                'Laporan',
                self::KOLOM_EKSPOR,
                $baris,
                self::LEBAR_KOLOM
            );
        }

        $this->exportCsv('laporan-titipin-' . $tanggal . '.csv', $baris);
    }

    /**
     * Filter laporan dari query string, dipakai bersama CSV dan Excel.
     *
     * @return array<string, string>
     */
    private function exportFilters(Request $request): array
    {
        return array_filter([
            'q'      => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
            'jenis'  => (string) $request->query('jenis', ''),
            'lokasi' => (string) $request->query('lokasi', ''),
        ], static fn (string $nilai): bool => $nilai !== '');
    }

    /**
     * Menyusun baris ekspor; urutan kolom sama untuk kedua format.
     *
     * @param  array<string, string>  $filters
     * @return array<int, array<int, string>>
     */
    private function exportRows(array $filters): array
    {
        $baris = [];

        foreach (Barang::all($filters) as $item) {
            $baris[] = [
                'TP-' . $item['id_barang'],
                (string) $item['nama_barang'],
                jenis_label((string) $item['jenis']),
                (string) ($item['nama_kategori'] ?? ''),
                trim((string) ($item['nama_lokasi'] ?? '') . ' ' . (string) ($item['lokasi_detail'] ?? '')),
                status_label((string) $item['status']),
                (string) ($item['tanggal'] ?? ''),
                (string) ($item['nama_pelapor'] ?? ''),
            ];
        }

        return $baris;
    }

    /**
     * Mengirim CSV ke browser.
     *
     * Nilai yang diawali karakter pemicu rumus diberi awalan kutip tunggal
     * agar tidak dieksekusi spreadsheet (CSV injection).
     *
     * @param  array<int, array<int, string>>  $baris
     */
    private function exportCsv(string $berkas, array $baris): never
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $berkas . '"');

        $keluaran = fopen('php://output', 'wb');

        if ($keluaran === false) {
            $this->error('Gagal membuat berkas ekspor.');
            $this->redirect('/admin/barang');
        }

        fputcsv($keluaran, self::KOLOM_EKSPOR);

        foreach ($baris as $nilai) {
            fputcsv($keluaran, array_map(
                static fn (string $sel): string => preg_match('/^[=+\-@\t\r]/', $sel) === 1
                    ? "'" . $sel
                    : $sel,
                $nilai
            ));
        }

        fclose($keluaran);

        exit;
    }
}
