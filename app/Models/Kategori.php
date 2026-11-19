<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Master kategori barang dan fasilitas.
 */
final class Kategori extends Model
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::rows('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori');
    }

    public static function exists(int $id): bool
    {
        return self::scalar('SELECT 1 FROM kategori WHERE id_kategori = ?', [$id]) !== false;
    }
}
