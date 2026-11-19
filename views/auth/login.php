<?php

declare(strict_types=1);

/**
 * Formulir masuk.
 */
?>
<p class="eyebrow">ACCOUNT LOGIN</p>
<h2>Selamat Datang!</h2>
<p class="muted">Silakan masuk menggunakan akun kampus untuk melanjutkan ke TITIPIN.</p>

<form method="post" action="<?= url('/login') ?>">
    <?= csrf_field() ?>

    <label for="email">Email Kampus</label>
    <input type="email" id="email" name="email"
           value="<?= e(old('email')) ?>"
           placeholder="nama@kampus.ac.id" required autofocus>
    <?php if (error_for('email')): ?>
        <p class="field-error"><?= e((string) error_for('email')) ?></p>
    <?php endif; ?>

    <label for="password">Password</label>
    <div class="password">
        <input type="password" id="password" name="password"
               placeholder="Masukkan password" required>
        <button type="button" class="show-pass" id="togglePass">Lihat</button>
    </div>
    <?php if (error_for('password')): ?>
        <p class="field-error"><?= e((string) error_for('password')) ?></p>
    <?php endif; ?>

    <div class="form-row">
        <label class="check">
            <input type="checkbox" name="ingat" value="1">
            Ingat saya
        </label>
    </div>

    <button type="submit" class="btn primary full">Masuk ke TITIPIN →</button>
</form>

<div class="or">ATAU</div>

<p class="tiny center muted">
    Belum punya akun?
    <a href="<?= url('/daftar') ?>"><b>Daftar sebagai mahasiswa</b></a>
</p>

<p class="tiny center muted" style="margin-top:16px">
    Sistem Informasi Kehilangan dan Pelaporan Fasilitas Kampus.
</p>
