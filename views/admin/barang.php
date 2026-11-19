<?php

declare(strict_types=1);

/**
 * Manajemen laporan barang untuk admin.
 *
 * @var string                            $keyword
 * @var string                            $status
 * @var string                            $jenis
 * @var string                            $lokasi
 * @var array<int, array<string, mixed>>  $items
 * @var array<string, int>                $stat
 * @var array<int, array<string, mixed>>  $lokasis
 */
?>
<?php
/* Tautan ekspor membawa filter yang sedang aktif agar isi berkas sama
   dengan yang terlihat di tabel. */
$filterEkspor = http_build_query(array_filter([
    'q'      => $keyword,
    'status' => $status,
    'jenis'  => $jenis,
    'lokasi' => $lokasi,
], static fn (string $nilai): bool => $nilai !== ''));

$awalan = $filterEkspor === '' ? '?' : '?' . $filterEkspor . '&';
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Manajemen Laporan',
    'sub'   => 'Cari, tinjau, dan kelola laporan kampus.',
    'aksi'  => '<a class="btn outline" href="' . url('/admin/barang/ekspor' . $awalan . 'format=csv') . '">↓ CSV</a>'
        . '<a class="btn outline" href="' . url('/admin/barang/ekspor' . $awalan . 'format=xlsx') . '">↓ Excel</a>'
        . '<a class="btn outline" href="' . url('/admin/fasilitas') . '">Kerusakan fasilitas →</a>',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">▤</div>
        <div class="stat-label">Total laporan</div>
        <div class="stat-number"><?= (int) $stat['laporan'] ?></div>
        <div class="stat-note">Seluruh laporan barang</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">▣</div>
        <div class="stat-label">Menunggu verifikasi</div>
        <div class="stat-number"><?= (int) $stat['menunggu'] ?></div>
        <div class="stat-note">Perlu dicek petugas</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Selesai</div>
        <div class="stat-number"><?= (int) $stat['selesai'] ?></div>
        <div class="stat-note">Sudah diserahterimakan</div>
    </article>
</div>

<form class="toolbar" method="get" action="<?= url('/admin/barang') ?>">
    <div class="searchbox">
        ⌕ <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari judul atau ID laporan...">
    </div>

    <select class="select" name="status" onchange="this.form.submit()">
        <option value="">Semua status</option>
        <?php foreach (['Menunggu Verifikasi', 'Tersedia', 'Selesai'] as $opsi): ?>
            <option value="<?= e($opsi) ?>" <?= $status === $opsi ? 'selected' : '' ?>><?= e($opsi) ?></option>
        <?php endforeach; ?>
    </select>

    <select class="select" name="jenis" onchange="this.form.submit()">
        <option value="">Semua jenis</option>
        <option value="Lost"  <?= $jenis === 'Lost' ? 'selected' : '' ?>>Barang hilang</option>
        <option value="Found" <?= $jenis === 'Found' ? 'selected' : '' ?>>Barang temuan</option>
    </select>

    <select class="select" name="lokasi" onchange="this.form.submit()">
        <option value="">Semua lokasi</option>
        <?php foreach ($lokasis as $item): ?>
            <option value="<?= (int) $item['id_lokasi'] ?>" <?= $lokasi === (string) $item['id_lokasi'] ? 'selected' : '' ?>>
                <?= e($item['nama_lokasi']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="btn outline" type="submit">Cari</button>
    <a class="btn outline" href="<?= url('/admin/barang') ?>">Reset</a>
</form>

<section class="panel">
    <?php if ($items === []): ?>
        <p class="muted" style="font-size:12px">Tidak ada laporan yang cocok dengan filter.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>JUDUL</th>
                    <th>JENIS</th>
                    <th>LOKASI</th>
                    <th>STATUS</th>
                    <th>TANGGAL</th>
                    <th>AKSI</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $id = (int) $item['id_barang']; ?>
                    <tr>
                        <td>TP-<?= $id ?></td>
                        <td>
                            <b><?= e($item['nama_barang']) ?></b>
                            <?php if (!empty($item['prioritas'])): ?>
                                <span class="status red">Prioritas</span>
                            <?php endif; ?>
                            <br><small class="muted"><?= e((string) ($item['nama_pelapor'] ?? '-')) ?></small>
                        </td>
                        <td><?= e(jenis_label((string) $item['jenis'])) ?></td>
                        <td><?= e((string) ($item['nama_lokasi'] ?? '-')) ?></td>
                        <td>
                            <span class="status <?= e(status_class((string) $item['status'])) ?>">
                                <?= e(status_label((string) $item['status'])) ?>
                            </span>
                        </td>
                        <td><?= e(tanggal_id((string) $item['tanggal'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <?php if ((string) $item['jenis'] === 'Found' && $item['status'] === 'Selesai'): ?>
                                    <small class="muted">Selesai lewat redeem</small>
                                <?php else: ?>
                                    <form method="post" action="<?= url('/admin/barang/' . $id . '/status') ?>" class="inline-form">
                                        <?= csrf_field() ?>
                                        <select class="select select-sm" name="status">
                                            <?php foreach (['Menunggu Verifikasi', 'Tersedia', 'Selesai'] as $opsi): ?>
                                                <?php
                                                $nonaktif = $opsi === 'Selesai' && (string) $item['jenis'] === 'Found';
                                                ?>
                                                <option value="<?= e($opsi) ?>"
                                                    <?= $item['status'] === $opsi ? 'selected' : '' ?>
                                                    <?= $nonaktif ? 'disabled' : '' ?>>
                                                    <?= e($opsi) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn small dark" type="submit">Simpan</button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" action="<?= url('/admin/barang/' . $id . '/prioritas') ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="prioritas" value="<?= !empty($item['prioritas']) ? '0' : '1' ?>">
                                    <button class="btn small outline" type="submit">
                                        <?= !empty($item['prioritas']) ? 'Lepas prioritas' : 'Prioritas' ?>
                                    </button>
                                </form>

                                <form method="post" action="<?= url('/admin/barang/' . $id . '/hapus') ?>" class="inline-form"
                                      onsubmit="return confirm('Hapus laporan ini beserta klaimnya?')">
                                    <?= csrf_field() ?>
                                    <button class="btn small outline" type="submit">Hapus</button>
                                </form>

                                <a class="btn small outline" href="<?= url('/laporan/barang/' . $id) ?>">Detail</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
