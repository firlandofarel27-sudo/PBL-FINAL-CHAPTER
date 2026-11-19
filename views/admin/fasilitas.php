<?php

declare(strict_types=1);

/**
 * Manajemen laporan kerusakan fasilitas.
 *
 * @var string                            $keyword
 * @var string                            $status
 * @var string                            $lokasi
 * @var array<int, array<string, mixed>>  $items
 * @var array<string, int>                $stat
 * @var array<int, array<string, mixed>>  $lokasis
 */
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Kerusakan Fasilitas',
    'sub'   => 'Perbarui status perbaikan dan catatan teknisi.',
    'aksi'  => '<a class="btn outline" href="' . url('/admin/barang') . '">← Laporan barang</a>',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">⚡</div>
        <div class="stat-label">Total kerusakan</div>
        <div class="stat-number"><?= (int) $stat['total'] ?></div>
        <div class="stat-note">Seluruh laporan fasilitas</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">▣</div>
        <div class="stat-label">Dilaporkan</div>
        <div class="stat-number"><?= (int) $stat['dilaporkan'] ?></div>
        <div class="stat-note">Belum ditangani</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">◷</div>
        <div class="stat-label">Proses perbaikan</div>
        <div class="stat-number"><?= (int) $stat['proses'] ?></div>
        <div class="stat-note">Sedang dikerjakan</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Selesai</div>
        <div class="stat-number"><?= (int) $stat['selesai'] ?></div>
        <div class="stat-note">Sudah diperbaiki</div>
    </article>
</div>

<form class="toolbar" method="get" action="<?= url('/admin/fasilitas') ?>">
    <div class="searchbox">
        ⌕ <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari fasilitas atau ID...">
    </div>

    <select class="select" name="status" onchange="this.form.submit()">
        <option value="">Semua status</option>
        <?php foreach (['Dilaporkan', 'Proses Perbaikan', 'Selesai'] as $opsi): ?>
            <option value="<?= e($opsi) ?>" <?= $status === $opsi ? 'selected' : '' ?>><?= e($opsi) ?></option>
        <?php endforeach; ?>
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
    <a class="btn outline" href="<?= url('/admin/fasilitas') ?>">Reset</a>
</form>

<section class="panel">
    <?php if ($items === []): ?>
        <p class="muted" style="font-size:12px">Tidak ada laporan kerusakan yang cocok.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>FASILITAS</th>
                    <th>LOKASI</th>
                    <th>STATUS</th>
                    <th>TANGGAL</th>
                    <th>AKSI</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $id = (int) $item['id_kerusakan']; ?>
                    <tr>
                        <td>FS-<?= $id ?></td>
                        <td>
                            <b><?= e($item['nama_fasilitas']) ?></b><br>
                            <small class="muted"><?= e((string) ($item['nama_pelapor'] ?? '-')) ?></small>
                        </td>
                        <td><?= e((string) ($item['nama_lokasi'] ?? '-')) ?></td>
                        <td>
                            <span class="status <?= e(status_class((string) $item['status'])) ?>">
                                <?= e(status_label((string) $item['status'], 'status_fasilitas')) ?>
                            </span>
                        </td>
                        <td><?= e(tanggal_id((string) $item['tanggal_lapor'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <form method="post" action="<?= url('/admin/fasilitas/' . $id . '/status') ?>" class="inline-form">
                                    <?= csrf_field() ?>
                                    <select class="select select-sm" name="status">
                                        <?php foreach (['Dilaporkan', 'Proses Perbaikan', 'Selesai'] as $opsi): ?>
                                            <option value="<?= e($opsi) ?>" <?= $item['status'] === $opsi ? 'selected' : '' ?>>
                                                <?= e($opsi) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input class="input-sm" name="catatan_teknisi" placeholder="Catatan teknisi"
                                           value="<?= e((string) ($item['catatan_teknisi'] ?? '')) ?>">
                                    <button class="btn small dark" type="submit">Simpan</button>
                                </form>

                                <form method="post" action="<?= url('/admin/fasilitas/' . $id . '/hapus') ?>" class="inline-form"
                                      onsubmit="return confirm('Hapus laporan kerusakan ini?')">
                                    <?= csrf_field() ?>
                                    <button class="btn small outline" type="submit">Hapus</button>
                                </form>

                                <a class="btn small outline" href="<?= url('/laporan/fasilitas/' . $id) ?>">Detail</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
