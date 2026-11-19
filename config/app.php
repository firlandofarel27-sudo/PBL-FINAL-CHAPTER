<?php

declare(strict_types=1);

/**
 * Konfigurasi aplikasi TITIPIN.
 *
 * Nilai di sini menjadi satu sumber kebenaran untuk menu per peran,
 * aturan kode redeem, batas upload, dan pilihan waktu kejadian.
 */
return [
    'name'     => 'TITIPIN',
    'tagline'  => 'Campus Care',
    'timezone' => 'Asia/Jakarta',

    /*
    |--------------------------------------------------------------------------
    | Berkas unggahan
    |--------------------------------------------------------------------------
    |
    | Disimpan di dalam document root agar bisa disajikan langsung oleh web
    | server. Folder dijaga oleh .htaccess yang mematikan handler PHP.
    |
    */
    'upload' => [
        'dir'          => __DIR__ . '/../public/uploads',
        'url'          => '/uploads',
        'allowed_ext'  => ['jpg', 'jpeg', 'png'],
        'allowed_mime' => ['image/jpeg', 'image/png'],
        'max_bytes'    => [
            'barang'    => 2 * 1024 * 1024,
            'fasilitas' => 2 * 1024 * 1024,
            'klaim'     => 5 * 1024 * 1024,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Aturan kode redeem
    |--------------------------------------------------------------------------
    |
    | Alfabet menghilangkan huruf yang mudah tertukar (I, O, 0, 1).
    |
    */
    'redeem' => [
        'alphabet'  => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
        'length'    => 5,
        'ttl_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu navigasi per peran
    |--------------------------------------------------------------------------
    |
    | Setiap item: [kunci aktif, ikon, label, path].
    |
    */
    'menu' => [
        'Mahasiswa' => [
            ['beranda', '⌂', 'Beranda', '/beranda'],
            ['katalog', '▦', 'Katalog', '/katalog'],
            ['lapor', '＋', 'Laporan', '/laporan/baru'],
            ['riwayat', '▤', 'Riwayat', '/riwayat'],
            ['klaim', '◈', 'Klaim', '/klaim'],
            ['profil', '●', 'Profil', '/profil'],
        ],
        'Admin' => [
            ['beranda', '⌂', 'Beranda', '/beranda'],
            ['laporan', '▤', 'Laporan', '/admin/barang'],
            ['verifikasi', '▣', 'Verifikasi', '/verifikasi'],
            ['analitik', '▥', 'Analitik', '/admin/analitik'],
            ['profil', '●', 'Profil', '/profil'],
        ],
        'Super Admin' => [
            ['beranda', '⌂', 'Dashboard', '/beranda'],
            ['pengguna', '♙', 'Manajemen User', '/admin/pengguna'],
            ['laporan', '▤', 'Semua Laporan', '/admin/barang'],
            ['lokasi', '⌖', 'Lokasi & Unit', '/admin/lokasi'],
            ['analitik', '▥', 'Analitik Sistem', '/admin/analitik'],
            ['pengaturan', '⚙', 'Pengaturan', '/admin/pengaturan'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pilihan waktu kejadian pada formulir laporan
    |--------------------------------------------------------------------------
    */
    'waktu_kejadian' => [
        'Sebelum jam 08.00',
        '08.00 – 10.00',
        '10.00 – 12.00',
        '12.00 – 14.00',
        '14.00 – 16.00',
        '16.00 – 18.00',
        'Setelah jam 18.00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Label tampilan untuk nilai enum database
    |--------------------------------------------------------------------------
    */
    'label' => [
        'jenis' => [
            'Lost'  => 'Barang hilang',
            'Found' => 'Barang temuan',
        ],
        'status_barang' => [
            'Menunggu Verifikasi' => 'Menunggu verifikasi',
            'Tersedia'            => 'Tersedia',
            'Selesai'             => 'Selesai',
        ],
        'status_fasilitas' => [
            'Dilaporkan'       => 'Dilaporkan',
            'Proses Perbaikan' => 'Proses perbaikan',
            'Selesai'          => 'Selesai',
        ],
        'status_klaim' => [
            'Diajukan'  => 'Menunggu verifikasi',
            'Disetujui' => 'Selesai',
            'Ditolak'   => 'Ditolak',
        ],
    ],
];
