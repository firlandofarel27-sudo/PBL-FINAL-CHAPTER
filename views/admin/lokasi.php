<?php

declare(strict_types=1);

/**
 * Daftar lokasi dan unit kampus.
 *
 * Kolom utama tabel adalah jumlah **barang temuan** per ruangan, sehingga
 * terlihat ruangan mana yang paling sering menjadi tempat barang
 * tertinggal. Jumlah laporan total tetap dipakai untuk menjaga aturan
 * penghapusan, tetapi tidak lagi ditampilkan sebagai kolom tersendiri.
 *
 * @var string                            $keyword
 * @var string                            $gedung
 * @var string                            $urutan
 * @var array<int, array<string, mixed>>  $items
 * @var array<int, int>                   $pemakaian
 * @var array<int, int>                   $temuan
 * @var array<string, int>                $lantai
 * @var array<int, string>                $floors
 */

$totalTemuan  = array_sum($temuan);
$tanpaTemuan  = 0;
$palingSering = null;

foreach ($items as $item) {
    $id     = (int) $item['id_lokasi'];
    $jumlah = (int) ($temuan[$id] ?? 0);

    if ($jumlah === 0) {
        $tanpaTemuan++;
    }

    if ($palingSering === null || $jumlah > $palingSering['jumlah']) {
        $palingSering = ['nama' => (string) $item['nama_lokasi'], 'jumlah' => $jumlah];
    }
}

$tautanUrut = static function (string $nilai) use ($keyword, $gedung): string {
    return url('/admin/lokasi?' . http_build_query(array_filter([
        'q'      => $keyword,
        'gedung' => $gedung,
        'urutan' => $nilai === 'lantai' ? '' : $nilai,
    ])));
};
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Lokasi & Unit',
    'sub'   => 'Lihat ruangan mana yang paling sering menjadi tempat barang tertinggal.',
    'aksi'  => '<a class="btn primary" href="' . url('/admin/lokasi/baru') . '">＋ Tambah lokasi</a>',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">⌖</div>
        <div class="stat-label">Lokasi aktif</div>
        <div class="stat-number"><?= count($items) ?></div>
        <div class="stat-note">Muncul di formulir laporan</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">◇</div>
        <div class="stat-label">Barang ditemukan</div>
        <div class="stat-number"><?= $totalTemuan ?></div>
        <div class="stat-note">Tercatat di seluruh ruangan</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">◈</div>
        <div class="stat-label">Paling sering</div>
        <div class="stat-number" style="font-size:20px">
            <?= e($palingSering['nama'] ?? '-') ?>
        </div>
        <div class="stat-note"><?= (int) ($palingSering['jumlah'] ?? 0) ?> barang tertinggal</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Tanpa temuan</div>
        <div class="stat-number"><?= $tanpaTemuan ?></div>
        <div class="stat-note">Belum pernah ada barang tertinggal</div>
    </article>
</div>

<form class="toolbar" method="get" action="<?= url('/admin/lokasi') ?>">
    <div class="searchbox">
        ⌕ <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari ruangan atau lantai...">
    </div>

    <select class="select" name="gedung" onchange="this.form.submit()">
        <option value="">Semua lantai</option>
        <?php foreach ($floors as $opsi): ?>
            <option value="<?= e($opsi) ?>" <?= $gedung === $opsi ? 'selected' : '' ?>>
                <?= e($opsi) ?> (<?= (int) ($lantai[$opsi] ?? 0) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <select class="select" name="urutan" onchange="this.form.submit()">
        <option value="lantai" <?= $urutan === 'lantai' ? 'selected' : '' ?>>Urut: lantai</option>
        <option value="temuan" <?= $urutan === 'temuan' ? 'selected' : '' ?>>Urut: temuan terbanyak</option>
    </select>

    <button class="btn outline" type="submit">Cari</button>
    <a class="btn outline" href="<?= url('/admin/lokasi') ?>">Reset</a>
</form>

<section class="panel">
    <div class="section-head">
        <h2>Daftar lokasi</h2>
        <span class="status"><?= count($items) ?> lokasi</span>
    </div>

    <?php if ($items === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">⌖</div>
            <h3>Tidak ada lokasi yang cocok</h3>
            <p>Coba ubah kata kunci atau filter lantai.</p>
            <a class="btn primary" href="<?= url('/admin/lokasi') ?>">Tampilkan semua</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table tabel-lokasi">
                <thead>
                <tr>
                    <th>GEDUNG / LANTAI</th>
                    <th>NAMA LOKASI</th>
                    <th>BARANG DITEMUKAN</th>
                    <th>AKSI</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $tertinggi = 0;

                foreach ($items as $item) {
                    $tertinggi = max($tertinggi, (int) ($temuan[(int) $item['id_lokasi']] ?? 0));
                }
                ?>

                <?php foreach ($items as $item): ?>
                    <?php
                    $id      = (int) $item['id_lokasi'];
                    $ditemukan = (int) ($temuan[$id] ?? 0);
                    $dipakai   = (int) ($pemakaian[$id] ?? 0);
                    $teratas   = $tertinggi > 0 && $ditemukan === $tertinggi;
                    ?>
                    <tr class="<?= $teratas ? 'baris-sorot' : '' ?>">
                        <td><span class="status"><?= e((string) ($item['gedung'] ?: 'Tanpa lantai')) ?></span></td>
                        <td>
                            <b><?= e((string) $item['nama_lokasi']) ?></b>
                            <?php if ($teratas): ?>
                                <span class="status red">Paling sering</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ditemukan > 0): ?>
                                <span class="status blue"><?= $ditemukan ?> barang</span>
                            <?php else: ?>
                                <span class="muted" style="font-size:11px">Belum ada temuan</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a class="btn small outline"
                                   href="<?= url('/admin/lokasi/' . $id . '/ubah') ?>">Edit</a>

                                <?php if ($dipakai > 0): ?>
                                    <button class="btn small outline" type="button" disabled
                                            title="Masih dipakai <?= $dipakai ?> laporan">Hapus</button>
                                <?php else: ?>
                                    <form method="post" action="<?= url('/admin/lokasi/' . $id . '/hapus') ?>"
                                          class="inline-form"
                                          onsubmit="return confirm('Nonaktifkan lokasi ini? Lokasi tidak akan muncul lagi di formulir laporan.')">
                                        <?= csrf_field() ?>
                                        <button class="btn small outline" type="submit">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
