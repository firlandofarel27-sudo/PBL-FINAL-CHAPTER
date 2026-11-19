<?php

declare(strict_types=1);

/**
 * Formulir tambah dan kelola pengguna.
 *
 * Satu view melayani dua mode:
 * - `tambah` → POST /admin/pengguna
 * - `ubah`   → POST /admin/pengguna/{nim}
 *
 * @var string                            $mode
 * @var array<string, mixed>|null         $user
 * @var array<int, string>                $roles
 */

$ubah = $mode === 'ubah';
$aksi = $ubah
    ? url('/admin/pengguna/' . rawurlencode((string) $user['nim']))
    : url('/admin/pengguna');

/* Nilai awal: data lama setelah validasi gagal, atau data dari database. */
$nilai = static fn (string $kunci, string $bawaan = ''): string => $ubah
    ? old($kunci, (string) ($user[$kunci] ?? $bawaan))
    : old($kunci, $bawaan);
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => $ubah ? 'Kelola pengguna' : 'Tambah pengguna',
    'sub'   => $ubah
        ? (string) $user['nama_lengkap'] . ' · ' . (string) $user['nim']
        : 'Buat akun baru untuk mahasiswa, admin, atau super admin.',
    'aksi'  => '<a class="btn outline" href="' . url('/admin/pengguna') . '">← Daftar pengguna</a>',
]) ?>

<div class="profil-grid">
    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px"><?= $ubah ? 'Data akun' : 'Data akun baru' ?></h2>
            <span class="status <?= $ubah ? 'blue' : 'green' ?>">
                <?= $ubah ? (string) $user['role'] : 'Akun baru' ?>
            </span>
        </div>

        <form method="post" action="<?= $aksi ?>" class="form-grid">
            <?= csrf_field() ?>

            <?php if (!$ubah): ?>
                <div class="field">
                    <label for="nim">NIM *</label>
                    <input id="nim" name="nim" value="<?= e($nilai('nim')) ?>"
                           placeholder="254107020040" required>
                    <?php if (error_for('nim')): ?>
                        <p class="field-error"><?= e((string) error_for('nim')) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="username">Username *</label>
                    <input id="username" name="username" value="<?= e($nilai('username')) ?>"
                           placeholder="nama_pengguna" required>
                    <?php if (error_for('username')): ?>
                        <p class="field-error"><?= e((string) error_for('username')) ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="field">
                    <label for="nim">NIM</label>
                    <input id="nim" value="<?= e((string) $user['nim']) ?>" disabled>
                    <p class="field-hint">NIM adalah kunci akun dan tidak dapat diubah.</p>
                </div>

                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" value="<?= e((string) $user['username']) ?>" disabled>
                </div>
            <?php endif; ?>

            <div class="field">
                <label for="nama_lengkap">Nama lengkap *</label>
                <input id="nama_lengkap" name="nama_lengkap" value="<?= e($nilai('nama_lengkap')) ?>" required>
                <?php if (error_for('nama_lengkap')): ?>
                    <p class="field-error"><?= e((string) error_for('nama_lengkap')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?= e($nilai('email')) ?>" required>
                <?php if (error_for('email')): ?>
                    <p class="field-error"><?= e((string) error_for('email')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="password">
                    <?= $ubah ? 'Password baru' : 'Password *' ?>
                </label>
                <input type="password" id="password" name="password"
                       placeholder="<?= $ubah ? 'Kosongkan bila tidak diubah' : 'Minimal 8 karakter' ?>"
                       <?= $ubah ? '' : 'required' ?>>
                <?php if (error_for('password')): ?>
                    <p class="field-error"><?= e((string) error_for('password')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="role">Peran *</label>
                <select id="role" name="role" required>
                    <?php foreach ($roles as $opsi): ?>
                        <option value="<?= e($opsi) ?>" <?= $nilai('role', 'Mahasiswa') === $opsi ? 'selected' : '' ?>>
                            <?= e($opsi) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (error_for('role')): ?>
                    <p class="field-error"><?= e((string) error_for('role')) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($ubah): ?>
                <div class="field">
                    <label for="status_akun">Status akun *</label>
                    <select id="status_akun" name="status_akun" required>
                        <?php foreach (['Aktif', 'Ditangguhkan'] as $opsi): ?>
                            <option value="<?= e($opsi) ?>" <?= $nilai('status_akun', 'Aktif') === $opsi ? 'selected' : '' ?>>
                                <?= e($opsi) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="field-hint">Akun ditangguhkan tidak dapat masuk.</p>
                </div>
            <?php else: ?>
                <div class="field">
                    <label>Status akun</label>
                    <input value="Aktif" disabled>
                    <p class="field-hint">Akun baru langsung aktif.</p>
                </div>
            <?php endif; ?>

            <div class="field">
                <label for="jurusan">Jurusan</label>
                <input id="jurusan" name="jurusan" value="<?= e($nilai('jurusan')) ?>"
                       placeholder="Teknik Informatika">
            </div>

            <div class="field">
                <label for="kelas">Kelas</label>
                <input id="kelas" name="kelas" value="<?= e($nilai('kelas')) ?>"
                       placeholder="TI-2A">
            </div>

            <div class="field">
                <label for="no_telepon">Nomor telepon</label>
                <input id="no_telepon" name="no_telepon" value="<?= e($nilai('no_telepon')) ?>"
                       placeholder="08xxxxxxxxxx">
            </div>

            <div class="field full-width profil-aksi">
                <button class="btn primary" type="submit">
                    <?= $ubah ? 'Simpan perubahan' : '＋ Tambah pengguna' ?>
                </button>
                <a class="btn outline" href="<?= url('/admin/pengguna') ?>">Batal</a>
            </div>
        </form>
    </section>

    <aside class="profil-side">
        <section class="panel">
            <div class="section-head">
                <h2 style="font-size:15px">Ringkasan</h2>
            </div>

            <?php if ($ubah): ?>
                <div class="profil-foto">
                    <div class="avatar avatar-xl"><?= e(initials((string) $user['nama_lengkap'])) ?></div>
                    <p class="muted" style="font-size:11px;margin:10px 0 0">
                        <?= e((string) $user['email']) ?>
                    </p>
                </div>

                <div class="profil-meta">
                    <div><span>NIM</span><b><?= e((string) $user['nim']) ?></b></div>
                    <div><span>Username</span><b><?= e((string) $user['username']) ?></b></div>
                    <div><span>Peran</span><b><?= e((string) $user['role']) ?></b></div>
                    <div><span>Status</span><b><?= e((string) $user['status_akun']) ?></b></div>
                </div>
            <?php else: ?>
                <p class="muted" style="font-size:11px;line-height:1.7;margin:0">
                    NIM dan username harus unik. Password minimal 8 karakter.
                    Peran <b>Super Admin</b> memberi akses penuh termasuk manajemen pengguna,
                    lokasi, dan pengaturan sistem.
                </p>
            <?php endif; ?>
        </section>

        <?php if ($ubah): ?>
            <section class="panel">
                <div class="section-head">
                    <h2 style="font-size:15px">Tindakan</h2>
                </div>

                <?php if ((string) $user['nim'] === App\Core\Auth::id()): ?>
                    <p class="muted" style="font-size:11px;margin:0 0 10px">
                        Ini akun kamu sendiri. Peran dan penghapusan dikunci agar kamu tidak
                        kehilangan akses.
                    </p>
                <?php else: ?>
                    <form method="post" action="<?= url('/admin/pengguna/' . rawurlencode((string) $user['nim']) . '/hapus') ?>"
                          onsubmit="return confirm('Hapus pengguna ini? Laporan dan klaimnya ikut terhapus.')">
                        <?= csrf_field() ?>
                        <button class="btn outline full" type="submit">Hapus pengguna</button>
                    </form>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </aside>
</div>
