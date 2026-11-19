<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Models\Klaim;
use App\Services\UploadService;

/**
 * Daftar seluruh klaim, penolakan, dan penghapusan tiket klaim.
 *
 * Persetujuan tidak ada di sini: klaim hanya disetujui lewat kode redeem
 * pada halaman Verifikasi.
 */
final class KlaimAdminController extends Controller
{
    public function index(Request $request): void
    {
        Klaim::expire();

        $keyword = (string) $request->query('q', '');
        $status  = (string) $request->query('status', '');

        $filters = array_filter([
            'q'      => $keyword,
            'status' => $status,
        ], static fn (string $value): bool => $value !== '');

        $this->view('admin/klaim', [
            'title'   => 'Manajemen klaim · TITIPIN',
            'active'  => 'verifikasi',
            'keyword' => $keyword,
            'status'  => $status,
            'items'   => Klaim::all($filters),
            'stat'    => Klaim::stats(),
        ]);
    }

    public function reject(string $id, Request $request): void
    {
        $klaim = Klaim::find($this->id($id));

        if ($klaim === null) {
            $this->error('Klaim tidak ditemukan.');
            $this->redirect('/admin/klaim');
        }

        if ($klaim['status_klaim'] === 'Disetujui') {
            $this->error('Klaim yang sudah disetujui tidak dapat ditolak.');
            $this->redirect('/admin/klaim');
        }

        $alasan = (string) $request->input('alasan_penolakan', '');
        $alasan = $alasan === '' ? 'Ditolak oleh admin.' : $alasan;

        Klaim::reject((int) $klaim['id_klaim'], $alasan);

        $this->success('Klaim ' . $klaim['kode_tiket'] . ' ditolak.');

        $this->redirect('/admin/klaim');
    }

    public function destroy(string $id): void
    {
        $klaim = Klaim::find($this->id($id));

        if ($klaim === null) {
            $this->error('Klaim tidak ditemukan.');
            $this->redirect('/admin/klaim');
        }

        foreach (Klaim::delete((int) $klaim['id_klaim']) as $berkas) {
            UploadService::delete($berkas);
        }

        $this->success('Tiket klaim ' . $klaim['kode_tiket'] . ' dihapus.');

        $this->redirect('/admin/klaim');
    }
}
