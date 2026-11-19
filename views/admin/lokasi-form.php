<?php

declare(strict_types=1);

/**
 * Formulir tambah dan edit lokasi.
 *
 * Satu view melayani dua mode:
 * - `tambah` → POST /admin/lokasi
 * - `ubah`   → POST /admin/lokasi/{id}
 *
 * @var string                        $mode
 * @var array<string, mixed>|null     $lokasi
 * @var array<int, string>            $floors
 * @var int                           $dipakai
 */

$ubah   = $mode === 'ubah';
$dipakai = $dipakai ?? 0;

$aksi = $ubah
    ? url('/admin/lokasi/' . (int) $lokasi['id_lokasi'])
    : url('/admin/lokasi');

$nilai = static fn (string $kunci): string => $ubah
    ? old($kunci, (string) ($lokasi[$kunci] ?? ''))
    : old($kunci);
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => $ubah ? 'Edit lokasi' : 'Tambah lokasi',
    'sub'   => $ubah
        ? (string) $lokasi['nama_lokasi'] . ' · ' . (string) ($lokasi['gedung'] ?: 'Tanpa lantai')
        : 'Tambahkan lantai atau ruangan baru untuk formulir laporan.',
    'aksi'  => '<a class="btn outline" href="' . url('/admin/lokasi') . '">← Daftar lokasi</a>',
]) ?>

<div class="profil-grid">
    <section class="panel">
        <div class="section-head">
            <h2 style="font-size:15px"><?= $ubah ? 'Data lokasi' : 'Data lokasi baru' ?></h2>
            <span class="status <?= $ubah ? 'blue' : 'green' ?>">
                <?= $ubah ? 'Mode edit' : 'Lokasi baru' ?>
            </span>
        </div>

        <form method="post" action="<?= $aksi ?>" class="form-grid">
            <?= csrf_field() ?>

            <div class="field">
                <label for="gedung">Gedung / lantai</label>
                <input id="gedung" name="gedung" value="<?= e($nilai('gedung')) ?>"
                       placeholder="Contoh: Lantai 6 Timur" list="daftarLantai">
                <datalist id="daftarLantai">
                    <?php foreach ($floors as $lantai): ?>
                        <option value="<?= e($lantai) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <p class="field-hint">Pilih dari daftar yang ada atau tulis lantai baru.</p>
                <?php if (error_for('gedung')): ?>
                    <p class="field-error"><?= e((string) error_for('gedung')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="nama_lokasi">Nama lokasi / unit *</label>
                <input id="nama_lokasi" name="nama_lokasi" value="<?= e($nilai('nama_lokasi')) ?>"
                       placeholder="Contoh: Ruang Baca" required>
                <?php if (error_for('nama_lokasi')): ?>
                    <p class="field-error"><?= e((string) error_for('nama_lokasi')) ?></p>
                <?php endif; ?>
            </div>

            <div class="field full-width profil-aksi">
                <button class="btn primary" type="submit">
                    <?= $ubah ? 'Simpan perubahan' : '＋ Tambah lokasi' ?>
                </button>
                <a class="btn outline" href="<?= url('/admin/lokasi') ?>">Batal</a>
            </div>
        </form>
    </section>

    <aside class="profil-side">
        <section class="panel">
            <div class="section-head">
                <h2 style="font-size:15px">Ringkasan</h2>
            </div>

            <?php if ($ubah): ?>
                <div class="profil-meta">
                    <div><span>ID</span><b>#<?= (int) $lokasi['id_lokasi'] ?></b></div>
                    <div><span>Lantai</span><b><?= e((string) ($lokasi['gedung'] ?: '-')) ?></b></div>
                    <div><span>Ruangan</span><b><?= e((string) $lokasi['nama_lokasi']) ?></b></div>
                    <div><span>Dipakai</span><b><?= $dipakai ?> laporan</b></div>
                </div>

                <?php if ($dipakai > 0): ?>
                    <p class="field-hint" style="margin-top:12px">
                        Lokasi ini sudah dipakai <?= $dipakai ?> laporan. Nama dan lantainya masih
                        bisa diubah, tetapi tidak bisa dihapus.
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p class="muted" style="font-size:11px;line-height:1.7;margin:0">
                    Satu baris mewakili satu ruangan. Kolom <b>Gedung / lantai</b> menentukan
                    posisi ruangan pada dropdown bertingkat di formulir laporan.
                </p>
            <?php endif; ?>
        </section>

        <?php if ($ubah && $dipakai === 0): ?>
            <section class="panel">
                <div class="section-head">
                    <h2 style="font-size:15px">Tindakan</h2>
                </div>

                <form method="post" action="<?= url('/admin/lokasi/' . (int) $lokasi['id_lokasi'] . '/hapus') ?>"
                      onsubmit="return confirm('Nonaktifkan lokasi ini? Lokasi tidak akan muncul lagi di formulir laporan.')">
                    <?= csrf_field() ?>
                    <button class="btn outline full" type="submit">Nonaktifkan lokasi</button>
                </form>
            </section>
        <?php endif; ?>
    </aside>
</div>
