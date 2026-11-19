<?php

declare(strict_types=1);

/**
 * Formulir pengajuan klaim barang temuan.
 *
 * @var array<string, mixed> $barang
 */
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Ajukan klaim',
    'sub'   => (string) $barang['nama_barang'] . ' · TP-' . (int) $barang['id_barang'],
    'aksi'  => '<a class="btn outline" href="' . url('/katalog') . '">← Katalog</a>',
]) ?>

<div class="dashboard-grid">
    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Barang yang diklaim</h2>
        </div>

        <?php if (!empty($barang['foto'])): ?>
            <img class="detail-photo" src="<?= e((string) upload_url($barang['foto'])) ?>"
                 alt="Foto <?= e($barang['nama_barang']) ?>">
        <?php endif; ?>

        <p><b>Lokasi ditemukan:</b> <?= e((string) ($barang['nama_lokasi'] ?? '-')) ?></p>
        <p><b>Dilaporkan:</b> <?= e(tanggal_id((string) $barang['tanggal'])) ?></p>
        <p class="muted" style="font-size:11px">
            <?= nl2br(e((string) ($barang['deskripsi'] ?? '-'))) ?>
        </p>
    </section>

    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px">Bukti kepemilikan</h2>
            <span class="status red">Wajib</span>
        </div>

        <form method="post" action="<?= url('/klaim') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= (int) $barang['id_barang'] ?>">

            <div class="field" style="margin-bottom:14px">
                <label for="ciri_khusus">Ciri khusus barang *</label>
                <textarea id="ciri_khusus" name="ciri_khusus" required
                          placeholder="Contoh: ada goresan di sudut kanan, sticker biru, isinya KTM atas nama saya dan dua kartu ATM..."><?= e(old('ciri_khusus')) ?></textarea>
                <?php if (error_for('ciri_khusus')): ?>
                    <p class="field-error"><?= e((string) error_for('ciri_khusus')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field" style="margin-bottom:14px">
                <label for="bukti_kepemilikan">Foto bukti kepemilikan *</label>
                <p class="muted" style="font-size:10px;margin:0 0 8px">
                    Nota pembelian, foto lama bersama barang, atau foto isi barang. PNG/JPG maks 5 MB.
                </p>
                <input type="file" accept="image/*" id="bukti_kepemilikan" name="bukti_kepemilikan" required>
            </div>

            <div style="display:flex;gap:10px">
                <a class="btn outline" href="<?= url('/katalog') ?>">Batal</a>
                <button class="btn primary" type="submit">Kirim pengajuan klaim →</button>
            </div>
        </form>
    </section>
</div>
