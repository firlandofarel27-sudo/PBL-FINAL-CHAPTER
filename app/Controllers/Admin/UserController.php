<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Models\User;

/**
 * Manajemen pengguna oleh super admin.
 *
 * Hanya super admin yang boleh membuat atau mengubah peran, sehingga
 * admin biasa tidak bisa menaikkan hak aksesnya sendiri.
 */
final class UserController extends Controller
{
    private const ROLES = ['Mahasiswa', 'Admin', 'Super Admin'];

    public function index(Request $request): void
    {
        $keyword = (string) $request->query('q', '');
        $role    = (string) $request->query('role', '');

        $this->view('admin/pengguna', [
            'title'   => 'Manajemen pengguna · TITIPIN',
            'active'  => 'pengguna',
            'keyword' => $keyword,
            'role'    => $role,
            'users'   => User::search($keyword, $role),
            'roles'   => self::ROLES,
            'stat'    => [
                'total'      => User::countAll(),
                'mahasiswa'  => User::countByRole('Mahasiswa'),
                'admin'      => User::countByRole('Admin') + User::countByRole('Super Admin'),
                'ditangguhkan' => User::countByStatus('Ditangguhkan'),
            ],
        ]);
    }

    /**
     * Halaman formulir tambah pengguna.
     */
    public function createForm(): void
    {
        $this->view('admin/pengguna-form', [
            'title'  => 'Tambah pengguna · TITIPIN',
            'active' => 'pengguna',
            'mode'   => 'tambah',
            'user'   => null,
            'roles'  => self::ROLES,
        ]);
    }

    /**
     * Halaman formulir kelola pengguna.
     */
    public function editForm(string $nim): void
    {
        $user = User::findByNim($nim);

        if ($user === null) {
            $this->error('Pengguna tidak ditemukan.');
            $this->redirect('/admin/pengguna');
        }

        $this->view('admin/pengguna-form', [
            'title'  => 'Kelola ' . $user['nama_lengkap'] . ' · TITIPIN',
            'active' => 'pengguna',
            'mode'   => 'ubah',
            'user'   => $user,
            'roles'  => self::ROLES,
        ]);
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('nim', 'NIM')
                ->max('nim', 'NIM', 50)
                ->required('username', 'Username')
                ->max('username', 'Username', 100)
                ->required('nama_lengkap', 'Nama lengkap')
                ->max('nama_lengkap', 'Nama lengkap', 255)
                ->required('email', 'Email')
                ->email('email', 'Email')
                ->required('password', 'Password')
                ->min('password', 'Password', 8)
                ->required('role', 'Peran')
                ->in('role', 'Peran', self::ROLES);
        }, '/admin/pengguna/baru');

        if (User::existsNim((string) $data['nim'])) {
            $this->error('NIM sudah terdaftar.');
            $this->redirect('/admin/pengguna/baru');
        }

        if (User::existsEmail((string) $data['email'])) {
            $this->error('Email sudah terdaftar.');
            $this->redirect('/admin/pengguna/baru');
        }

        User::create([
            'nim'          => (string) $data['nim'],
            'username'     => (string) $data['username'],
            'nama_lengkap' => (string) $data['nama_lengkap'],
            'email'        => (string) $data['email'],
            'password'     => (string) $data['password'],
            'role'         => (string) $data['role'],
            'jurusan'      => (string) ($data['jurusan'] ?? ''),
            'kelas'        => (string) ($data['kelas'] ?? ''),
            'no_telepon'   => (string) ($data['no_telepon'] ?? ''),
            'status_akun'  => 'Aktif',
        ]);

        $this->success('Pengguna "' . $data['nama_lengkap'] . '" ditambahkan.');

        $this->redirect('/admin/pengguna');
    }

    public function update(string $nim, Request $request): void
    {
        $user = User::findByNim($nim);

        if ($user === null) {
            $this->error('Pengguna tidak ditemukan.');
            $this->redirect('/admin/pengguna');
        }

        $data = $this->validate($request, static function ($validator): void {
            $validator
                ->required('nama_lengkap', 'Nama lengkap')
                ->max('nama_lengkap', 'Nama lengkap', 255)
                ->required('email', 'Email')
                ->email('email', 'Email')
                ->required('role', 'Peran')
                ->in('role', 'Peran', self::ROLES)
                ->required('status_akun', 'Status akun')
                ->in('status_akun', 'Status akun', ['Aktif', 'Ditangguhkan'])
                ->min('password', 'Password baru', 8);
        }, '/admin/pengguna/' . rawurlencode($nim) . '/ubah');

        if (User::existsEmail((string) $data['email'], $nim)) {
            $this->error('Email sudah dipakai akun lain.');
            $this->redirect('/admin/pengguna/' . rawurlencode($nim) . '/ubah');
        }

        if ($nim === Auth::id() && (string) $data['role'] !== $user['role']) {
            $this->error('Kamu tidak dapat mengubah peran akunmu sendiri.');
            $this->redirect('/admin/pengguna/' . rawurlencode($nim) . '/ubah');
        }

        $payload = [
            'nama_lengkap' => (string) $data['nama_lengkap'],
            'email'        => (string) $data['email'],
            'role'         => (string) $data['role'],
            'status_akun'  => (string) $data['status_akun'],
            'jurusan'      => (string) ($data['jurusan'] ?? ''),
            'kelas'        => (string) ($data['kelas'] ?? ''),
            'no_telepon'   => (string) ($data['no_telepon'] ?? ''),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = (string) $data['password'];
        }

        User::update($nim, $payload);

        $this->success('Data pengguna diperbarui.');

        $this->redirect('/admin/pengguna');
    }

    public function destroy(string $nim): void
    {
        if ($nim === Auth::id()) {
            $this->error('Kamu tidak dapat menghapus akun sendiri saat masih login.');
            $this->redirect('/admin/pengguna');
        }

        $user = User::findByNim($nim);

        if ($user === null) {
            $this->error('Pengguna tidak ditemukan.');
            $this->redirect('/admin/pengguna');
        }

        User::delete($nim);

        $this->success('Pengguna "' . $user['nama_lengkap'] . '" dihapus.');

        $this->redirect('/admin/pengguna');
    }
}
