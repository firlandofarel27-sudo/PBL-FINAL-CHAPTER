<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\AppException;

/**
 * Menyimpan dan menghapus berkas gambar hasil unggahan pengguna.
 *
 * Nama berkas selalu dibuat ulang secara acak, sehingga nama asli dari
 * peramban tidak pernah dipakai di sistem berkas.
 */
final class UploadService
{
    /**
     * Menyimpan berkas dan mengembalikan nama berkas barunya.
     *
     * @param  array{name: string, type: string, tmp_name: string, error: int, size: int}  $file
     * @param  string  $kind  Kunci batas ukuran pada config('app.upload.max_bytes').
     *
     * @throws AppException Bila berkas tidak memenuhi aturan unggah.
     */
    public static function store(array $file, string $prefix, string $kind): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new AppException('Gagal mengunggah berkas. Silakan coba lagi.');
        }

        $maxBytes = (int) config('app.upload.max_bytes.' . $kind, 2 * 1024 * 1024);

        if ($file['size'] > $maxBytes) {
            throw new AppException('Ukuran berkas maksimal ' . self::megabytes($maxBytes) . ' MB.');
        }

        $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, config('app.upload.allowed_ext', []), true)) {
            throw new AppException('Format berkas tidak valid. Gunakan JPG atau PNG.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = $finfo === false ? '' : (string) finfo_file($finfo, $file['tmp_name']);

        if ($finfo !== false) {
            finfo_close($finfo);
        }

        if (!in_array($mime, config('app.upload.allowed_mime', []), true)) {
            throw new AppException('Tipe berkas tidak diizinkan. Gunakan JPG atau PNG.');
        }

        $name = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $dir  = (string) config('app.upload.dir');

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new AppException('Folder unggahan tidak dapat dibuat. Hubungi administrator.');
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new AppException('Berkas gagal disimpan. Silakan coba lagi.');
        }

        return $name;
    }

    /**
     * Menghapus berkas unggahan bila ada.
     */
    public static function delete(?string $name): void
    {
        if ($name === null || $name === '') {
            return;
        }

        $path = (string) config('app.upload.dir') . '/' . basename($name);

        if (is_file($path)) {
            unlink($path);
        }
    }

    private static function megabytes(int $bytes): string
    {
        return rtrim(rtrim(number_format($bytes / 1048576, 1, ',', ''), '0'), ',');
    }
}
