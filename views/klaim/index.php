<?php

declare(strict_types=1);

/**
 * Daftar tiket klaim milik mahasiswa.
 *
 * @var array<int, array<string, mixed>>  $klaim
 * @var array<string, mixed>|null         $kodeBaru
 */
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Tiket Klaim',
    'sub'   => 'Lihat tiket dan kode klaim barang kamu.',
]) ?>

<?php if (is_array($kodeBaru)): ?>
    <section class="panel" style="margin-bottom:16px">
        <div class="section-head">
            <h2 style="font-size:15px">Kode redeem baru</h2>
            <span class="status green">Sekali tampil</span>
        </div>

        <div class="claim-result-box">
            <div style="font-size:11px;color:var(--muted)">Kode redeem untuk petugas posko</div>
            <div class="claim-code-display"><?= e((string) $kodeBaru['kode']) ?></div>
            <small style="color:var(--muted)">
                Kode tiket <?= e((string) $kodeBaru['kode_tiket']) ?> ·
                berlaku <?= (int) config('app.redeem.ttl_hours') ?> jam sejak klaim diajukan
            </small>
        </div>

        <p class="muted" style="font-size:11px;margin:0">
            Kode ini tidak bisa ditampilkan lagi karena server hanya menyimpan hash-nya.
            Salin sekarang dan tunjukkan ke petugas saat serah terima.
        </p>
    </section>
<?php endif; ?>

<?php if ($klaim === []): ?>
    <div class="empty-state">
        <div class="empty-state-icon">◈</div>
        <h3>Belum ada tiket klaim</h3>
        <p>Klaim barang temuan di katalog, lalu tiket akan muncul di halaman ini.</p>
        <a class="btn primary" href="<?= url('/katalog') ?>">Jelajahi katalog</a>
    </div>
<?php else: ?>
    <?php foreach ($klaim as $item): ?>
        <?php
        $status  = (string) $item['status_klaim'];
        $selesai = $status === 'Disetujui';
        $ditolak = $status === 'Ditolak';
        ?>
        <section class="panel" style="max-width:720px;margin-bottom:16px">
            <div class="section-head">
                <h2 style="font-size:15px"><?= e($item['nama_barang']) ?></h2>
                <span class="status <?= e(status_class($status)) ?>">
                    <?= e(status_label($status, 'status_klaim')) ?>
                </span>
            </div>

            <p class="muted" style="font-size:11px;margin:0 0 14px">
                Tiket <b><?= e((string) $item['kode_tiket']) ?></b> ·
                <?= e((string) ($item['nama_lokasi'] ?? '-')) ?> ·
                kode berlaku sampai <?= e(tanggal_jam_id((string) $item['redeem_expires_at'])) ?>
            </p>

            <div class="timeline">
                <div class="timeline-step active">✓<br>Laporan dibuat</div>
                <div class="timeline-step active">✓<br>Klaim diajukan</div>
                <div class="timeline-step <?= $selesai ? 'active' : '' ?>">
                    <?= $selesai ? '✓' : '3' ?><br>Serah terima
                </div>
            </div>

            <?php if ($ditolak): ?>
                <div class="alert alert-error" style="margin-top:14px">
                    <?= e((string) ($item['alasan_penolakan'] ?? 'Klaim ditolak.')) ?>
                </div>
            <?php elseif (!$selesai): ?>
                <p class="muted" style="font-size:11px;margin:16px 0 6px">
                    Tunjukkan kode redeem ke petugas posko sebelum masa berlaku habis:
                    <b><?= e(sisa_waktu((string) $item['redeem_expires_at'])) ?></b>
                </p>
            <?php else: ?>
                <p class="muted" style="font-size:11px;margin:16px 0 6px">
                    Klaim disetujui <?= e(tanggal_jam_id((string) $item['redeem_used_at'])) ?>
                    oleh <?= e((string) ($item['nama_admin'] ?? 'petugas')) ?>.
                </p>
            <?php endif; ?>

            <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap">
                <a class="btn outline small" href="<?= url('/klaim/' . (int) $item['id_klaim']) ?>">Lihat tiket</a>
                <a class="btn outline small" href="<?= url('/laporan/barang/' . (int) $item['item_id']) ?>">Detail barang</a>
            </div>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
