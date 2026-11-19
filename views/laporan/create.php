<?php

declare(strict_types=1);

/**
 * Formulir laporan terpadu: barang hilang, barang temuan, kerusakan fasilitas.
 *
 * Lokasi dipilih bertingkat: lantai lebih dulu, lalu ruangan pada lantai
 * tersebut. Pilihan ruangan disaring oleh assets/js/app.js.
 *
 * @var array<string, string>                            $jenisOpsi
 * @var array<int, array<string, mixed>>                 $kategoris
 * @var array<string, array<int, array<string, mixed>>>  $lokasis
 */

$waktu    = config('app.waktu_kejadian', []);
$jenisLama = old('jenis', $jenisAwal ?? 'Lost');
$lokasiLama = old('lokasi_id');

/* Lantai dari ruangan yang sudah dipilih, dipakai saat validasi gagal. */
$lantaiLama = '';

foreach ($lokasis as $lantai => $daftar) {
    foreach ($daftar as $lokasi) {
        if ((string) $lokasi['id_lokasi'] === $lokasiLama) {
            $lantaiLama = (string) $lantai;
        }
    }
}
?>
<?= App\Core\View::capture('partials/page-heading', [
    'judul' => 'Buat laporan baru',
    'sub'   => 'Ceritakan barang hilang, barang temuan, atau kerusakan fasilitas.',
]) ?>

<section class="form-panel" style="padding:22px">
    <div class="section-head">
        <h2>Informasi laporan</h2>
        <span class="status blue">Langkah 1 dari 1</span>
    </div>

    <form id="reportForm" class="form-grid" method="post"
          action="<?= url('/laporan') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="field">
            <label for="jenis">Jenis laporan *</label>
            <select id="jenis" name="jenis" required>
                <?php foreach ($jenisOpsi as $nilai => $label): ?>
                    <option value="<?= e($nilai) ?>" <?= $jenisLama === $nilai ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (error_for('jenis')): ?>
                <p class="field-error"><?= e((string) error_for('jenis')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="nama" id="namaLabel">
                <?= $jenisLama === 'Rusak' ? 'Nama fasilitas *' : 'Nama barang *' ?>
            </label>
            <input id="nama" name="nama" value="<?= e(old('nama')) ?>"
                   placeholder="<?= $jenisLama === 'Rusak' ? 'Contoh: AC Lab 5A' : 'Contoh: Dompet kulit hitam' ?>" required>
            <?php if (error_for('nama')): ?>
                <p class="field-error"><?= e((string) error_for('nama')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id">
                <option value="">Pilih kategori</option>
                <?php foreach ($kategoris as $kategori): ?>
                    <option value="<?= (int) $kategori['id_kategori'] ?>"
                        <?= old('kategori_id') === (string) $kategori['id_kategori'] ? 'selected' : '' ?>>
                        <?= e($kategori['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="lantai">Lantai *</label>
            <select id="lantai" name="lantai" data-ruangan-target="lokasi_id" required>
                <option value="">Pilih lantai</option>
                <?php foreach (array_keys($lokasis) as $lantai): ?>
                    <option value="<?= e((string) $lantai) ?>" <?= $lantaiLama === (string) $lantai ? 'selected' : '' ?>>
                        <?= e((string) $lantai) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="lokasi_id">Ruangan *</label>
            <select id="lokasi_id" name="lokasi_id" required>
                <option value="">Pilih ruangan</option>
                <?php foreach ($lokasis as $lantai => $daftar): ?>
                    <optgroup label="<?= e((string) $lantai) ?>" data-lantai="<?= e((string) $lantai) ?>">
                        <?php foreach ($daftar as $lokasi): ?>
                            <option value="<?= (int) $lokasi['id_lokasi'] ?>"
                                <?= $lokasiLama === (string) $lokasi['id_lokasi'] ? 'selected' : '' ?>>
                                <?= e($lokasi['nama_lokasi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <p class="field-hint" id="ruanganHint">
                Pilih lantai untuk mempersempit daftar ruangan.
            </p>
            <?php if (error_for('lokasi_id')): ?>
                <p class="field-error"><?= e((string) error_for('lokasi_id')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="lokasi_detail">Detail lokasi</label>
            <input id="lokasi_detail" name="lokasi_detail" value="<?= e(old('lokasi_detail')) ?>"
                   placeholder="Contoh: meja dekat jendela, koridor sayap timur">
        </div>

        <div class="field">
            <label for="tanggal">Tanggal kejadian *</label>
            <input type="date" id="tanggal" name="tanggal"
                   value="<?= e(old('tanggal', date('Y-m-d'))) ?>" required>
            <?php if (error_for('tanggal')): ?>
                <p class="field-error"><?= e((string) error_for('tanggal')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="waktu_kejadian">Perkiraan waktu</label>
            <select id="waktu_kejadian" name="waktu_kejadian">
                <option value="">Tidak tahu / tidak ingat</option>
                <?php foreach ($waktu as $opsi): ?>
                    <option value="<?= e($opsi) ?>" <?= old('waktu_kejadian') === $opsi ? 'selected' : '' ?>>
                        <?= e($opsi) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field full-width">
            <label for="deskripsi" id="deskripsiLabel">Deskripsi detail *</label>
            <textarea id="deskripsi" name="deskripsi" required
                      placeholder="Jelaskan ciri-ciri barang (warna, merek, isi), kondisi kerusakan, atau informasi tambahan yang membantu petugas..."><?= e(old('deskripsi')) ?></textarea>
            <?php if (error_for('deskripsi')): ?>
                <p class="field-error"><?= e((string) error_for('deskripsi')) ?></p>
            <?php endif; ?>
        </div>

        <div class="field full-width">
            <label for="foto">Foto pendukung (opsional)</label>
            <div class="upload" id="uploadZone">
                <label class="upload-trigger" for="foto">
                    <div class="upload-icon">＋</div>
                    <b style="font-size:12px">Pilih atau seret foto ke sini</b>
                    <small>PNG, JPG, atau JPEG · Maks 2 MB</small>
                </label>
                <input type="file" accept="image/*" id="foto" name="foto">
                <div id="photoPreviewContainer"></div>
            </div>
        </div>

        <div class="field full-width">
            <label class="check">
                <input type="checkbox" required>
                Saya menyatakan bahwa informasi yang diberikan adalah benar dan dapat dipertanggungjawabkan.
            </label>
        </div>

        <div class="field full-width" style="display:flex;justify-content:flex-end;gap:10px">
            <a class="btn outline" href="<?= url('/beranda') ?>">Batal</a>
            <button class="btn primary" type="submit">Kirim laporan →</button>
        </div>
    </form>
</section>
