<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Membatasi akses berdasarkan peran pengguna.
 *
 * Pengunjung yang belum masuk diarahkan ke halaman masuk beserta alamat
 * tujuan, sedangkan pengguna dengan peran lain dikembalikan ke beranda.
 */
final class RoleMiddleware
{
    public static function handle(string $roles, Request $request): void
    {
        $allowed = array_map('trim', explode(',', $roles));

        if (!Auth::check()) {
            Session::set('_intended', $request->uri());
            Session::flash('error', 'Silakan masuk sebagai ' . implode(' atau ', $allowed) . '.');

            Response::redirect('/login');
        }

        if (Auth::is(...$allowed)) {
            return;
        }

        Session::flash('error', 'Halaman ini hanya tersedia untuk ' . implode(' atau ', $allowed) . '.');

        Response::redirect('/beranda');
    }
}
