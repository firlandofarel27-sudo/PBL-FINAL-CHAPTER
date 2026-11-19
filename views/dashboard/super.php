<?php

declare(strict_types=1);

/**
 * Dashboard super admin.
 *
 * @var array<string, int|float>         $stat
 * @var array<int, array<string, mixed>> $terbaru
 * @var array<int, array<string, mixed>> $fasilitas
 */

$persen = (float) $stat['persen'];
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Dashboard sistem',
    'sub'   => 'Ikhtisar aktivitas seluruh kampus',
    'aksi'  => '<a class="btn primary" href="' . url('/admin/pengguna') . '">＋ Tambah pengguna</a>',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">♙</div>
        <div class="stat-label">Total Pengguna</div>
        <div class="stat-number"><?= (int) $stat['pengguna'] ?></div>
        <div class="stat-note">Seluruh peran terdaftar</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">▤</div>
        <div class="stat-label">Total Laporan</div>
        <div class="stat-number"><?= (int) $stat['laporan'] ?></div>
        <div class="stat-note">Barang dan fasilitas</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Tingkat Selesai</div>
        <div class="stat-number"><?= e(number_format($persen, 1, ',', '.')) ?>%</div>
        <div class="stat-note"><span class="positive"><?= (int) $stat['selesai'] ?> laporan selesai</span></div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">⌖</div>
        <div class="stat-label">Lokasi Terdaftar</div>
        <div class="stat-number"><?= (int) $stat['lokasi'] ?></div>
        <div class="stat-note"><?= (int) $stat['klaim'] ?> klaim pending</div>
    </article>
</div>

<div class="dashboard-grid">
    <section class="panel">
        <div class="section-head">
            <h2>Aktivitas terbaru</h2>
            <a href="<?= url('/admin/barang') ?>">Semua laporan →</a>
        </div>

        <?php if ($terbaru === []): ?>
            <p class="muted" style="font-size:12px">Belum ada laporan masuk.</p>
        <?php else: ?>
            <?php foreach (array_slice($terbaru, 0, 4) as $item): ?>
                <div class="action-row">
                    <div class="mini-icon"><?= e(jenis_icon((string) $item['jenis'])) ?></div>
                    <div class="action-copy">
                        <b><?= e($item['nama_barang']) ?></b>
                        <small><?= e($item['nama_pelapor'] ?? '-') ?> · <?= e(status_label((string) $item['status'])) ?></small>
                    </div>
                    <a class="btn small dark" href="<?= url('/laporan/barang/' . (int) $item['id_barang']) ?>">Detail</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="section-head">
            <h2>Kesehatan sistem</h2>
            <span class="status green">Normal</span>
        </div>

        <div class="target-number"><?= e(number_format($persen, 1, ',', '.')) ?>%</div>

        <p class="muted" style="font-size:11px">
            Tingkat penyelesaian seluruh laporan
        </p>

        <div class="progress">
            <span style="width:<?= $persen ?>%"></span>
        </div>

        <div class="action-row">
            <div class="mini-icon">✓</div>
            <div class="action-copy">
                <b>Database</b>
                <small>Terhubung ke PostgreSQL</small>
            </div>
            <span class="status green">Aktif</span>
        </div>

        <div class="action-row">
            <div class="mini-icon">✓</div>
            <div class="action-copy">
                <b>Folder unggahan</b>
                <small><?= count($fasilitas) ?> laporan fasilitas tersimpan</small>
            </div>
            <span class="status green">Aktif</span>
        </div>
    </section>
</div>
