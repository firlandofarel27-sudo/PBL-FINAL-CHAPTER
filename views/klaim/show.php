<?php

declare(strict_types=1);

/**
 * Detail satu tiket klaim.
 *
 * @var array<string, mixed>       $klaim
 * @var array<string, mixed>|null  $kodeBaru
 */

$status  = (string) $klaim['status_klaim'];
$selesai = $status === 'Disetujui';
$ditolak = $status === 'Ditolak';
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Tiket ' . (string) $klaim['kode_tiket'],
    'sub'   => (string) $klaim['nama_barang'] . ' · diajukan oleh ' . (string) $klaim['nama_claimer'],
    'aksi'  => '<a class="btn outline" href="' . url('/klaim') . '">← Semua tiket</a>',
]) ?>

<?php if (is_array($kodeBaru) && (int) $kodeBaru['id_klaim'] === (int) $klaim['id_klaim']): ?>
    <section class="panel" style="margin-bottom:16px">
        <div class="section-head">
            <h2 style="font-size:15px">Kode redeem</h2>
            <span class="status green">Sekali tampil</span>
        </div>

        <div class="claim-result-box">
            <div style="font-size:11px;color:var(--muted)">Tunjukkan kode ini ke petugas posko</div>
            <div class="claim-code-display"><?= e((string) $kodeBaru['kode']) ?></div>
            <small style="color:var(--muted)">
                Berlaku <?= (int) config('app.redeem.ttl_hours') ?> jam sejak klaim diajukan
            </small>
        </div>

        <p class="muted" style="font-size:11px;margin:0">
            Kode tidak dapat ditampilkan ulang karena hanya hash-nya yang disimpan.
        </p>
    </section>
<?php endif; ?>

<div class="dashboard-grid">
    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Progres klaim</h2>
            <span class="status <?= e(status_class($status)) ?>">
                <?= e(status_label($status, 'status_klaim')) ?>
            </span>
        </div>

        <div class="timeline">
            <div class="timeline-step active">✓<br>Laporan dibuat</div>
            <div class="timeline-step active">✓<br>Klaim diajukan</div>
            <div class="timeline-step <?= $selesai ? 'active' : '' ?>">
                <?= $selesai ? '✓' : '3' ?><br>Serah terima
            </div>
        </div>

        <?php if ($ditolak): ?>
            <div class="alert alert-error" style="margin-top:16px">
                <?= e((string) ($klaim['alasan_penolakan'] ?? 'Klaim ditolak.')) ?>
            </div>
        <?php elseif ($selesai): ?>
            <p class="muted" style="font-size:11px;margin-top:16px">
                Disetujui <?= e(tanggal_jam_id((string) $klaim['redeem_used_at'])) ?>
                oleh <?= e((string) ($klaim['nama_admin'] ?? 'petugas')) ?>.
                Barang sudah diserahterimakan.
            </p>
        <?php else: ?>
            <p class="muted" style="font-size:11px;margin-top:16px">
                Menunggu serah terima di posko. Sisa masa berlaku kode:
                <b><?= e(sisa_waktu((string) $klaim['redeem_expires_at'])) ?></b>.
            </p>
        <?php endif; ?>
    </section>

    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Data klaim</h2>
        </div>

        <div class="action-row">
            <div class="mini-icon">◈</div>
            <div class="action-copy">
                <b><?= e((string) $klaim['kode_tiket']) ?></b>
                <small>Kode tiket</small>
            </div>
        </div>

        <div class="action-row">
            <div class="mini-icon">▤</div>
            <div class="action-copy">
                <b><?= e((string) $klaim['nama_barang']) ?></b>
                <small><?= e(jenis_label((string) $klaim['jenis'])) ?> · status barang: <?= e((string) $klaim['status_barang']) ?></small>
            </div>
        </div>

        <div class="action-row">
            <div class="mini-icon">⌖</div>
            <div class="action-copy">
                <b>Posko satpam</b>
                <small>Gedung Rektorat Lt. 1 · Senin–Jumat 07.00–17.00</small>
            </div>
        </div>

        <?php if (!empty($klaim['bukti_kepemilikan'])): ?>
            <a class="btn outline full" style="margin-top:10px"
               href="<?= e((string) upload_url($klaim['bukti_kepemilikan'])) ?>" target="_blank" rel="noopener">
                Lihat bukti kepemilikan
            </a>
        <?php endif; ?>

        <?php if (App\Core\Auth::is('Admin', 'Super Admin') && $status === 'Diajukan'): ?>
            <a class="btn primary full" style="margin-top:10px"
               href="<?= url('/verifikasi?kode=' . rawurlencode((string) $klaim['kode_tiket'])) ?>">
                Verifikasi klaim ini
            </a>
        <?php endif; ?>
    </section>
</div>
