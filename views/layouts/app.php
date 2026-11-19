<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Session;

/**
 * Shell aplikasi: sidebar, topbar, area konten, dan navigasi bawah.
 *
 * @var string  $title
 * @var string  $content
 * @var string  $active
 */

$user   = Auth::user();
$role   = $user['role'] ?? 'Mahasiswa';
$menu   = config('app.menu.' . $role, []);
$sukses = Session::pull('success');
$gagal  = Session::pull('error');
$aktif  = $active ?? '';
$bawah  = array_slice($menu, 0, 5);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>

<body data-role="<?= e($role) ?>">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= url('/beranda') ?>">
            <span class="brand-mark">⌕</span>
            <div>
                <b><?= e(config('app.name')) ?></b>
                <small><?= e(config('app.tagline')) ?></small>
            </div>
        </a>

        <div class="side-label">Menu utama</div>

        <nav class="nav-links">
            <?php foreach ($menu as [$kunci, $ikon, $label, $path]): ?>
                <a class="nav-link<?= $aktif === $kunci ? ' active' : '' ?>"
                   href="<?= url($path) ?>">
                    <span class="nav-icon"><?= e($ikon) ?></span><?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="side-bottom">
            <div class="profile-mini">
                <div class="avatar">
                    <?php if (!empty($user['foto_profil'])): ?>
                        <img src="<?= e((string) upload_url($user['foto_profil'])) ?>"
                             alt="Foto profil <?= e($user['nama_lengkap'] ?? '') ?>">
                    <?php else: ?>
                        <?= e(initials((string) ($user['nama_lengkap'] ?? 'Pengguna'))) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <?= e($user['nama_lengkap'] ?? 'Pengguna') ?>
                    <small><?= e($role) ?></small>
                </div>
            </div>

            <form method="post" action="<?= url('/logout') ?>" class="logout-form">
                <?= csrf_field() ?>
                <button class="btn small outline full" type="submit">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:10px">
                <button class="icon-btn mobile-menu" id="menuBtn" type="button" aria-label="Buka menu">☰</button>
                <span class="breadcrumb">
                    <?= e(config('app.name')) ?> / <b><?= e($aktif === '' ? 'Beranda' : $aktif) ?></b>
                </span>
            </div>

            <div class="top-actions">
                <?php
                $notifikasi = App\Services\NotifikasiService::untuk($user);
                $jumlahNotif = count($notifikasi);
                ?>

                <div class="notif-wrap" id="notifWrap">
                    <button class="icon-btn" type="button" id="notifBtn"
                            aria-label="Notifikasi" aria-expanded="false"
                            aria-controls="notifPanel">
                        ♧
                        <?php if ($jumlahNotif > 0): ?>
                            <span class="notif-badge"><?= $jumlahNotif ?></span>
                        <?php endif; ?>
                    </button>

                    <div class="notif-panel" id="notifPanel" role="dialog" aria-label="Notifikasi" hidden>
                        <div class="notif-head">
                            <b>Notifikasi</b>
                            <span class="muted" style="font-size:10px"><?= $jumlahNotif ?> item</span>
                        </div>

                        <?php if ($notifikasi === []): ?>
                            <p class="muted" style="font-size:11px;padding:14px 8px;margin:0">
                                Belum ada notifikasi. Semua laporan dan klaim kamu sudah ditangani.
                            </p>
                        <?php endif; ?>

                        <?php foreach ($notifikasi as $notif): ?>
                            <a class="notif-item" href="<?= url($notif['tautan']) ?>">
                                <span class="notif-dot <?= e($notif['nada']) ?>"></span>
                                <span>
                                    <b><?= e($notif['judul']) ?></b>
                                    <small><?= e($notif['keterangan']) ?></small>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </header>

        <section class="content" id="pageContent">
            <?php if ($sukses): ?>
                <div class="alert alert-success" role="status"><?= e((string) $sukses) ?></div>
            <?php endif; ?>

            <?php if ($gagal): ?>
                <div class="alert alert-error" role="alert"><?= e((string) $gagal) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </section>
    </main>

    <nav class="mobile-nav" aria-label="Navigasi utama">
        <?php foreach ($bawah as [$kunci, $ikon, $label, $path]): ?>
            <a href="<?= url($path) ?>"
               class="<?= $aktif === $kunci ? 'active' : '' ?>"
               aria-label="<?= e($label) ?>">
                <span><?= e($ikon) ?></span><?= e($label) ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
