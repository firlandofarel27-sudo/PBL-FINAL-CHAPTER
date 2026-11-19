<?php

declare(strict_types=1);

/**
 * Manajemen seluruh klaim.
 *
 * @var string                            $keyword
 * @var string                            $status
 * @var array<int, array<string, mixed>>  $items
 * @var array<string, int>                $stat
 */
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Manajemen Klaim',
    'sub'   => 'Tinjau tiket klaim, tolak dengan alasan, atau hapus tiket.',
    'aksi'  => '<a class="btn primary" href="' . url('/verifikasi') . '">Verifikasi kode →</a>',
]) ?>

<div class="stats">
    <article class="stat-card">
        <div class="stat-icon">◈</div>
        <div class="stat-label">Total klaim</div>
        <div class="stat-number"><?= (int) $stat['total'] ?></div>
        <div class="stat-note">Seluruh tiket klaim</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">▣</div>
        <div class="stat-label">Diajukan</div>
        <div class="stat-number"><?= (int) $stat['diajukan'] ?></div>
        <div class="stat-note">Menunggu serah terima</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-label">Disetujui</div>
        <div class="stat-number"><?= (int) $stat['disetujui'] ?></div>
        <div class="stat-note">Barang sudah diserahkan</div>
    </article>

    <article class="stat-card">
        <div class="stat-icon">✕</div>
        <div class="stat-label">Ditolak</div>
        <div class="stat-number"><?= (int) $stat['ditolak'] ?></div>
        <div class="stat-note">Termasuk kode kedaluwarsa</div>
    </article>
</div>

<form class="toolbar" method="get" action="<?= url('/admin/klaim') ?>">
    <div class="searchbox">
        ⌕ <input type="search" name="q" value="<?= e($keyword) ?>" placeholder="Cari kode tiket, barang, atau pengklaim...">
    </div>

    <select class="select" name="status" onchange="this.form.submit()">
        <option value="">Semua status</option>
        <?php foreach (['Diajukan', 'Disetujui', 'Ditolak'] as $opsi): ?>
            <option value="<?= e($opsi) ?>" <?= $status === $opsi ? 'selected' : '' ?>><?= e($opsi) ?></option>
        <?php endforeach; ?>
    </select>

    <button class="btn outline" type="submit">Cari</button>
    <a class="btn outline" href="<?= url('/admin/klaim') ?>">Reset</a>
</form>

<section class="panel">
    <?php if ($items === []): ?>
        <p class="muted" style="font-size:12px">Tidak ada klaim yang cocok.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>KODE TIKET</th>
                    <th>BARANG</th>
                    <th>PENGKLAIM</th>
                    <th>STATUS</th>
                    <th>BERLAKU</th>
                    <th>AKSI</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $id = (int) $item['id_klaim']; ?>
                    <tr>
                        <td><b><?= e((string) $item['kode_tiket']) ?></b></td>
                        <td><?= e((string) $item['nama_barang']) ?></td>
                        <td><?= e((string) $item['nama_claimer']) ?></td>
                        <td>
                            <span class="status <?= e(status_class((string) $item['status_klaim'])) ?>">
                                <?= e(status_label((string) $item['status_klaim'], 'status_klaim')) ?>
                            </span>
                            <?php if (!empty($item['alasan_penolakan'])): ?>
                                <br><small class="muted"><?= e((string) $item['alasan_penolakan']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e(sisa_waktu((string) $item['redeem_expires_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <?php if ($item['status_klaim'] === 'Diajukan'): ?>
                                    <form method="post" action="<?= url('/admin/klaim/' . $id . '/tolak') ?>" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input class="input-sm" name="alasan_penolakan" placeholder="Alasan penolakan" required>
                                        <button class="btn small dark" type="submit">Tolak</button>
                                    </form>

                                    <a class="btn small outline" href="<?= url('/verifikasi?kode=' . rawurlencode((string) $item['kode_tiket'])) ?>">
                                        Verifikasi
                                    </a>
                                <?php endif; ?>

                                <form method="post" action="<?= url('/admin/klaim/' . $id . '/hapus') ?>" class="inline-form"
                                      onsubmit="return confirm('Hapus tiket klaim ini?')">
                                    <?= csrf_field() ?>
                                    <button class="btn small outline" type="submit">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
