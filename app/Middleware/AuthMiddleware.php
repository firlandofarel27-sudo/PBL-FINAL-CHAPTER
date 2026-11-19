<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Membatasi akses untuk pengguna yang sudah login.
 *
 * Halaman yang memerlukan login menyimpan alamat tujuannya lebih dulu,
 * sehingga setelah berhasil masuk pengguna kembali ke halaman yang tadi
 * ingin dibuka (misalnya formulir klaim barang).
 */
final class AuthMiddleware
{
    public static function handle(Request $request): void
    {
        if (Auth::check()) {
            return;
        }

        Session::set('_intended', $request->uri());
        Session::flash('error', 'Silakan masuk terlebih dahulu untuk melanjutkan.');

        Response::redirect('/login');
    }

    /**
     * Kebalikannya: halaman masuk dan daftar tidak perlu dibuka saat sudah login.
     */
    public static function guest(): void
    {
        if (Auth::check()) {
            Response::redirect('/beranda');
        }
    }
}
