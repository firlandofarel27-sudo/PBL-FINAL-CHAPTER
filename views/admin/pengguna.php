<?php

declare(strict_types=1);

/**
 * Daftar pengguna untuk super admin.
 *
 * Halaman ini hanya menampilkan daftar. Penambahan dan penyuntingan
 * dilakukan di halaman formulir tersendiri agar tabel tetap ringkas dan
 * setiap form punya ruang yang cukup.
 *
 * @var string                            $keyword
 * @var string                            $role
 * @var array<int, array<string, mixed>>  $users
 * @var array<int, string>                $roles
 * @var array<string, int>                $stat
 */
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Manajemen Pengguna',
    'sub'   => 'Kelola akses, peran, dan status akun kampus.',
    'aksi'  => '<a class="btn primary" href="' . url('/admin/pengguna/baru') . '">＋ Tambah pengguna</a>',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">♙</div>
        <div class="stat-label">Total akun</div>
        <div class="stat-number"><?= (int) $stat['total'] ?></div>
        <div class="stat-note">Seluruh peran terdaftar</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">▤</div>
        <div class="stat-label">Mahasiswa</div>
        <div class="stat-number"><?= (int) $stat['mahasiswa'] ?></div>
        <div class="stat-note">Pelapor dan pengklaim</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">▣</div>
        <div class="stat-label">Admin</div>
        <div class="stat-number"><?= (int) $stat['admin'] ?></div>
        <div class="stat-note">Admin dan super admin</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✕</div>
        <div class="stat-label">Ditangguhkan</div>
        <div class="stat-number"><?= (int) $stat['ditangguhkan'] ?></div>
        <div class="stat-note">Tidak dapat masuk</div>
    </article>
</div>

<form class="toolbar" method="get" action="<?= url('/admin/pengguna') ?>">
    <div class="searchbox">
        ⌕ <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari nama, email, atau NIM...">
    </div>

    <select class="select" name="role" onchange="this.form.submit()">
        <option value="">Semua peran</option>
        <?php foreach ($roles as $opsi): ?>
            <option value="<?= e($opsi) ?>" <?= $role === $opsi ? 'selected' : '' ?>><?= e($opsi) ?></option>
        <?php endforeach; ?>
    </select>

    <button class="btn outline" type="submit">Cari</button>
    <a class="btn outline" href="<?= url('/admin/pengguna') ?>">Reset</a>
</form>

<section class="panel">
    <div class="section-head">
        <h2>Daftar pengguna</h2>
        <span class="status"><?= count($users) ?> akun</span>
    </div>

    <?php if ($users === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">♙</div>
            <h3>Tidak ada pengguna yang cocok</h3>
            <p>Coba ubah kata kunci atau filter peran.</p>
            <a class="btn primary" href="<?= url('/admin/pengguna') ?>">Tampilkan semua</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table tabel-pengguna">
                <thead>
                <tr>
                    <th>PENGGUNA</th>
                    <th>NIM</th>
                    <th>PERAN</th>
                    <th>STATUS</th>
                    <th>KONTAK</th>
                    <th>AKSI</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $item): ?>
                    <?php $nim = (string) $item['nim']; ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="avatar"><?= e(initials((string) $item['nama_lengkap'])) ?></div>
                                <div class="user-cell-teks">
                                    <b><?= e((string) $item['nama_lengkap']) ?></b>
                                    <small><?= e((string) $item['email']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="mono"><?= e($nim) ?></span></td>
                        <td>
                            <span class="status <?= $item['role'] === 'Super Admin' ? 'red' : ($item['role'] === 'Admin' ? 'blue' : '') ?>">
                                <?= e((string) $item['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status <?= e(status_class((string) $item['status_akun'])) ?>">
                                <?= e((string) $item['status_akun']) ?>
                            </span>
                        </td>
                        <td>
                            <small class="muted">
                                <?= e((string) ($item['no_telepon'] ?: '-')) ?><br>
                                <?= e((string) ($item['jurusan'] ?: 'Tanpa jurusan')) ?>
                                <?= $item['kelas'] ? ' · ' . e((string) $item['kelas']) : '' ?>
                            </small>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="btn small outline" href="<?= url('/admin/pengguna/' . rawurlencode($nim) . '/ubah') ?>">
                                    Kelola
                                </a>

                                <?php if ($nim !== App\Core\Auth::id()): ?>
                                    <form method="post" action="<?= url('/admin/pengguna/' . rawurlencode($nim) . '/hapus') ?>"
                                          class="inline-form"
                                          onsubmit="return confirm('Hapus pengguna ini? Laporan dan klaimnya ikut terhapus.')">
                                        <?= csrf_field() ?>
                                        <button class="btn small outline" type="submit">Hapus</button>
                                    </form>
                                <?php else: ?>
                                    <span class="status green">Akun kamu</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
