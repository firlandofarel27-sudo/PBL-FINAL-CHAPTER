<?php

declare(strict_types=1);

/**
 * Riwayat laporan milik mahasiswa: barang dan fasilitas.
 *
 * @var string                            $keyword
 * @var string                            $status
 * @var array<int, array<string, mixed>>  $barang
 * @var array<int, array<string, mixed>>  $fasilitas
 */

$statusBarang = ['', 'Menunggu Verifikasi', 'Tersedia', 'Selesai'];
$statusFasilitas = ['', 'Dilaporkan', 'Proses Perbaikan', 'Selesai'];
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Laporan Saya',
    'sub'   => 'Pantau status laporan yang kamu buat.',
    'aksi'  => '<a class="btn primary" href="' . url('/laporan/baru') . '">＋ Buat laporan</a>',
]) ?>

<form class="toolbar" method="get" action="<?= url('/riwayat') ?>">
    <div class="searchbox">
        ⌕ <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari nama atau ID laporan...">
    </div>

    <select class="select" name="status" onchange="this.form.submit()">
        <option value="">Semua status</option>
        <?php foreach (array_unique(array_merge($statusBarang, $statusFasilitas)) as $opsi): ?>
            <?php if ($opsi === '') {
                continue;
            } ?>
            <option value="<?= e($opsi) ?>" <?= $status === $opsi ? 'selected' : '' ?>><?= e($opsi) ?></option>
        <?php endforeach; ?>
    </select>

    <button class="btn outline" type="submit">Cari</button>
</form>

<section class="panel">
    <div class="section-head">
        <h2>Laporan barang</h2>
        <span class="status"><?= count($barang) ?> laporan</span>
    </div>

    <?php if ($barang === []): ?>
        <p class="muted" style="font-size:12px">
            Belum ada laporan barang.
            <a href="<?= url('/laporan/baru') ?>"><b>Buat sekarang</b></a>.
        </p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>JUDUL</th>
                    <th>KATEGORI</th>
                    <th>LOKASI</th>
                    <th>STATUS</th>
                    <th>TANGGAL</th>
                    <th>AKSI</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($barang as $item): ?>
                    <tr>
                        <td>TP-<?= (int) $item['id_barang'] ?></td>
                        <td><b><?= e($item['nama_barang']) ?></b></td>
                        <td><?= e(jenis_label((string) $item['jenis'])) ?></td>
                        <td><?= e((string) ($item['nama_lokasi'] ?? '-')) ?></td>
                        <td>
                            <span class="status <?= e(status_class((string) $item['status'])) ?>">
                                <?= e(status_label((string) $item['status'])) ?>
                            </span>
                        </td>
                        <td><?= e(tanggal_id((string) $item['tanggal'])) ?></td>
                        <td>
                            <a class="btn small outline" href="<?= url('/laporan/barang/' . (int) $item['id_barang']) ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel" style="margin-top:16px">
    <div class="section-head">
        <h2>Laporan kerusakan fasilitas</h2>
        <span class="status"><?= count($fasilitas) ?> laporan</span>
    </div>

    <?php if ($fasilitas === []): ?>
        <p class="muted" style="font-size:12px">
            Belum ada laporan kerusakan.
            <a href="<?= url('/laporan/baru?jenis=Rusak') ?>"><b>Laporkan kerusakan</b></a>.
        </p>
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
                <?php foreach ($fasilitas as $item): ?>
                    <tr>
                        <td>FS-<?= (int) $item['id_kerusakan'] ?></td>
                        <td><b><?= e($item['nama_fasilitas']) ?></b></td>
                        <td><?= e((string) ($item['nama_lokasi'] ?? '-')) ?></td>
                        <td>
                            <span class="status <?= e(status_class((string) $item['status'])) ?>">
                                <?= e(status_label((string) $item['status'], 'status_fasilitas')) ?>
                            </span>
                        </td>
                        <td><?= e(tanggal_id((string) $item['tanggal_lapor'])) ?></td>
                        <td>
                            <a class="btn small outline" href="<?= url('/laporan/fasilitas/' . (int) $item['id_kerusakan']) ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
