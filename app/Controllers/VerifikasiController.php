<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AppException;
use App\Core\Auth;
use App\Core\Request;
use App\Models\Klaim;
use App\Services\ClaimService;

/**
 * Verifikasi klaim oleh petugas.
 *
 * Petugas memasukkan kode tiket untuk menemukan klaim, lalu meminta kode
 * redeem 5 karakter dari pengklaim. Persetujuan hanya sah lewat kode itu.
 */
final class VerifikasiController extends Controller
{
    public function index(Request $request): void
    {
        Klaim::expire();

        $kode   = (string) $request->query('kode', '');
        $klaim  = $kode === '' ? null : Klaim::findByTicket($kode);

        if ($kode !== '' && $klaim === null) {
            $this->error('Kode tiket "' . $kode . '" tidak ditemukan.');
        }

        $this->view('verifikasi/index', [
            'title'   => 'Verifikasi klaim · TITIPIN',
            'active'  => 'verifikasi',
            'kode'    => $kode,
            'klaim'   => $klaim,
            'pending' => Klaim::all(['status' => 'Diajukan']),
        ]);
    }

    /**
     * Mencari klaim berdasarkan kode tiket.
     */
    public function lookup(Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator->required('kode_tiket', 'Kode tiket');
        }, '/verifikasi');

        $this->redirect('/verifikasi?kode=' . rawurlencode((string) $data['kode_tiket']));
    }

    /**
     * Menyetujui klaim dengan kode redeem.
     */
    public function redeem(string $id, Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator->required('redeem_code', 'Kode redeem');
        }, '/verifikasi');

        try {
            $klaim = ClaimService::redeem(
                $this->id($id),
                (string) $data['redeem_code'],
                (string) Auth::id()
            );
        } catch (AppException $e) {
            $this->error($e->getMessage());
            $this->back('/verifikasi');
        }

        $this->success(
            'Klaim ' . $klaim['kode_tiket'] . ' disetujui. Barang "' . $klaim['nama_barang'] . '" ditandai selesai.'
        );

        $this->redirect('/verifikasi');
    }
}
