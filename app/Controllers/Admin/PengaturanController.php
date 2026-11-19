<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Models\Pengaturan;

/**
 * Pengaturan sistem: identitas aplikasi, retensi, target, dan notifikasi.
 */
final class PengaturanController extends Controller
{
    public function index(): void
    {
        $this->view('admin/pengaturan', [
            'title'      => 'Pengaturan sistem · TITIPIN',
            'active'     => 'pengaturan',
            'pengaturan' => Pengaturan::all(),
        ]);
    }

    public function update(Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('nama_aplikasi', 'Nama aplikasi')
                ->max('nama_aplikasi', 'Nama aplikasi', 100)
                ->required('retensi_hari', 'Durasi penyimpanan')
                ->numeric('retensi_hari', 'Durasi penyimpanan')
                ->required('target_selesai', 'Target penyelesaian')
                ->numeric('target_selesai', 'Target penyelesaian')
                ->in('notifikasi_email', 'Notifikasi email', ['Aktif', 'Nonaktif']);
        }, '/admin/pengaturan');

        Pengaturan::put([
            'nama_aplikasi'    => (string) $data['nama_aplikasi'],
            'retensi_hari'     => (string) max(1, (int) $data['retensi_hari']),
            'target_selesai'   => (string) min(100, max(1, (int) $data['target_selesai'])),
            'notifikasi_email' => (string) $data['notifikasi_email'],
        ]);

        $this->success('Pengaturan sistem disimpan.');

        $this->redirect('/admin/pengaturan');
    }
}
