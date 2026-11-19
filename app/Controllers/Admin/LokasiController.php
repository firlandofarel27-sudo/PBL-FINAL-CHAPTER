<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Models\Lokasi;

/**
 * Master lokasi dan unit kampus.
 *
 * Lokasi dipakai sebagai pilihan pada formulir laporan, sehingga
 * penghapusan ditolak bila masih dipakai laporan mana pun. Baris yang
 * dihapus sebenarnya dinonaktifkan agar riwayat laporan tetap utuh.
 */
final class LokasiController extends Controller
{
    public function index(Request $request): void
    {
        $keyword = (string) $request->query('q', '');
        $gedung  = (string) $request->query('gedung', '');
        $urutan  = (string) $request->query('urutan', 'lantai');

        $items = Lokasi::search($keyword, $gedung);
        $temuan = Lokasi::foundMap();

        /* Urutkan menurut jumlah barang temuan bila diminta, supaya
           ruangan yang paling sering menjadi tempat barang tertinggal
           langsung terlihat di atas. */
        if ($urutan === 'temuan') {
            usort($items, static function (array $a, array $b) use ($temuan): int {
                $selisih = ($temuan[(int) $b['id_lokasi']] ?? 0) <=> ($temuan[(int) $a['id_lokasi']] ?? 0);

                return $selisih !== 0
                    ? $selisih
                    : strcmp((string) $a['gedung'] . $a['nama_lokasi'], (string) $b['gedung'] . $b['nama_lokasi']);
            });
        }

        $this->view('admin/lokasi', [
            'title'     => 'Lokasi & unit · TITIPIN',
            'active'    => 'lokasi',
            'keyword'   => $keyword,
            'gedung'    => $gedung,
            'urutan'    => $urutan,
            'items'     => $items,
            'pemakaian' => Lokasi::usageMap(),
            'temuan'    => $temuan,
            'lantai'    => Lokasi::countPerFloor(),
            'floors'    => Lokasi::floors(),
        ]);
    }

    /**
     * Halaman formulir tambah lokasi.
     */
    public function createForm(): void
    {
        $this->view('admin/lokasi-form', [
            'title'  => 'Tambah lokasi · TITIPIN',
            'active' => 'lokasi',
            'mode'   => 'tambah',
            'lokasi' => null,
            'floors' => Lokasi::floors(),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('nama_lokasi', 'Nama lokasi')
                ->max('nama_lokasi', 'Nama lokasi', 100)
                ->max('gedung', 'Gedung / lantai', 50);
        }, '/admin/lokasi/baru');

        Lokasi::create((string) $data['nama_lokasi'], (string) ($data['gedung'] ?? ''));

        $this->success('Lokasi "' . $data['nama_lokasi'] . '" ditambahkan.');

        $this->redirect('/admin/lokasi');
    }

    /**
     * Halaman formulir edit lokasi.
     */
    public function editForm(string $id): void
    {
        $lokasi = Lokasi::find($this->id($id));

        if ($lokasi === null) {
            $this->error('Lokasi tidak ditemukan.');
            $this->redirect('/admin/lokasi');
        }

        $this->view('admin/lokasi-form', [
            'title'  => 'Edit ' . $lokasi['nama_lokasi'] . ' · TITIPIN',
            'active' => 'lokasi',
            'mode'   => 'ubah',
            'lokasi' => $lokasi,
            'floors' => Lokasi::floors(),
            'dipakai' => Lokasi::usage((int) $lokasi['id_lokasi']),
        ]);
    }

    public function update(string $id, Request $request): void
    {
        $lokasi = Lokasi::find($this->id($id));

        if ($lokasi === null) {
            $this->error('Lokasi tidak ditemukan.');
            $this->redirect('/admin/lokasi');
        }

        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('nama_lokasi', 'Nama lokasi')
                ->max('nama_lokasi', 'Nama lokasi', 100)
                ->max('gedung', 'Gedung / lantai', 50);
        }, '/admin/lokasi/' . $this->id($id) . '/ubah');

        Lokasi::update($this->id($id), (string) $data['nama_lokasi'], (string) ($data['gedung'] ?? ''));

        $this->success('Lokasi "' . $data['nama_lokasi'] . '" diperbarui.');

        $this->redirect('/admin/lokasi');
    }

    public function destroy(string $id): void
    {
        if (!Lokasi::exists($this->id($id))) {
            $this->error('Lokasi tidak ditemukan.');
            $this->redirect('/admin/lokasi');
        }

        if (Lokasi::usage($this->id($id)) > 0) {
            $this->error('Lokasi masih dipakai laporan, jadi tidak bisa dihapus.');
            $this->redirect('/admin/lokasi');
        }

        Lokasi::deactivate($this->id($id));

        $this->success('Lokasi dinonaktifkan dan tidak lagi muncul di formulir laporan.');

        $this->redirect('/admin/lokasi');
    }
}
