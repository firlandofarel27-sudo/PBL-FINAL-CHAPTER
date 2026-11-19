<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;

/**
 * Masuk, daftar akun, dan keluar.
 *
 * Kredensial diperiksa dengan password_verify terhadap hash bcrypt yang
 * tersimpan di tabel users.
 */
final class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->view('auth/login', [
            'title' => 'Masuk · TITIPIN',
        ], 'layouts/auth');
    }

    public function login(Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('email', 'Email kampus')
                ->email('email', 'Email kampus')
                ->required('password', 'Password');
        }, '/login');

        $user = User::findByEmail((string) $data['email']);

        if ($user === null || !password_verify((string) $data['password'], (string) $user['password'])) {
            $this->error('Email atau password salah.');

            Session::flash('_old', ['email' => $data['email']]);

            $this->redirect('/login');
        }

        if ($user['status_akun'] === 'Ditangguhkan') {
            $this->error('Akun kamu sedang ditangguhkan. Hubungi administrator.');

            $this->redirect('/login');
        }

        unset($user['password']);

        Session::remember($request->input('ingat') === '1');
        Auth::login($user);

        $this->success('Selamat datang, ' . $user['nama_lengkap'] . '.');

        /* Kembali ke halaman yang tadi ingin dibuka, misalnya formulir klaim. */
        $intended = Session::get('_intended');
        Session::forget('_intended');

        $tujuan = is_string($intended) && str_starts_with($intended, '/')
            ? $intended
            : '/beranda';

        $this->redirect($tujuan);
    }

    public function registerForm(): void
    {
        $this->view('auth/register', [
            'title' => 'Daftar · TITIPIN',
        ], 'layouts/auth');
    }

    public function register(Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('nim', 'NIM')
                ->max('nim', 'NIM', 50)
                ->required('username', 'Username')
                ->max('username', 'Username', 100)
                ->required('nama_lengkap', 'Nama lengkap')
                ->max('nama_lengkap', 'Nama lengkap', 255)
                ->required('email', 'Email kampus')
                ->email('email', 'Email kampus')
                ->required('password', 'Password')
                ->min('password', 'Password', 8)
                ->confirmed('password', 'Password', 'konfirm_password')
                ->max('jurusan', 'Jurusan', 100)
                ->max('kelas', 'Kelas', 50)
                ->max('no_telepon', 'Nomor telepon', 20);
        }, '/daftar');

        if (User::existsNim((string) $data['nim'])) {
            $this->error('NIM sudah terdaftar.');
            Session::flash('_old', $data);

            $this->redirect('/daftar');
        }

        if (User::existsEmail((string) $data['email'])) {
            $this->error('Email sudah terdaftar.');
            Session::flash('_old', $data);

            $this->redirect('/daftar');
        }

        User::create([
            'nim'          => (string) $data['nim'],
            'username'     => (string) $data['username'],
            'nama_lengkap' => (string) $data['nama_lengkap'],
            'email'        => (string) $data['email'],
            'password'     => (string) $data['password'],
            'role'         => 'Mahasiswa',
            'jurusan'      => (string) ($data['jurusan'] ?? ''),
            'kelas'        => (string) ($data['kelas'] ?? ''),
            'no_telepon'   => (string) ($data['no_telepon'] ?? ''),
            'status_akun'  => 'Aktif',
        ]);

        $user = User::findByNim((string) $data['nim']);

        if ($user !== null) {
            Auth::login($user);
        }

        $this->success('Akun berhasil dibuat. Selamat datang di TITIPIN.');

        $this->redirect('/beranda');
    }

    public function logout(): void
    {
        Session::remember(false);
        Auth::logout();

        Session::flash('success', 'Kamu sudah keluar. Sampai jumpa lagi.');

        $this->redirect('/login');
    }
}
