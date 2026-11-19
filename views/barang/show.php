<?php

declare(strict_types=1);

/**
 * Detail satu laporan barang.
 *
 * @var array<string, mixed>             $barang
 * @var array<int, array<string, mixed>> $klaim
 * @var bool                             $bisaKlaim
 */

$tempat = trim((string) ($barang['nama_lokasi'] ?? '') . ' · ' . (string) ($barang['lokasi_detail'] ?? ''), ' ·');
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => (string) $barang['nama_barang'],
    'sub'   => 'TP-' . (int) $barang['id_barang'] . ' · ' . tanggal_id((string) $barang['tanggal']),
    'aksi'  => '<a class="btn outline" href="' . url('/riwayat') . '">← Kembali</a>',
]) ?>

<div class="dashboard-grid">
    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Detail laporan</h2>
            <span class="status <?= e(status_class((string) $barang['status'])) ?>">
                <?= e(status_label((string) $barang['status'])) ?>
            </span>
        </div>

        <?php if (!empty($barang['foto'])): ?>
            <img class="detail-photo" src="<?= e((string) upload_url($barang['foto'])) ?>"
                 alt="Foto <?= e($barang['nama_barang']) ?>">
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <div class="meta-label">KATEGORI</div>
                <span class="status <?= (string) $barang['jenis'] === 'Lost' ? 'red' : 'blue' ?>">
                    <?= e(jenis_label((string) $barang['jenis'])) ?>
                </span>
            </div>
            <div>
                <div class="meta-label">PRIORITAS</div>
                <span class="status <?= !empty($barang['prioritas']) ? 'red' : '' ?>">
                    <?= !empty($barang['prioritas']) ? 'Prioritas' : 'Normal' ?>
                </span>
            </div>
        </div>

        <p><b>Lokasi:</b> <?= e($tempat === '' ? 'Belum diisi' : $tempat) ?></p>
        <p><b>Waktu kejadian:</b> <?= e((string) ($barang['waktu_kejadian'] ?: 'Tidak diketahui')) ?></p>
        <p><b>Kategori barang:</b> <?= e((string) ($barang['nama_kategori'] ?? 'Tanpa kategori')) ?></p>

        <p style="font-size:12px;background:#f8faf9;border-radius:8px;padding:12px;line-height:1.6">
            <?= nl2br(e((string) ($barang['deskripsi'] ?? '-'))) ?>
        </p>

        <p style="font-size:11px;color:var(--muted)">
            <b>Dilaporkan oleh:</b>
            <?= App\Core\Auth::check()
                ? e((string) ($barang['nama_pelapor'] ?? '-'))
                : 'disembunyikan untuk pengunjung' ?>
        </p>
    </section>

    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Status &amp; tindakan</h2>
        </div>

        <?php if ($bisaKlaim): ?>
            <div style="padding:12px;background:#edf8ff;border-radius:8px;border:1px solid #c7e4ff">
                <b style="font-size:12px">Apakah ini barang kamu?</b>
                <p style="font-size:11px;color:var(--muted);margin:6px 0 10px">
                    <?php if (App\Core\Auth::check()): ?>
                        Ajukan klaim dengan bukti kepemilikan. Petugas akan memverifikasi kode redeem saat serah terima.
                    <?php else: ?>
                        Masuk dengan akun kampus dulu, lalu ajukan klaim dengan bukti kepemilikan.
                    <?php endif; ?>
                </p>
                <a class="btn primary small" href="<?= url('/klaim/baru/' . (int) $barang['id_barang']) ?>">
                    ◈ <?= App\Core\Auth::check() ? 'Ajukan klaim barang ini' : 'Masuk untuk mengajukan klaim' ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if ((string) $barang['jenis'] === 'Found' && App\Core\Auth::check()): ?>
            <div class="action-row">
                <div class="mini-icon">◈</div>
                <div class="action-copy">
                    <b>Klaim diajukan</b>
                    <small><?= count($klaim) ?> klaim menunggu verifikasi</small>
                </div>
                <span class="status <?= $klaim === [] ? '' : 'blue' ?>"><?= count($klaim) ?></span>
            </div>
        <?php endif; ?>

        <div class="action-row">
            <div class="mini-icon">⌖</div>
            <div class="action-copy">
                <b>Posko penemuan</b>
                <small>Gedung Rektorat Lt. 1 · Senin–Jumat 07.00–17.00</small>
            </div>
        </div>

        <?php if (App\Core\Auth::is('Admin', 'Super Admin')): ?>
            <a class="btn outline full" style="margin-top:12px"
               href="<?= url('/admin/barang?q=' . (int) $barang['id_barang']) ?>">Kelola di panel admin</a>
        <?php endif; ?>
    </section>
</div>
