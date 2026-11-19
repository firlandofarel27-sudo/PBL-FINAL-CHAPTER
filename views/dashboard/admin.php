<?php

declare(strict_types=1);

/**
 * Beranda admin.
 *
 * @var array<string, int>              $stat
 * @var array<int, array<string, mixed>> $menunggu
 * @var array<int, array<string, mixed>> $fasilitas
 */

$total  = max(1, (int) $stat['laporan']);
$persen = (int) round((int) $stat['selesai'] / $total * 100);
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Ringkasan hari ini',
    'sub'   => 'Selamat bertugas, ' . (App\Core\Auth::user()['nama_lengkap'] ?? 'Admin'),
    'aksi'  => '<a class="btn outline" href="' . url('/admin/barang') . '">Lihat laporan</a>',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">▤</div>
        <div class="stat-label">Total Laporan</div>
        <div class="stat-number"><?= (int) $stat['laporan'] ?></div>
        <div class="stat-note"><?= (int) $stat['menunggu'] ?> menunggu verifikasi</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">▣</div>
        <div class="stat-label">Klaim Pending</div>
        <div class="stat-number"><?= (int) $stat['klaim_pending'] ?></div>
        <div class="stat-note">Perlu diverifikasi</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">⬡</div>
        <div class="stat-label">Kerusakan Aktif</div>
        <div class="stat-number"><?= (int) $stat['fasilitas_aktif'] ?></div>
        <div class="stat-note"><?= (int) $stat['prioritas'] ?> laporan prioritas</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Laporan Selesai</div>
        <div class="stat-number"><?= (int) $stat['selesai'] ?></div>
        <div class="stat-note">Dari total laporan barang</div>
    </article>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <div class="section-head">
            <h2>Perlu tindakan</h2>
            <a href="<?= url('/admin/barang?status=Menunggu+Verifikasi') ?>">Lihat semua</a>
        </div>

        <?php if ($menunggu === []): ?>
            <p class="muted" style="font-size:12px">Tidak ada laporan yang menunggu verifikasi.</p>
        <?php else: ?>
            <?php foreach (array_slice($menunggu, 0, 4) as $item): ?>
                <div class="action-row">
                    <div class="mini-icon"><?= e(jenis_icon((string) $item['jenis'])) ?></div>
                    <div class="action-copy">
                        <b><?= e($item['nama_barang']) ?></b>
                        <small><?= e(jenis_label((string) $item['jenis'])) ?> · <?= e((string) ($item['nama_lokasi'] ?? '-')) ?></small>
                    </div>
                    <a class="btn small dark" href="<?= url('/laporan/barang/' . (int) $item['id_barang']) ?>">Cek</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="section-head">
            <h2>Target layanan</h2>
        </div>

        <div class="target-number"><?= $persen ?>%</div>

        <p class="muted" style="font-size:11px">
            <?= (int) $stat['selesai'] ?> dari <?= (int) $stat['laporan'] ?> laporan barang terselesaikan
        </p>

        <div class="progress">
            <span style="width:<?= $persen ?>%"></span>
        </div>

        <p class="muted" style="font-size:10px">
            <?= max(0, 100 - $persen) ?>% lagi untuk mencapai penyelesaian penuh.
        </p>

        <a class="btn outline full" href="<?= url('/admin/analitik') ?>">Lihat analitik</a>
    </section>
</div>

<section class="panel" style="margin-top:16px">
    <div class="section-head">
        <h2>Kerusakan fasilitas terbaru</h2>
        <a href="<?= url('/admin/fasilitas') ?>">Kelola fasilitas</a>
    </div>

    <?php if ($fasilitas === []): ?>
        <p class="muted" style="font-size:12px">Tidak ada laporan kerusakan baru.</p>
    <?php else: ?>
        <?php foreach (array_slice($fasilitas, 0, 3) as $item): ?>
            <div class="action-row">
                <div class="mini-icon">⚡</div>
                <div class="action-copy">
                    <b><?= e($item['nama_fasilitas']) ?></b>
                    <small><?= e((string) ($item['nama_lokasi'] ?? '-')) ?> · <?= e(tanggal_id((string) $item['tanggal_lapor'])) ?></small>
                </div>
                <a class="btn small dark" href="<?= url('/laporan/fasilitas/' . (int) $item['id_kerusakan']) ?>">Cek</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
