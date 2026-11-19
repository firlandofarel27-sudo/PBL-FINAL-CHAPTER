<?php

declare(strict_types=1);

/**
 * Analitik tren dan kinerja layanan.
 *
 * @var array<string, mixed> $statistik
 */

$kategori = $statistik['kategori'];
$maksimal = 1;

foreach ($kategori as $baris) {
    $maksimal = max($maksimal, (int) $baris['jumlah']);
}

$persen = (float) $statistik['persen'];
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => App\Core\Auth::is('Super Admin') ? 'Analitik sistem' : 'Analitik laporan',
    'sub'   => 'Pantau tren dan kinerja layanan TITIPIN.',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">▤</div>
        <div class="stat-label">Total laporan</div>
        <div class="stat-number"><?= (int) $statistik['total'] ?></div>
        <div class="stat-note">Barang dan fasilitas</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Terselesaikan</div>
        <div class="stat-number"><?= e(number_format($persen, 1, ',', '.')) ?>%</div>
        <div class="stat-note">Target <?= (int) $statistik['target'] ?>%</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">◷</div>
        <div class="stat-label">Dalam proses</div>
        <div class="stat-number"><?= (int) $statistik['proses'] ?></div>
        <div class="stat-note">Belum selesai</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">♙</div>
        <div class="stat-label">Pengguna aktif</div>
        <div class="stat-number"><?= (int) $statistik['pengguna_aktif'] ?></div>
        <div class="stat-note">Dari <?= (int) $statistik['pengguna'] ?> akun terdaftar</div>
    </article>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <h2 style="font-size:15px">Laporan berdasarkan kategori</h2>

        <?php if ($kategori === []): ?>
            <p class="muted" style="font-size:12px">Belum ada data kategori.</p>
        <?php else: ?>
            <div class="chart-bars">
                <?php foreach ($kategori as $baris): ?>
                    <?php $lebar = (int) round((int) $baris['jumlah'] / $maksimal * 100); ?>
                    <div class="bar-row">
                        <span><?= e((string) $baris['nama']) ?></span>
                        <div class="bar-track">
                            <span style="width:<?= $lebar ?>%"></span>
                        </div>
                        <b><?= (int) $baris['jumlah'] ?></b>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2 style="font-size:15px">Status penyelesaian</h2>
        <div class="target-number"><?= e(number_format($persen, 1, ',', '.')) ?>%</div>

        <p class="muted" style="font-size:11px">
            Laporan selesai sesuai standar layanan
        </p>

        <div class="progress">
            <span style="width:<?= $persen ?>%"></span>
        </div>

        <div class="action-row">
            <div class="mini-icon">✓</div>
            <div class="action-copy">
                <b>Selesai</b>
                <small><?= (int) $statistik['selesai'] ?> laporan</small>
            </div>
            <span class="status green"><?= e(number_format($persen, 1, ',', '.')) ?>%</span>
        </div>

        <div class="action-row">
            <div class="mini-icon">◷</div>
            <div class="action-copy">
                <b>Dalam proses</b>
                <small><?= (int) $statistik['proses'] ?> laporan</small>
            </div>
            <span class="status blue">
                <?= e(number_format(100 - $persen, 1, ',', '.')) ?>%
            </span>
        </div>
    </section>
</div>

<div class="dashboard-grid" style="margin-top:16px">
    <section class="panel">
        <h2 style="font-size:15px">Laporan barang</h2>

        <div class="action-row">
            <div class="mini-icon">⊗</div>
            <div class="action-copy"><b>Barang hilang</b><small>Total laporan</small></div>
            <span class="status red"><?= (int) $statistik['barang']['hilang'] ?></span>
        </div>

        <div class="action-row">
            <div class="mini-icon">◇</div>
            <div class="action-copy"><b>Barang temuan</b><small>Total laporan</small></div>
            <span class="status blue"><?= (int) $statistik['barang']['temuan'] ?></span>
        </div>

        <div class="action-row">
            <div class="mini-icon">▣</div>
            <div class="action-copy"><b>Menunggu verifikasi</b><small>Perlu ditindak</small></div>
            <span class="status"><?= (int) $statistik['barang']['menunggu'] ?></span>
        </div>
    </section>

    <section class="panel">
        <h2 style="font-size:15px">Kerusakan fasilitas</h2>

        <div class="action-row">
            <div class="mini-icon">⚡</div>
            <div class="action-copy"><b>Dilaporkan</b><small>Belum ditangani</small></div>
            <span class="status red"><?= (int) $statistik['fasilitas']['dilaporkan'] ?></span>
        </div>

        <div class="action-row">
            <div class="mini-icon">◷</div>
            <div class="action-copy"><b>Proses perbaikan</b><small>Sedang dikerjakan</small></div>
            <span class="status blue"><?= (int) $statistik['fasilitas']['proses'] ?></span>
        </div>

        <div class="action-row">
            <div class="mini-icon">✓</div>
            <div class="action-copy"><b>Selesai</b><small>Sudah diperbaiki</small></div>
            <span class="status green"><?= (int) $statistik['fasilitas']['selesai'] ?></span>
        </div>
    </section>
</div>

<section class="panel" style="margin-top:16px">
    <h2 style="font-size:15px">Laporan masuk per bulan</h2>

    <?php if ($statistik['tren'] === []): ?>
        <p class="muted" style="font-size:12px">Belum ada data tanggal laporan.</p>
    <?php else: ?>
        <div class="chart-bars">
            <?php foreach ($statistik['tren'] as $baris): ?>
                <?php $lebar = (int) round((int) $baris['jumlah'] / max(1, (int) $baris['jumlah']) * 100); ?>
                <div class="bar-row">
                    <span><?= e((string) $baris['label']) ?></span>
                    <div class="bar-track">
                        <span style="width:<?= $lebar ?>%"></span>
                    </div>
                    <b><?= (int) $baris['jumlah'] ?></b>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
