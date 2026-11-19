<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Fasilitas;

/**
 * Detail laporan kerusakan fasilitas.
 *
 * Pembuatan laporan ditangani LaporanController karena formulirnya
 * menyatu dengan laporan barang.
 */
final class FasilitasController extends Controller
{
    public function show(string $id): void
    {
        $fasilitas = Fasilitas::find($this->id($id));

        if ($fasilitas === null) {
            $this->error('Laporan kerusakan tidak ditemukan.');
            $this->redirect('/katalog?tab=fasilitas');
        }

        $this->view('fasilitas/show', [
            'title'     => $fasilitas['nama_fasilitas'] . ' · TITIPIN',
            'active'    => 'katalog',
            'fasilitas' => $fasilitas,
        ], $this->layout());
    }
}
