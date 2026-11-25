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
<html lang="id" data-user="<?= e((string) ($user['nim'] ?? '')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <script>
        /* Preferensi ringkas/lebar sidebar dibaca sebelum CSS dilukis
           agar sidebar tidak berkedip saat halaman dimuat. Disimpan
           per pengguna (NIM), jadi tiap akun punya pilihan sendiri. */
        (function () {
            var nim = document.documentElement.dataset.user;
            var kunci = nim ? 'titipin.sidebar.' + nim : 'titipin.sidebar';

            try {
                if (window.localStorage.getItem(kunci) === 'tutup') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {
                /* localStorage diblokir: pakai sidebar lebar. */
            }
        })();
    </script>
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
                   href="<?= url($path) ?>" title="<?= e($label) ?>">
                    <span class="nav-icon"><?= e($ikon) ?></span><span class="nav-text"><?= e($label) ?></span>
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
                <button class="btn small outline full" type="submit" title="Keluar">
                    <span class="logout-icon">⏻</span><span class="logout-text">Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:10px">
                <button class="icon-btn mobile-menu" id="menuBtn" type="button" aria-label="Buka menu">
                    <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">
                        <path d="M2.5 4.5h11M2.5 8h11M2.5 11.5h11"
                              fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </button>
                <button class="icon-btn desktop-menu" id="collapseBtn" type="button"
                        aria-controls="sidebar" aria-expanded="true"
                        aria-label="Sembunyikan menu samping" title="Sembunyikan menu samping">
                    <svg class="collapse-icon" viewBox="0 0 16 16" width="16" height="16"
                         aria-hidden="true" focusable="false">
                        <path d="M7 3.6 2.6 8 7 12.4M13.4 3.6 9 8l4.4 4.4"
                              fill="none" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
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
                        <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">
                            <path d="M8 2.2a3.4 3.4 0 0 0-3.4 3.4v2.3L3.4 10.2h9.2L11.4 7.9V5.6A3.4 3.4 0 0 0 8 2.2Z"
                                  fill="none" stroke="currentColor" stroke-width="1.4"
                                  stroke-linejoin="round"/>
                            <path d="M6.6 11.9a1.5 1.5 0 0 0 2.8 0"
                                  fill="none" stroke="currentColor" stroke-width="1.4"
                                  stroke-linecap="round"/>
                        </svg>
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
