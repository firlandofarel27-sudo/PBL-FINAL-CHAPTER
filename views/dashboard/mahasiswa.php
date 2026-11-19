<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * Beranda mahasiswa.
 *
 * @var array<string, int|float>       $stat
 * @var array<int, array<string, mixed>> $temuan
 * @var array<int, array<string, mixed>> $laporan
 * @var array<int, array<string, mixed>> $klaim
 */

$user = Auth::user();
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Beranda',
    'sub'   => 'Halo, ' . ($user['nama_lengkap'] ?? 'Mahasiswa') . '! Senang melihat kamu kembali.',
    'aksi'  => '<a class="btn primary" href="' . url('/laporan/baru') . '">＋ Buat laporan</a>',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">▤</div>
        <div class="stat-label">Laporan Saya</div>
        <div class="stat-number"><?= (int) $stat['laporan'] ?></div>
        <div class="stat-note"><?= (int) $stat['diproses'] ?> masih diproses</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">⌕</div>
        <div class="stat-label">Barang Tersedia</div>
        <div class="stat-number"><?= count($temuan) ?></div>
        <div class="stat-note">Siap diklaim di posko</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">◈</div>
        <div class="stat-label">Tiket Klaim</div>
        <div class="stat-number"><?= (int) $stat['klaim'] ?></div>
        <div class="stat-note"><?= (int) $stat['klaim_aktif'] ?> menunggu verifikasi</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Laporan Selesai</div>
        <div class="stat-number"><?= (int) $stat['selesai'] ?></div>
        <div class="stat-note"><span class="positive">Terima kasih sudah peduli!</span></div>
    </article>
</div>

<div class="dashboard-grid">
    <section>
        <div class="section-head">
            <h2>Barang temuan terbaru</h2>
            <a href="<?= url('/katalog') ?>">Lihat semua →</a>
        </div>

        <div class="item-grid">
            <?php if ($temuan === []): ?>
                <div class="empty-state" style="grid-column:1/-1">
                    <div class="empty-state-icon">⌕</div>
                    <h3>Belum ada barang temuan tersedia</h3>
                    <p>Barang temuan akan muncul di sini setelah petugas memverifikasi laporan.</p>
                </div>
            <?php else: ?>
                <?php foreach ($temuan as $item): ?>
                    <?= App\Core\View::capture('partials/item-card', ['item' => $item, 'kind' => 'barang']) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="section-head">
            <h2>Aktivitas laporan</h2>
            <a href="<?= url('/riwayat') ?>">Detail</a>
        </div>

        <?php if ($laporan === []): ?>
            <p class="muted" style="font-size:12px">Belum ada laporan. Mulai dengan membuat laporan pertama.</p>
        <?php else: ?>
            <?php foreach (array_slice($laporan, 0, 3) as $item): ?>
                <div class="action-row">
                    <div class="mini-icon"><?= e(jenis_icon((string) $item['jenis'])) ?></div>
                    <div class="action-copy">
                        <b><?= e($item['nama_barang']) ?></b>
                        <small><?= e(jenis_label((string) $item['jenis'])) ?> · <?= e((string) ($item['nama_lokasi'] ?? '-')) ?></small>
                    </div>
                    <a class="btn small dark" href="<?= url('/laporan/barang/' . (int) $item['id_barang']) ?>">Lihat</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="margin-top:16px;padding:14px;background:#f3f8e9;border-radius:8px">
            <b style="font-size:12px">💡 Tips TITIPIN</b>
            <p class="muted" style="font-size:11px;margin-bottom:0">
                Tambahkan lokasi dan ciri khusus yang jelas agar barang lebih mudah diverifikasi oleh petugas.
            </p>
        </div>
    </section>
</div>
