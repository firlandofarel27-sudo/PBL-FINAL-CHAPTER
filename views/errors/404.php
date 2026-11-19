<?php

declare(strict_types=1);

/**
 * Halaman 404 sederhana.
 */
?>
<div class="login-page">
    <div class="login-card" style="max-width:480px;margin:auto">
        <p class="eyebrow">404</p>
        <h1 style="margin:0 0 8px">Halaman tidak ditemukan</h1>
        <p class="muted">Alamat yang kamu buka tidak tersedia di aplikasi ini.</p>
        <a class="btn primary full" href="<?= url('/beranda') ?>">Kembali ke beranda</a>
    </div>
</div>
