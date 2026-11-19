<?php

declare(strict_types=1);

/**
 * Pengaturan sistem.
 *
 * @var array<string, string> $pengaturan
 */
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Pengaturan Sistem',
    'sub'   => 'Konfigurasi aturan dan layanan TITIPIN.',
]) ?>

<section class="panel" style="max-width:820px">
    <form method="post" action="<?= url('/admin/pengaturan') ?>" class="form-grid">
        <?= csrf_field() ?>

        <div class="field">
            <label for="nama_aplikasi">Nama aplikasi</label>
            <input id="nama_aplikasi" name="nama_aplikasi"
                   value="<?= e($pengaturan['nama_aplikasi']) ?>" required>
            <?php if (error_for('nama_aplikasi')): ?>
                <p class="field-error"><?= e((string) error_for('nama_aplikasi')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="retensi_hari">Durasi penyimpanan laporan (hari)</label>
            <input type="number" id="retensi_hari" name="retensi_hari" min="1"
                   value="<?= e($pengaturan['retensi_hari']) ?>" required>
            <?php if (error_for('retensi_hari')): ?>
                <p class="field-error"><?= e((string) error_for('retensi_hari')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="target_selesai">Target penyelesaian (%)</label>
            <input type="number" id="target_selesai" name="target_selesai" min="1" max="100"
                   value="<?= e($pengaturan['target_selesai']) ?>" required>
            <?php if (error_for('target_selesai')): ?>
                <p class="field-error"><?= e((string) error_for('target_selesai')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="notifikasi_email">Notifikasi email</label>
            <select id="notifikasi_email" name="notifikasi_email">
                <?php foreach (['Aktif', 'Nonaktif'] as $opsi): ?>
                    <option value="<?= e($opsi) ?>" <?= $pengaturan['notifikasi_email'] === $opsi ? 'selected' : '' ?>>
                        <?= e($opsi) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field full-width">
            <button class="btn primary" type="submit">Simpan pengaturan</button>
        </div>
    </form>
</section>

<section class="panel" style="max-width:820px;margin-top:16px">
    <div class="section-head">
        <h2 style="font-size:15px">Informasi sistem</h2>
    </div>

    <div class="action-row">
        <div class="mini-icon">▤</div>
        <div class="action-copy">
            <b>Basis data</b>
            <small>PostgreSQL · database <?= e((string) env('DB_NAME', 'titipin')) ?></small>
        </div>
        <span class="status green">Terhubung</span>
    </div>

    <div class="action-row">
        <div class="mini-icon">⌖</div>
        <div class="action-copy">
            <b>Kode redeem</b>
            <small><?= (int) config('app.redeem.length') ?> karakter · berlaku <?= (int) config('app.redeem.ttl_hours') ?> jam</small>
        </div>
    </div>

    <div class="action-row">
        <div class="mini-icon">＋</div>
        <div class="action-copy">
            <b>Batas unggah</b>
            <small>Barang 2 MB · Fasilitas 2 MB · Bukti klaim 5 MB</small>
        </div>
    </div>
</section>
