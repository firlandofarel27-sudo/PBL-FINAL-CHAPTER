<?php

declare(strict_types=1);

use App\Core\Auth;

/**
 * Kartu ringkas untuk satu barang atau satu laporan fasilitas.
 *
 * Tombol "Detail" membuka popup berisi keterangan lengkap termasuk foto.
 * Payload-nya dititipkan pada atribut data-detail agar tidak perlu request
 * tambahan; tautan halaman detail tetap disediakan sebagai cadangan bila
 * JavaScript dimatikan.
 *
 * @var array<string, mixed>  $item
 * @var string                $kind  'barang' atau 'fasilitas'
 * @var bool                  $bisaKlaim
 */

$isBarang = ($kind ?? 'barang') === 'barang';

$id      = (int) ($isBarang ? $item['id_barang'] : $item['id_kerusakan']);
$nama    = (string) ($isBarang ? $item['nama_barang'] : $item['nama_fasilitas']);
$jenis   = $isBarang ? (string) $item['jenis'] : 'Rusak';
$status  = (string) $item['status'];
$ikon    = $isBarang ? jenis_icon($jenis) : '⚡';
$ikonKls = $isBarang ? jenis_icon_class($jenis) : 'facility';
$label   = $isBarang ? jenis_label($jenis) : 'Kerusakan fasilitas';
$tempat  = trim((string) ($item['nama_lokasi'] ?? '') . ' · ' . (string) ($item['lokasi_detail'] ?? ''), ' ·');
$tanggal = $isBarang ? $item['tanggal'] : $item['tanggal_lapor'];
$kode    = $isBarang ? 'TP-' . $id : 'FS-' . $id;
$tautan  = url(($isBarang ? '/laporan/barang/' : '/laporan/fasilitas/') . $id);
$bisaKlaim = $bisaKlaim ?? ($isBarang && $jenis === 'Found' && $status === 'Tersedia');

$badgeKls = match (true) {
    !$isBarang        => '',
    $jenis === 'Lost' => 'red',
    default           => 'blue',
};

/* Data untuk popup detail. Nama pelapor hanya untuk pengguna yang masuk. */
$detail = [
    'judul'      => $nama,
    'kode'       => $kode,
    'jenis'      => $label,
    'status'     => status_label($status, $isBarang ? 'status_barang' : 'status_fasilitas'),
    'statusKls'  => status_class($status),
    'tempat'     => $tempat === '' ? 'Lokasi belum diisi' : $tempat,
    'tanggal'    => tanggal_id($tanggal === null ? null : (string) $tanggal),
    'deskripsi'  => (string) ($isBarang ? ($item['deskripsi'] ?? '') : ($item['deskripsi_kerusakan'] ?? '')),
    'foto'       => upload_url($item['foto'] ?? null),
    'pelapor'    => Auth::check() ? (string) ($item['nama_pelapor'] ?? '-') : null,
    'tautan'     => $tautan,
    'klaim'      => $bisaKlaim ? url('/klaim/baru/' . $id) : null,
    'klaimLabel' => Auth::check() ? 'Ajukan klaim' : 'Masuk untuk mengklaim',
];
?>
<article class="item-card">
    <div class="item-photo">
        <?php if (!empty($item['foto'])): ?>
            <img class="item-photo-img" src="<?= e((string) upload_url($item['foto'])) ?>" alt="<?= e($nama) ?>">
        <?php else: ?>
            <div class="item-photo-icon <?= e($ikonKls) ?>"><?= e($ikon) ?></div>
        <?php endif; ?>

        <span class="item-type-badge">
            <span class="status <?= e($badgeKls) ?>"><?= e($label) ?></span>
        </span>

        <?php if ($status !== 'Menunggu Verifikasi' && $status !== 'Dilaporkan'): ?>
            <span class="item-status-badge">
                <span class="status <?= e(status_class($status)) ?>">
                    <?= e(status_label($status, $isBarang ? 'status_barang' : 'status_fasilitas')) ?>
                </span>
            </span>
        <?php endif; ?>
    </div>

    <div class="item-body">
        <h3><?= e($nama) ?></h3>

        <p>
            ⌖ <?= e($tempat === '' ? 'Lokasi belum diisi' : $tempat) ?><br>
            ◷ <?= e(tanggal_id($tanggal === null ? null : (string) $tanggal)) ?>
        </p>

        <div class="item-foot">
            <small><?= e($kode) ?></small>

            <div style="display:flex;gap:6px">
                <button class="btn small outline" type="button"
                        data-detail="<?= e(json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
                    Detail
                </button>

                <?php if ($bisaKlaim): ?>
                    <a class="btn small primary" href="<?= url('/klaim/baru/' . $id) ?>">Klaim</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>
