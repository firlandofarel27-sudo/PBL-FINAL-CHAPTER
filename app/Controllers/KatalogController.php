<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Models\Barang;
use App\Models\Fasilitas;
use App\Models\Kategori;
use App\Models\Lokasi;

/**
 * Katalog publik di dalam aplikasi.
 *
 * Dua tab: barang hilang/temuan dan kerusakan fasilitas. Pencarian dan
 * filter dikerjakan di SQL sehingga hasil tetap benar untuk data besar.
 */
final class KatalogController extends Controller
{
    public function index(Request $request): void
    {
        $tab      = (string) $request->query('tab', 'barang');
        $tab      = $tab === 'fasilitas' ? 'fasilitas' : 'barang';
        $keyword  = (string) $request->query('q', '');
        $kategori = (string) $request->query('kategori', '');
        $lokasi   = (string) $request->query('lokasi', '');

        $filters = array_filter([
            'q'        => $keyword,
            'kategori' => $kategori,
            'lokasi'   => $lokasi,
        ], static fn (string $value): bool => $value !== '');

        $this->view('katalog/index', [
            'title'    => 'Katalog · TITIPIN',
            'active'   => 'katalog',
            'tab'      => $tab,
            'keyword'  => $keyword,
            'kategori' => $kategori,
            'lokasi'   => $lokasi,
            'items'    => $tab === 'barang'
                ? Barang::all($filters)
                : Fasilitas::all($filters),
            'kategoris' => Kategori::all(),
            'lokasis'   => Lokasi::all(),
        ], $this->layout());
    }
}
