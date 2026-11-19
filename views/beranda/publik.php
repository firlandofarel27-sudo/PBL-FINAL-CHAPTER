<?php

declare(strict_types=1);

/**
 * Halaman depan publik.
 *
 * Pengunjung dapat melihat barang temuan dan laporan kerusakan tanpa akun.
 * Akun baru diperlukan saat mengajukan klaim atau membuat laporan.
 *
 * @var array<int, array<string, mixed>> $temuan
 * @var array<string, int>               $statistik
 */
?>
<section class="public-hero">
    <p class="eyebrow">WELCOME TO <?= e(mb_strtoupper((string) config('app.name'))) ?></p>
    <h1>Temukan.<br>Laporkan.<br>Titipkan.</h1>
    <p class="muted" style="max-width:520px;font-size:13px;line-height:1.7">
        Platform informasi kehilangan dan penemuan barang serta pelaporan kerusakan
        fasilitas di lingkungan kampus. Lihat katalog tanpa perlu akun; masuk hanya
        ketika kamu ingin mengklaim barang.
    </p>

    <div class="public-hero-actions">
        <a class="btn primary" href="<?= url('/katalog') ?>">Jelajahi katalog →</a>
        <a class="btn outline" href="<?= url('/katalog?tab=fasilitas') ?>">Lihat kerusakan fasilitas</a>
    </div>
</section>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">◇</div>
        <div class="stat-label">Barang tersedia</div>
        <div class="stat-number"><?= (int) $statistik['temuan'] ?></div>
        <div class="stat-note">Siap diklaim di posko</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Barang kembali</div>
        <div class="stat-number"><?= (int) $statistik['selesai'] ?></div>
        <div class="stat-note">Sudah diserahterimakan</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">⚡</div>
        <div class="stat-label">Kerusakan ditangani</div>
        <div class="stat-number"><?= (int) $statistik['perbaikan'] ?></div>
        <div class="stat-note">Dilaporkan atau diperbaiki</div>
    </article>
</div>

<div class="section-head" style="margin-top:8px">
    <h2>Barang temuan terbaru</h2>
    <a href="<?= url('/katalog') ?>">Lihat semua →</a>
</div>

<div class="item-grid">
    <?php if ($temuan === []): ?>
        <div class="empty-state" style="grid-column:1/-1">
            <div class="empty-state-icon">⌕</div>
            <h3>Belum ada barang temuan tersedia</h3>
            <p>Barang yang sudah diverifikasi petugas akan muncul di sini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($temuan as $item): ?>
            <?= App\Core\View::capture('partials/item-card', ['item' => $item, 'kind' => 'barang']) ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<section class="panel" style="margin-top:24px">
    <div class="section-head">
        <h2 style="font-size:15px">Bagaimana cara mengklaim barang?</h2>
    </div>

    <div class="action-row">
        <div class="mini-icon">1</div>
        <div class="action-copy">
            <b>Cari barangmu di katalog</b>
            <small>Tidak perlu akun — katalog terbuka untuk semua pengunjung.</small>
        </div>
    </div>

    <div class="action-row">
        <div class="mini-icon">2</div>
        <div class="action-copy">
            <b>Masuk saat menekan tombol klaim</b>
            <small>Login hanya diperlukan untuk mengajukan klaim dan membuat laporan.</small>
        </div>
    </div>

    <div class="action-row">
        <div class="mini-icon">3</div>
        <div class="action-copy">
            <b>Bawa kode redeem ke posko</b>
            <small>Petugas memverifikasi kode 5 karakter sebelum menyerahkan barang.</small>
        </div>
    </div>
</section>
