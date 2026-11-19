<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AppException;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Models\Barang;
use App\Models\Fasilitas;
use App\Models\Kategori;
use App\Models\Lokasi;
use App\Services\UploadService;

/**
 * Formulir laporan terpadu.
 *
 * Satu formulir melayani tiga jenis laporan: barang hilang, barang temuan,
 * dan kerusakan fasilitas. Jenis yang dipilih menentukan tabel tujuan,
 * sehingga pengguna tidak perlu berpindah halaman.
 *
 * Laporan kerusakan fasilitas tidak memakai kode redeem: status
 * perbaikannya diubah langsung oleh admin.
 */
final class LaporanController extends Controller
{
    /**
     * Nilai `jenis` pada formulir beserta labelnya.
     */
    public const JENIS = [
        'Lost'  => 'Barang hilang',
        'Found' => 'Barang temuan',
        'Rusak' => 'Kerusakan fasilitas',
    ];

    public function create(Request $request): void
    {
        $jenis = (string) $request->query('jenis', 'Lost');
        $jenis = array_key_exists($jenis, self::JENIS) ? $jenis : 'Lost';

        $this->view('laporan/create', [
            'title'     => 'Buat laporan · TITIPIN',
            'active'    => 'lapor',
            'jenisOpsi' => self::JENIS,
            'jenisAwal' => $jenis,
            'kategoris' => Kategori::all(),
            'lokasis'   => Lokasi::grouped(),
        ]);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('jenis', 'Jenis laporan')
                ->in('jenis', 'Jenis laporan', array_keys(self::JENIS))
                ->required('nama', 'Nama barang atau fasilitas')
                ->max('nama', 'Nama barang atau fasilitas', 255)
                ->required('deskripsi', 'Deskripsi')
                ->max('deskripsi', 'Deskripsi', 2000)
                ->required('tanggal', 'Tanggal kejadian')
                ->required('lokasi_id', 'Ruangan')
                ->numeric('lokasi_id', 'Ruangan')
                ->max('lokasi_detail', 'Detail lokasi', 255);
        }, '/laporan/baru');

        if (!Lokasi::exists((int) $data['lokasi_id'])) {
            $this->error('Ruangan yang dipilih tidak tersedia. Silakan pilih ulang.');
            Session::flash('_old', $data);

            $this->redirect('/laporan/baru');
        }

        $jenis = (string) $data['jenis'];
        $foto  = null;
        $file  = $request->file('foto');

        try {
            if ($file !== null) {
                $foto = UploadService::store($file, $jenis === 'Rusak' ? 'fasilitas' : 'barang', $jenis === 'Rusak' ? 'fasilitas' : 'barang');
            }

            if ($jenis === 'Rusak') {
                $id = Fasilitas::create([
                    'nama_fasilitas'      => (string) $data['nama'],
                    'kategori_id'         => (string) ($data['kategori_id'] ?? ''),
                    'lokasi_id'           => (string) ($data['lokasi_id'] ?? ''),
                    'lokasi_detail'       => (string) ($data['lokasi_detail'] ?? ''),
                    'waktu_kejadian'      => (string) ($data['waktu_kejadian'] ?? ''),
                    'deskripsi_kerusakan' => (string) $data['deskripsi'],
                    'foto'                => $foto,
                    'tanggal_lapor'       => (string) $data['tanggal'],
                    'pelapor_id'          => (string) Auth::id(),
                ]);

                $this->success('Laporan kerusakan berhasil dikirim. Nomor laporan FS-' . $id . '.');

                $this->redirect('/laporan/fasilitas/' . $id);
            }

            $id = Barang::create([
                'nama_barang'    => (string) $data['nama'],
                'kategori_id'    => (string) ($data['kategori_id'] ?? ''),
                'lokasi_id'      => (string) ($data['lokasi_id'] ?? ''),
                'lokasi_detail'  => (string) ($data['lokasi_detail'] ?? ''),
                'waktu_kejadian' => (string) ($data['waktu_kejadian'] ?? ''),
                'jenis'          => $jenis,
                'deskripsi'      => (string) $data['deskripsi'],
                'tanggal'        => (string) $data['tanggal'],
                'foto'           => $foto,
                'pelapor_id'     => (string) Auth::id(),
            ]);
        } catch (AppException $e) {
            UploadService::delete($foto);
            $this->error($e->getMessage());
            $this->back('/laporan/baru');
        }

        $this->success('Laporan berhasil dikirim. Nomor laporan TP-' . $id . '.');

        $this->redirect('/laporan/barang/' . $id);
    }
}
