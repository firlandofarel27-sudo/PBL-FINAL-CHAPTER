<?php

declare(strict_types=1);

use App\Core\Session;

/**
 * Layout publik: dipakai halaman yang boleh dibuka tanpa login.
 *
 * Pengunjung bisa menjelajah katalog dan detail barang; tombol masuk baru
 * diminta ketika mereka hendak mengajukan klaim atau membuat laporan.
 *
 * @var string  $title
 * @var string  $content
 * @var string  $active
 */

$sukses = Session::pull('success');
$gagal  = Session::pull('error');
$aktif  = $active ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>

<body class="public-page">

<header class="public-topbar">
    <a class="brand" href="<?= url('/') ?>">
        <span class="brand-mark">⌕</span>
        <div>
            <b><?= e(config('app.name')) ?></b>
            <small><?= e(config('app.tagline')) ?></small>
        </div>
    </a>

    <nav class="public-nav" aria-label="Navigasi publik">
        <a href="<?= url('/katalog') ?>" class="<?= $aktif === 'katalog' ? 'active' : '' ?>">Barang</a>
        <a href="<?= url('/katalog?tab=fasilitas') ?>">Kerusakan Fasilitas</a>
    </nav>

    <div class="public-actions">
        <a class="btn outline small" href="<?= url('/login') ?>">Masuk</a>
        <a class="btn primary small" href="<?= url('/daftar') ?>">Daftar</a>
    </div>
</header>

<main class="public-main">
    <?php if ($sukses): ?>
        <div class="alert alert-success" role="status"><?= e((string) $sukses) ?></div>
    <?php endif; ?>

    <?php if ($gagal): ?>
        <div class="alert alert-error" role="alert"><?= e((string) $gagal) ?></div>
    <?php endif; ?>

    <?= $content ?>
</main>

<footer class="public-footer">
    <div>
        <b><?= e(config('app.name')) ?></b> · <?= e(config('app.tagline')) ?>
    </div>
    <div class="muted" style="font-size:11px">
        Posko penemuan barang: Gedung Rektorat Lt. 1 · Senin–Jumat 07.00–17.00
    </div>
</footer>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
