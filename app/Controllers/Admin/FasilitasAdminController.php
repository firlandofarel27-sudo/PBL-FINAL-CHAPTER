<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Models\Fasilitas;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Services\UploadService;

/**
 * Manajemen laporan kerusakan fasilitas.
 *
 * Admin memperbarui status perbaikan dan menulis catatan teknisi.
 */
final class FasilitasAdminController extends Controller
{
    public function index(Request $request): void
    {
        $keyword = (string) $request->query('q', '');
        $status  = (string) $request->query('status', '');
        $lokasi  = (string) $request->query('lokasi', '');

        $filters = array_filter([
            'q'      => $keyword,
            'status' => $status,
            'lokasi' => $lokasi,
        ], static fn (string $value): bool => $value !== '');

        $this->view('admin/fasilitas', [
            'title'     => 'Kerusakan fasilitas · TITIPIN',
            'active'    => 'laporan',
            'keyword'   => $keyword,
            'status'    => $status,
            'lokasi'    => $lokasi,
            'items'     => Fasilitas::all($filters),
            'stat'      => Fasilitas::stats(),
            'kategoris' => Kategori::all(),
            'lokasis'   => Lokasi::all(),
        ]);
    }

    public function updateStatus(string $id, Request $request): void
    {
        $fasilitas = Fasilitas::find($this->id($id));
        $status    = (string) $request->input('status', '');
        $catatan   = (string) $request->input('catatan_teknisi', '');

        if ($fasilitas === null) {
            $this->error('Laporan fasilitas tidak ditemukan.');
            $this->redirect('/admin/fasilitas');
        }

        if (!in_array($status, ['Dilaporkan', 'Proses Perbaikan', 'Selesai'], true)) {
            $this->error('Status perbaikan tidak valid.');
            $this->redirect('/admin/fasilitas');
        }

        Fasilitas::updateStatus((int) $fasilitas['id_kerusakan'], $status, $catatan);

        $this->success('Status "' . $fasilitas['nama_fasilitas'] . '" diubah menjadi ' . $status . '.');

        $this->redirect('/admin/fasilitas');
    }

    public function destroy(string $id): void
    {
        $fasilitas = Fasilitas::find($this->id($id));

        if ($fasilitas === null) {
            $this->error('Laporan fasilitas tidak ditemukan.');
            $this->redirect('/admin/fasilitas');
        }

        $foto = Fasilitas::delete((int) $fasilitas['id_kerusakan']);

        UploadService::delete($foto);

        $this->success('Laporan "' . $fasilitas['nama_fasilitas'] . '" dihapus.');

        $this->redirect('/admin/fasilitas');
    }
}
