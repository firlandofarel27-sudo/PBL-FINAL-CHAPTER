<?php

declare(strict_types=1);

/**
 * Detail laporan kerusakan fasilitas.
 *
 * @var array<string, mixed> $fasilitas
 */

$tempat = trim((string) ($fasilitas['nama_lokasi'] ?? '') . ' · ' . (string) ($fasilitas['lokasi_detail'] ?? ''), ' ·');
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => (string) $fasilitas['nama_fasilitas'],
    'sub'   => 'FS-' . (int) $fasilitas['id_kerusakan'] . ' · ' . tanggal_id((string) $fasilitas['tanggal_lapor']),
    'aksi'  => '<a class="btn outline" href="' . url('/riwayat') . '">← Kembali</a>',
]) ?>

<div class="dashboard-grid">
    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Detail kerusakan</h2>
            <span class="status <?= e(status_class((string) $fasilitas['status'])) ?>">
                <?= e(status_label((string) $fasilitas['status'], 'status_fasilitas')) ?>
            </span>
        </div>

        <?php if (!empty($fasilitas['foto'])): ?>
            <img class="detail-photo" src="<?= e((string) upload_url($fasilitas['foto'])) ?>"
                 alt="Foto <?= e($fasilitas['nama_fasilitas']) ?>">
        <?php endif; ?>

        <p><b>Lokasi:</b> <?= e($tempat === '' ? 'Belum diisi' : $tempat) ?></p>
        <p><b>Waktu kejadian:</b> <?= e((string) ($fasilitas['waktu_kejadian'] ?: 'Tidak diketahui')) ?></p>
        <p><b>Kategori:</b> <?= e((string) ($fasilitas['nama_kategori'] ?? 'Tanpa kategori')) ?></p>

        <p style="font-size:12px;background:#f8faf9;border-radius:8px;padding:12px;line-height:1.6">
            <?= nl2br(e((string) ($fasilitas['deskripsi_kerusakan'] ?? '-'))) ?>
        </p>

        <p style="font-size:11px;color:var(--muted)">
            <b>Dilaporkan oleh:</b>
            <?= App\Core\Auth::check()
                ? e((string) ($fasilitas['nama_pelapor'] ?? '-'))
                : 'disembunyikan untuk pengunjung' ?>
        </p>
    </section>

    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Penanganan</h2>
        </div>

        <div class="action-row">
            <div class="mini-icon">⚡</div>
            <div class="action-copy">
                <b>Status perbaikan</b>
                <small><?= e(status_label((string) $fasilitas['status'], 'status_fasilitas')) ?></small>
            </div>
        </div>

        <?php if (!empty($fasilitas['catatan_teknisi'])): ?>
            <div style="padding:12px;background:#f3f8e9;border-radius:8px">
                <b style="font-size:12px">Catatan teknisi</b>
                <p class="muted" style="font-size:11px;margin:6px 0 0">
                    <?= nl2br(e((string) $fasilitas['catatan_teknisi'])) ?>
                </p>
            </div>
        <?php else: ?>
            <p class="muted" style="font-size:12px">Belum ada catatan dari teknisi.</p>
        <?php endif; ?>

        <?php if (App\Core\Auth::is('Admin', 'Super Admin')): ?>
            <a class="btn outline full" style="margin-top:12px"
               href="<?= url('/admin/fasilitas?q=' . (int) $fasilitas['id_kerusakan']) ?>">Kelola di panel admin</a>
        <?php endif; ?>
    </section>
</div>
