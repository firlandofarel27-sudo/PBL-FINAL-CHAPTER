<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AppException;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Models\Barang;
use App\Models\Klaim;
use App\Services\ClaimService;

/**
 * Tiket klaim milik mahasiswa.
 *
 * Kode redeem hanya ditampilkan sekali, tepat setelah klaim diajukan,
 * karena database hanya menyimpan hash-nya.
 */
final class KlaimController extends Controller
{
    public function index(): void
    {
        Klaim::expire();

        $this->view('klaim/index', [
            'title'  => 'Tiket klaim · TITIPIN',
            'active' => 'klaim',
            'klaim'  => Klaim::forClaimer((string) Auth::id()),
            'kodeBaru' => Session::pull('redeem_baru'),
        ]);
    }

    public function create(string $id): void
    {
        $barang = Barang::find($this->id($id));
        $nim    = (string) Auth::id();

        if ($barang === null) {
            $this->error('Barang tidak ditemukan.');
            $this->redirect('/katalog');
        }

        if ($barang['jenis'] !== 'Found' || $barang['status'] !== 'Tersedia') {
            $this->error('Barang ini tidak sedang tersedia untuk diklaim.');
            $this->redirect('/katalog');
        }

        if ($barang['pelapor_id'] === $nim) {
            $this->error('Kamu tidak dapat mengklaim barang yang kamu laporkan sendiri.');
            $this->redirect('/katalog');
        }

        if (Klaim::hasActiveClaim((int) $barang['id_barang'], $nim)) {
            $this->error('Kamu sudah punya klaim aktif untuk barang ini.');
            $this->redirect('/klaim');
        }

        $this->view('klaim/create', [
            'title'  => 'Ajukan klaim · TITIPIN',
            'active' => 'klaim',
            'barang' => $barang,
        ]);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('item_id', 'Barang')
                ->numeric('item_id', 'Barang')
                ->required('ciri_khusus', 'Ciri khusus barang')
                ->max('ciri_khusus', 'Ciri khusus barang', 1000);
        }, '/katalog');

        $file = $request->file('bukti_kepemilikan');

        if ($file === null) {
            $this->error('Foto bukti kepemilikan wajib diunggah.');
            $this->back('/katalog');
        }

        try {
            $hasil = ClaimService::create(
                (int) $data['item_id'],
                (string) Auth::id(),
                $file,
                (string) $data['ciri_khusus']
            );
        } catch (AppException $e) {
            $this->error($e->getMessage());
            $this->back('/katalog');
        }

        Session::flash('redeem_baru', [
            'id_klaim'  => (int) $hasil['klaim']['id_klaim'],
            'kode'      => $hasil['redeem_code'],
            'kode_tiket' => $hasil['klaim']['kode_tiket'],
        ]);

        $this->success('Klaim diajukan. Simpan kode redeem yang tampil sekali ini.');

        $this->redirect('/klaim/' . (int) $hasil['klaim']['id_klaim']);
    }

    public function show(string $id): void
    {
        $klaim = Klaim::find($this->id($id));

        if ($klaim === null) {
            $this->error('Tiket klaim tidak ditemukan.');
            $this->redirect('/klaim');
        }

        if (!Auth::is('Admin', 'Super Admin') && $klaim['claimer_id'] !== Auth::id()) {
            $this->error('Kamu tidak berhak melihat tiket klaim ini.');
            $this->redirect('/klaim');
        }

        $this->view('klaim/show', [
            'title'    => 'Tiket ' . $klaim['kode_tiket'] . ' · TITIPIN',
            'active'   => 'klaim',
            'klaim'    => $klaim,
            'kodeBaru' => Session::pull('redeem_baru'),
        ]);
    }
}
