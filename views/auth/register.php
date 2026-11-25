<?php

declare(strict_types=1);

/**
 * Formulir pendaftaran akun mahasiswa.
 */
?>
<p class="eyebrow">ACCOUNT REGISTER</p>
<h2>Buat Akun</h2>
<p class="muted">Gunakan data kampus yang valid agar laporan mudah diverifikasi.</p>

<form method="post" action="<?= url('/daftar') ?>">
    <?= csrf_field() ?>

    <div class="form-grid">
        <div class="field">
            <label for="nim">NIM *</label>
            <input id="nim" name="nim" value="<?= e(old('nim')) ?>" placeholder="254107020040" required>
            <?php if (error_for('nim')): ?>
                <p class="field-error"><?= e((string) error_for('nim')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="username">Username *</label>
            <input id="username" name="username" value="<?= e(old('username')) ?>" placeholder="nama_pengguna" required>
            <?php if (error_for('username')): ?>
                <p class="field-error"><?= e((string) error_for('username')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field full-width">
            <label for="nama_lengkap">Nama lengkap *</label>
            <input id="nama_lengkap" name="nama_lengkap" value="<?= e(old('nama_lengkap')) ?>" required>
            <?php if (error_for('nama_lengkap')): ?>
                <p class="field-error"><?= e((string) error_for('nama_lengkap')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field full-width">
            <label for="email">Email kampus *</label>
            <input type="email" id="email" name="email" value="<?= e(old('email')) ?>"
                   placeholder="nama@kampus.ac.id" required>
            <?php if (error_for('email')): ?>
                <p class="field-error"><?= e((string) error_for('email')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
            <?php if (error_for('password')): ?>
                <p class="field-error"><?= e((string) error_for('password')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="konfirm_password">Ulangi password *</label>
            <input type="password" id="konfirm_password" name="konfirm_password" required>
        </div>

        <div class="field">
            <label for="jurusan">Jurusan</label>
            <select id="jurusan" name="jurusan">
                <option value="">Pilih jurusan</option>
                <?php foreach (['Teknik Informatika', 'Sistem Informasi Bisnis'] as $opsi): ?>
                    <option value="<?= e($opsi) ?>" <?= old('jurusan') === $opsi ? 'selected' : '' ?>>
                        <?= e($opsi) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="kelas">Kelas</label>
            <input id="kelas" name="kelas" value="<?= e(old('kelas')) ?>" placeholder="TI-2A">
        </div>

        <div class="field full-width">
            <label for="no_telepon">Nomor telepon</label>
            <input id="no_telepon" name="no_telepon" value="<?= e(old('no_telepon')) ?>" placeholder="08xxxxxxxxxx">
        </div>
    </div>

    <button type="submit" class="btn primary full" style="margin-top:16px">Daftar sekarang →</button>
</form>

<p class="tiny center muted" style="margin-top:16px">
    Sudah punya akun? <a href="<?= url('/login') ?>"><b>Masuk di sini</b></a>
</p>
