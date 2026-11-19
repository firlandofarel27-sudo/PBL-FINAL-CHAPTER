<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AppException;
use App\Core\Auth;
use App\Core\Request;
use App\Models\User;
use App\Services\UploadService;

/**
 * Profil pengguna yang sedang login.
 *
 * Peran tidak dapat diubah dari halaman ini; hanya administrator yang
 * boleh memindahkan peran pengguna lain.
 */
final class ProfilController extends Controller
{
    public function edit(): void
    {
        $this->view('profil/edit', [
            'title'  => 'Profil · TITIPIN',
            'active' => 'profil',
            'user'   => Auth::user(),
        ]);
    }

    public function update(Request $request): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('nama_lengkap', 'Nama lengkap')
                ->max('nama_lengkap', 'Nama lengkap', 255)
                ->required('email', 'Email kampus')
                ->email('email', 'Email kampus')
                ->max('no_telepon', 'Nomor telepon', 20)
                ->max('jurusan', 'Jurusan', 100)
                ->max('kelas', 'Kelas', 50)
                ->min('password', 'Password baru', 8)
                ->confirmed('password', 'Password baru', 'konfirm_password');
        }, '/profil');

        if (User::existsEmail((string) $data['email'], (string) $user['nim'])) {
            $this->error('Email sudah dipakai akun lain.');
            $this->redirect('/profil');
        }

        $foto = null;
        $file = $request->file('foto_profil');

        try {
            if ($file !== null) {
                $foto = UploadService::store($file, 'profil', 'barang');
            }
        } catch (AppException $e) {
            $this->error($e->getMessage());
            $this->redirect('/profil');
        }

        $payload = [
            'nama_lengkap' => (string) $data['nama_lengkap'],
            'email'        => (string) $data['email'],
            'jurusan'      => (string) ($data['jurusan'] ?? ''),
            'kelas'        => (string) ($data['kelas'] ?? ''),
            'no_telepon'   => (string) ($data['no_telepon'] ?? ''),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = (string) $data['password'];
        }

        User::update((string) $user['nim'], $payload);

        if ($foto !== null) {
            UploadService::delete($user['foto_profil']);
            User::update((string) $user['nim'], ['foto_profil' => $foto]);
        }

        $this->success('Profil berhasil diperbarui.');

        $this->redirect('/profil');
    }
}
