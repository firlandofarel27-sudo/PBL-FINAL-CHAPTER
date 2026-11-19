<?php

declare(strict_types=1);

/**
 * Katalog barang hilang/temuan dan kerusakan fasilitas.
 *
 * @var string                            $tab
 * @var string                            $keyword
 * @var string                            $kategori
 * @var string                            $lokasi
 * @var array<int, array<string, mixed>>  $items
 * @var array<int, array<string, mixed>>  $kategoris
 * @var array<int, array<string, mixed>>  $lokasis
 */

$isBarang = $tab === 'barang';
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Katalog',
    'sub'   => 'Jelajahi barang temuan dan laporan kerusakan di kampus.',
]) ?>

<div class="tab-bar">
    <a class="tab-btn<?= $isBarang ? ' active' : '' ?>"
       href="<?= url('/katalog?tab=barang') ?>">
        ◇ Barang Hilang &amp; Temuan
    </a>
    <a class="tab-btn<?= $isBarang ? '' : ' active' ?>"
       href="<?= url('/katalog?tab=fasilitas') ?>">
        ⚡ Kerusakan Fasilitas
    </a>
</div>

<form class="toolbar" method="get" action="<?= url('/katalog') ?>">
    <input type="hidden" name="tab" value="<?= e($tab) ?>">

    <div class="searchbox">
        ⌕ <input type="search" name="q" value="<?= e($keyword) ?>"
                 placeholder="<?= $isBarang ? 'Cari nama barang atau lokasi...' : 'Cari fasilitas atau lokasi...' ?>">
    </div>

    <select class="select" name="kategori" onchange="this.form.submit()">
        <option value="">Semua kategori</option>
        <?php foreach ($kategoris as $item): ?>
            <option value="<?= (int) $item['id_kategori'] ?>"
                <?= $kategori === (string) $item['id_kategori'] ? 'selected' : '' ?>>
                <?= e($item['nama_kategori']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select class="select" name="lokasi" onchange="this.form.submit()">
        <option value="">Semua lokasi</option>
        <?php foreach ($lokasis as $item): ?>
            <option value="<?= (int) $item['id_lokasi'] ?>"
                <?= $lokasi === (string) $item['id_lokasi'] ? 'selected' : '' ?>>
                <?= e(trim(($item['gedung'] ? $item['gedung'] . ' · ' : '') . $item['nama_lokasi'])) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button class="btn outline" type="submit">Cari</button>
</form>

<div class="item-grid">
    <?php if ($items === []): ?>
        <div class="empty-state" style="grid-column:1/-1">
            <div class="empty-state-icon"><?= $isBarang ? '⌕' : '⚡' ?></div>
            <h3>Tidak ada data yang cocok</h3>
            <p>Coba ubah kata kunci atau filter yang dipakai.</p>
            <a class="btn primary" href="<?= url('/laporan/baru') ?>">
                ＋ Buat laporan baru
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <?= App\Core\View::capture('partials/item-card', [
                'item' => $item,
                'kind' => $isBarang ? 'barang' : 'fasilitas',
            ]) ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
