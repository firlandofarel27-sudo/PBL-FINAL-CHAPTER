<?php

declare(strict_types=1);

use App\Core\Session;

/**
 * Layout untuk halaman masuk dan daftar.
 *
 * @var string  $title
 * @var string  $content
 */

$sukses = Session::pull('success');
$gagal  = Session::pull('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>

<body>

<div class="login-page">
    <div class="login-wrap">
        <div class="brand-panel">
            <div class="brand">
                <div class="brand-mark">⌕</div>
                <div>
                    <b><?= e(config('app.name')) ?></b>
                    <small><?= e(config('app.tagline')) ?></small>
                </div>
            </div>

            <div class="brand-copy">
                <p class="eyebrow">WELCOME TO <?= e(config('app.name')) ?></p>
                <h1>Temukan.<br>Laporkan.<br>Titipkan.</h1>
                <p>
                    Platform informasi kehilangan dan penemuan barang serta
                    pelaporan kerusakan fasilitas di lingkungan kampus.
                </p>
            </div>

            <div class="decor-circles"></div>

            <div class="copyright">© <?= date('Y') ?> <?= e(config('app.name')) ?> - <?= e(config('app.tagline')) ?></div>
        </div>

        <div class="login-panel">
            <div class="login-card">
                <?php if ($sukses): ?>
                    <div class="alert alert-success" role="status"><?= e((string) $sukses) ?></div>
                <?php endif; ?>

                <?php if ($gagal): ?>
                    <div class="alert alert-error" role="alert"><?= e((string) $gagal) ?></div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
