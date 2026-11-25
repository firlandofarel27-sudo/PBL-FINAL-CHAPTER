<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Session;

/**
 * Membaca variabel dari berkas .env.
 *
 * Nilai di-parse dengan INI_SCANNER_TYPED agar angka dan boolean langsung
 * bertipe benar. Komentar bergaya `#` dibuang lebih dulu karena parse_ini
 * hanya mengenali `;`.
 */
function env(string $key, mixed $default = null): mixed
{
    static $values = null;

    if ($values === null) {
        $values = [];
        $path   = TITIPIN_ROOT . '/.env';

        if (is_file($path)) {
            $raw    = (string) file_get_contents($path);
            $raw    = (string) preg_replace('/^\s*#.*$/m', '', $raw);
            $parsed = parse_ini_string($raw, false, INI_SCANNER_TYPED);

            $values = is_array($parsed) ? $parsed : [];
        }
    }

    return $values[$key] ?? $default;
}

/**
 * Membaca konfigurasi dengan notasi titik, misalnya config('app.upload.dir').
 */
function config(string $key, mixed $default = null): mixed
{
    static $files = [];

    [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

    if (!isset($files[$file])) {
        $full = TITIPIN_ROOT . '/config/' . $file . '.php';
        $files[$file] = is_file($full) ? require $full : [];
    }

    if ($path === null) {
        return $files[$file];
    }

    $value = $files[$file];

    foreach (explode('.', $path) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

/**
 * Meng-escape teks untuk konteks HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * URL absolut terhadap document root aplikasi.
 */
function url(string $path = '/'): string
{
    return App::basePath() . '/' . ltrim($path, '/');
}

/**
 * URL aset statis di dalam public/.
 *
 * Waktu ubah berkas ditambahkan sebagai versi agar browser tidak
 * memakai salinan lama setelah CSS/JS diperbarui.
 */
function asset(string $path): string
{
    $bersih = ltrim($path, '/');
    $berkas = dirname(__DIR__) . '/public/assets/' . $bersih;
    $versi  = is_file($berkas) ? (string) filemtime($berkas) : '';

    return url('assets/' . $bersih) . ($versi === '' ? '' : '?v=' . $versi);
}

/**
 * URL berkas unggahan berdasarkan nama berkas di database.
 */
function upload_url(?string $file): ?string
{
    if ($file === null || $file === '') {
        return null;
    }

    return url('uploads/' . rawurlencode($file));
}

/**
 * Field tersembunyi berisi token CSRF.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Session::csrfToken()) . '">';
}

/**
 * Mengambil nilai input lama setelah validasi gagal.
 */
function old(string $key, mixed $default = ''): string
{
    $old = Session::flashed('_old', []);

    return (string) ($old[$key] ?? $default);
}

/**
 * Pesan error validasi untuk satu field.
 */
function error_for(string $key): ?string
{
    $errors = Session::flashed('_errors', []);

    return $errors[$key] ?? null;
}

/**
 * Inisial nama untuk avatar bulat.
 */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];

    $letters = array_map(
        static fn (string $part): string => mb_substr($part, 0, 1),
        array_slice(array_filter($parts), 0, 2)
    );

    return mb_strtoupper(implode('', $letters));
}

/**
 * Tanggal panjang berbahasa Indonesia, misalnya "13 Okt 2026".
 */
function tanggal_id(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $time = strtotime($date);

    if ($time === false) {
        return $date;
    }

    $bulan = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    return (int) date('j', $time) . ' ' . $bulan[(int) date('n', $time)] . ' ' . date('Y', $time);
}

/**
 * Tanggal dan jam berbahasa Indonesia, misalnya "13 Okt 2026 · 14.05".
 */
function tanggal_jam_id(?string $value): string
{
    if ($value === null || $value === '') {
        return '-';
    }

    $time = strtotime($value);

    if ($time === false) {
        return $value;
    }

    return tanggal_id(date('Y-m-d', $time)) . ' · ' . date('H.i', $time);
}

/**
 * Sisa waktu dalam bentuk "23 jam 12 menit" atau "kedaluwarsa".
 */
function sisa_waktu(?string $expiresAt): string
{
    if ($expiresAt === null || $expiresAt === '') {
        return 'tidak ada batas';
    }

    $time = strtotime($expiresAt);

    if ($time === false) {
        return '-';
    }

    $diff = $time - time();

    if ($diff <= 0) {
        return 'kedaluwarsa';
    }

    $hours   = intdiv($diff, 3600);
    $minutes = intdiv($diff % 3600, 60);

    if ($hours > 0) {
        return $hours . ' jam ' . $minutes . ' menit';
    }

    return $minutes . ' menit';
}

/**
 * Kelas warna badge berdasarkan status.
 */
function status_class(string $status): string
{
    return match ($status) {
        'Selesai', 'Tersedia', 'Disetujui', 'Aktif' => 'green',
        'Prioritas', 'Ditolak', 'Ditangguhkan'        => 'red',
        'Diproses', 'Proses Perbaikan', 'Diajukan'    => 'blue',
        default                                       => '',
    };
}

/**
 * Ikon teks untuk jenis laporan.
 */
function jenis_icon(string $jenis): string
{
    return match ($jenis) {
        'Lost'  => '⊗',
        'Found' => '◇',
        default => '⚡',
    };
}

/**
 * Kelas ikon untuk jenis laporan.
 */
function jenis_icon_class(string $jenis): string
{
    return match ($jenis) {
        'Lost'  => 'lost',
        'Found' => 'found',
        default => 'facility',
    };
}

/**
 * Label jenis laporan.
 */
function jenis_label(string $jenis): string
{
    return config('app.label.jenis.' . $jenis, $jenis);
}

/**
 * Label status dengan fallback ke nilai aslinya.
 */
function status_label(string $status, string $grup = 'status_barang'): string
{
    return config('app.label.' . $grup . '.' . $status, $status);
}
