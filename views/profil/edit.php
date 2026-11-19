<?php

declare(strict_types=1);

/**
 * Profil pengguna yang sedang login.
 *
 * Tata letak dua kolom: formulir data diri di kiri, foto dan ringkasan
 * akun di kanan. Keduanya berada di dalam satu form; kolom kanan memakai
 * atribut `form="profilForm"` agar tetap ikut terkirim.
 *
 * @var array<string, mixed> $user
 */
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Profil saya',
    'sub'   => 'Kelola informasi akun dan preferensi kamu.',
]) ?>

<section class="panel profil-head">
    <div class="profil-identitas">
        <div class="avatar avatar-lg">
            <?php if (!empty($user['foto_profil'])): ?>
                <img src="<?= e((string) upload_url($user['foto_profil'])) ?>" alt="Foto profil">
            <?php else: ?>
                <?= e(initials((string) $user['nama_lengkap'])) ?>
            <?php endif; ?>
        </div>

        <div class="profil-identitas-teks">
            <h2><?= e((string) $user['nama_lengkap']) ?></h2>
            <p class="muted">
                <?= e((string) $user['role']) ?> ·
                <?= e((string) ($user['jurusan'] ?: 'Kampus')) ?>
                <?php if (!empty($user['kelas'])): ?>
                    · <?= e((string) $user['kelas']) ?>
                <?php endif; ?>
            </p>
        </div>

        <span class="status <?= e(status_class((string) $user['status_akun'])) ?>">
            <?= e((string) $user['status_akun']) ?>
        </span>
    </div>
</section>

<div class="profil-grid">
    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Data diri</h2>
            <span class="status blue">Wajib lengkap</span>
        </div>

        <form id="profilForm" method="post" action="<?= url('/profil') ?>"
              enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>

            <div class="field">
                <label for="nama_lengkap">Nama lengkap</label>
                <input id="nama_lengkap" name="nama_lengkap"
                       value="<?= e((string) $user['nama_lengkap']) ?>" required>
                <?php if (error_for('nama_lengkap')): ?>
                    <p class="field-error"><?= e((string) error_for('nama_lengkap')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="email">Email kampus</label>
                <input type="email" id="email" name="email"
                       value="<?= e((string) $user['email']) ?>" required>
                <?php if (error_for('email')): ?>
                    <p class="field-error"><?= e((string) error_for('email')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="peran">Peran</label>
                <input id="peran" value="<?= e((string) $user['role']) ?>" disabled>
                <p class="field-hint">Peran hanya bisa diubah oleh super admin.</p>
            </div>

            <div class="field">
                <label for="no_telepon">Nomor telepon</label>
                <input id="no_telepon" name="no_telepon"
                       value="<?= e((string) ($user['no_telepon'] ?? '')) ?>"
                       placeholder="08xxxxxxxxxx">
            </div>

            <div class="field">
                <label for="jurusan">Jurusan</label>
                <input id="jurusan" name="jurusan"
                       value="<?= e((string) ($user['jurusan'] ?? '')) ?>"
                       placeholder="Teknik Informatika">
            </div>

            <div class="field">
                <label for="kelas">Kelas</label>
                <input id="kelas" name="kelas"
                       value="<?= e((string) ($user['kelas'] ?? '')) ?>"
                       placeholder="TI-2A">
            </div>

            <div class="field">
                <label for="password">Password baru</label>
                <input type="password" id="password" name="password"
                       placeholder="Kosongkan bila tidak diubah">
                <?php if (error_for('password')): ?>
                    <p class="field-error"><?= e((string) error_for('password')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="konfirm_password">Ulangi password baru</label>
                <input type="password" id="konfirm_password" name="konfirm_password">
            </div>

            <div class="field full-width profil-aksi">
                <button class="btn primary" type="submit">Simpan perubahan</button>
                <a class="btn outline" href="<?= url('/beranda') ?>">Batal</a>
            </div>
        </form>
    </section>

    <aside class="profil-side">
        <section class="panel">
            <div class="section-head">
                <h2 style="font-size:15px">Foto profil</h2>
            </div>

            <div class="profil-foto">
                <div class="avatar avatar-xl">
                    <?php if (!empty($user['foto_profil'])): ?>
                        <img src="<?= e((string) upload_url($user['foto_profil'])) ?>" alt="Foto profil">
                    <?php else: ?>
                        <?= e(initials((string) $user['nama_lengkap'])) ?>
                    <?php endif; ?>
                </div>

                <p class="muted" style="font-size:11px;margin:10px 0 0">
                    PNG atau JPG, maksimal 2 MB. Foto tampil pada sidebar dan kartu profil.
                </p>
            </div>

            <label class="btn outline full" for="foto">Pilih foto baru</label>
            <input type="file" id="foto" name="foto_profil" accept="image/*"
                   form="profilForm" class="profil-file">

            <div id="photoPreviewContainer"></div>

            <button class="btn primary full" type="submit" form="profilForm"
                    style="margin-top:12px">
                Simpan perubahan
            </button>
        </section>

        <section class="panel">
            <div class="section-head">
                <h2 style="font-size:15px">Ringkasan akun</h2>
            </div>

            <div class="profil-meta">
                <div>
                    <span>NIM</span>
                    <b><?= e((string) $user['nim']) ?></b>
                </div>
                <div>
                    <span>Username</span>
                    <b><?= e((string) $user['username']) ?></b>
                </div>
                <div>
                    <span>Peran</span>
                    <b><?= e((string) $user['role']) ?></b>
                </div>
                <div>
                    <span>Status</span>
                    <b><?= e((string) $user['status_akun']) ?></b>
                </div>
            </div>

            <form method="post" action="<?= url('/logout') ?>" style="margin-top:14px">
                <?= csrf_field() ?>
                <button class="btn outline full" type="submit">Keluar dari akun</button>
            </form>
        </section>
    </aside>
</div>
