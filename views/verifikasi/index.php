<?php

declare(strict_types=1);

/**
 * Halaman verifikasi klaim untuk petugas.
 *
 * @var string                            $kode
 * @var array<string, mixed>|null         $klaim
 * @var array<int, array<string, mixed>>  $pending
 */

$status = $klaim === null ? '' : (string) $klaim['status_klaim'];
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Verifikasi klaim',
    'sub'   => 'Periksa kode tiket lalu cocokkan kode redeem sebelum penyerahan barang.',
    'aksi'  => '<a class="btn outline" href="' . url('/admin/klaim') . '">Semua klaim</a>',
]) ?>

<div class="dashboard-grid">
    <section class="panel">
        <div class="section-head">
            <h2>Masukkan kode tiket</h2>
        </div>

        <form method="post" action="<?= url('/verifikasi') ?>">
            <?= csrf_field() ?>

            <div class="field">
                <label for="kode_tiket">Kode tiket klaim</label>
                <input id="kode_tiket" name="kode_tiket" class="code-input"
                       value="<?= e($kode) ?>" placeholder="KLM-XXXXXXXX"
                       autocomplete="off" required>
                <?php if (error_for('kode_tiket')): ?>
                    <p class="field-error"><?= e((string) error_for('kode_tiket')) ?></p>
                <?php endif; ?>
            </div>

            <button class="btn primary full" style="margin-top:14px" type="submit">Cari tiket →</button>
        </form>

        <?php if ($klaim !== null): ?>
            <div style="margin-top:18px;padding:16px;background:#f8faf9;border-radius:8px">
                <div class="section-head" style="margin-bottom:10px">
                    <b style="font-size:13px"><?= e((string) $klaim['nama_barang']) ?></b>
                    <span class="status <?= e(status_class($status)) ?>">
                        <?= e(status_label($status, 'status_klaim')) ?>
                    </span>
                </div>

                <p class="muted" style="font-size:11px;margin:0 0 4px">
                    Pengklaim: <b><?= e((string) $klaim['nama_claimer']) ?></b> ·
                    tiket <?= e((string) $klaim['kode_tiket']) ?>
                </p>
                <p class="muted" style="font-size:11px;margin:0 0 4px">
                    Status barang: <b><?= e((string) $klaim['status_barang']) ?></b> ·
                    berlaku sampai <?= e(tanggal_jam_id((string) $klaim['redeem_expires_at'])) ?>
                    (<?= e(sisa_waktu((string) $klaim['redeem_expires_at'])) ?>)
                </p>

                <?php if (!empty($klaim['ciri_khusus'])): ?>
                    <div style="margin-top:10px;padding:10px 12px;background:#fff;border:1px solid var(--line);border-radius:8px">
                        <div class="meta-label">CIRI KHUSUS YANG DISEBUT PENGKLAIM</div>
                        <p style="font-size:11px;margin:0;line-height:1.6">
                            <?= nl2br(e((string) $klaim['ciri_khusus'])) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($klaim['bukti_kepemilikan'])): ?>
                    <a class="btn outline small" style="margin-top:8px"
                       href="<?= e((string) upload_url($klaim['bukti_kepemilikan'])) ?>"
                       target="_blank" rel="noopener">Lihat bukti kepemilikan</a>
                <?php endif; ?>

                <?php if ($status === 'Diajukan'): ?>
                    <form method="post" action="<?= url('/klaim/' . (int) $klaim['id_klaim'] . '/redeem') ?>"
                          style="margin-top:14px">
                        <?= csrf_field() ?>

                        <div class="field">
                            <?php $panjangKode = (int) config('app.redeem.length', 5); ?>
                            <label for="redeem_code">Kode redeem <?= $panjangKode ?> karakter dari pengklaim *</label>
                            <input id="redeem_code" name="redeem_code" class="code-input"
                                   maxlength="<?= $panjangKode ?>" minlength="<?= $panjangKode ?>" required
                                   placeholder="<?= e(str_repeat('•', $panjangKode)) ?>" autocomplete="off"
                                   style="text-transform:uppercase;letter-spacing:6px">
                            <?php if (error_for('redeem_code')): ?>
                                <p class="field-error"><?= e((string) error_for('redeem_code')) ?></p>
                            <?php endif; ?>
                        </div>

                        <button class="btn primary full" style="margin-top:12px" type="submit">
                            Konfirmasi serah terima →
                        </button>
                    </form>
                <?php elseif ($status === 'Ditolak'): ?>
                    <div class="alert alert-error" style="margin-top:12px">
                        <?= e((string) ($klaim['alasan_penolakan'] ?? 'Klaim ditolak.')) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success" style="margin-top:12px">
                        Klaim sudah disetujui pada <?= e(tanggal_jam_id((string) $klaim['redeem_used_at'])) ?>.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2 style="font-size:15px;margin-top:0">Panduan verifikasi</h2>

        <div class="action-row">
            <div class="mini-icon">1</div>
            <div class="action-copy">
                <b>Cocokkan kode tiket</b>
                <small>Pastikan kode sesuai tiket klaim yang ditunjukkan pengguna.</small>
            </div>
        </div>

        <div class="action-row">
            <div class="mini-icon">2</div>
            <div class="action-copy">
                <b>Periksa identitas</b>
                <small>Cocokkan nama, KTM, dan bukti kepemilikan.</small>
            </div>
        </div>

        <div class="action-row">
            <div class="mini-icon">3</div>
            <div class="action-copy">
                <b>Konfirmasi serah terima</b>
                <small>Masukkan kode redeem 5 karakter; barang otomatis ditandai Selesai.</small>
            </div>
        </div>
    </section>
</div>

<section class="panel" style="margin-top:16px">
    <div class="section-head">
        <h2>Klaim menunggu verifikasi</h2>
        <span class="status blue"><?= count($pending) ?> tiket</span>
    </div>

    <?php if ($pending === []): ?>
        <p class="muted" style="font-size:12px">Tidak ada klaim yang menunggu verifikasi.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>KODE TIKET</th>
                    <th>BARANG</th>
                    <th>PENGKLAIM</th>
                    <th>BERLAKU SAMPAI</th>
                    <th>SISA</th>
                    <th>AKSI</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pending as $item): ?>
                    <tr>
                        <td><b><?= e((string) $item['kode_tiket']) ?></b></td>
                        <td><?= e((string) $item['nama_barang']) ?></td>
                        <td><?= e((string) $item['nama_claimer']) ?></td>
                        <td><?= e(tanggal_jam_id((string) $item['redeem_expires_at'])) ?></td>
                        <td><?= e(sisa_waktu((string) $item['redeem_expires_at'])) ?></td>
                        <td>
                            <a class="btn small outline"
                               href="<?= url('/verifikasi?kode=' . rawurlencode((string) $item['kode_tiket'])) ?>">
                                Verifikasi
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
