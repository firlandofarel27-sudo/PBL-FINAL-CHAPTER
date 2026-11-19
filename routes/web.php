<?php

declare(strict_types=1);

use App\Core\Router;

/**
 * Tabel rute aplikasi.
 *
 * Format handler: 'Controller@method' di dalam namespace App\Controllers.
 * Middleware: 'auth', 'guest', 'role:Peran1,Peran2'.
 */
$router = new Router();

/* --- Autentikasi ------------------------------------------------------- */
$router->get('/login', 'AuthController@loginForm', ['guest']);
$router->post('/login', 'AuthController@login', ['guest']);
$router->get('/daftar', 'AuthController@registerForm', ['guest']);
$router->post('/daftar', 'AuthController@register', ['guest']);
$router->post('/logout', 'AuthController@logout', ['auth']);

/* --- Halaman publik (tanpa login) ------------------------------------- */
$router->get('/', 'DashboardController@index');
$router->get('/beranda', 'DashboardController@index');
$router->get('/katalog', 'KatalogController@index');
$router->get('/laporan/barang/{id}', 'BarangController@show');
$router->get('/laporan/fasilitas/{id}', 'FasilitasController@show');

/* --- Laporan milik mahasiswa (perlu login) ---------------------------- */
$router->get('/riwayat', 'BarangController@index', ['auth']);
$router->get('/laporan/baru', 'LaporanController@create', ['role:Mahasiswa']);
$router->post('/laporan', 'LaporanController@store', ['role:Mahasiswa']);

/* --- Klaim ------------------------------------------------------------ */
$router->get('/klaim', 'KlaimController@index', ['auth']);
$router->get('/klaim/baru/{id}', 'KlaimController@create', ['role:Mahasiswa']);
$router->post('/klaim', 'KlaimController@store', ['role:Mahasiswa']);
$router->get('/klaim/{id}', 'KlaimController@show', ['auth']);

/* --- Verifikasi oleh petugas ------------------------------------------ */
$router->get('/verifikasi', 'VerifikasiController@index', ['role:Admin,Super Admin']);
$router->post('/verifikasi', 'VerifikasiController@lookup', ['role:Admin,Super Admin']);
$router->post('/klaim/{id}/redeem', 'VerifikasiController@redeem', ['role:Admin,Super Admin']);

/* --- Profil ----------------------------------------------------------- */
$router->get('/profil', 'ProfilController@edit', ['auth']);
$router->post('/profil', 'ProfilController@update', ['auth']);

/* --- Administrasi ----------------------------------------------------- */
$router->get('/admin/barang', 'Admin\BarangAdminController@index', ['role:Admin,Super Admin']);
$router->get('/admin/barang/ekspor', 'Admin\BarangAdminController@export', ['role:Admin,Super Admin']);
$router->post('/admin/barang/{id}/status', 'Admin\BarangAdminController@updateStatus', ['role:Admin,Super Admin']);
$router->post('/admin/barang/{id}/prioritas', 'Admin\BarangAdminController@prioritas', ['role:Admin,Super Admin']);
$router->post('/admin/barang/{id}/hapus', 'Admin\BarangAdminController@destroy', ['role:Admin,Super Admin']);

$router->get('/admin/fasilitas', 'Admin\FasilitasAdminController@index', ['role:Admin,Super Admin']);
$router->post('/admin/fasilitas/{id}/status', 'Admin\FasilitasAdminController@updateStatus', ['role:Admin,Super Admin']);
$router->post('/admin/fasilitas/{id}/hapus', 'Admin\FasilitasAdminController@destroy', ['role:Admin,Super Admin']);

$router->get('/admin/klaim', 'Admin\KlaimAdminController@index', ['role:Admin,Super Admin']);
$router->post('/admin/klaim/{id}/tolak', 'Admin\KlaimAdminController@reject', ['role:Admin,Super Admin']);
$router->post('/admin/klaim/{id}/hapus', 'Admin\KlaimAdminController@destroy', ['role:Admin,Super Admin']);

$router->get('/admin/pengguna', 'Admin\UserController@index', ['role:Super Admin']);
$router->get('/admin/pengguna/baru', 'Admin\UserController@createForm', ['role:Super Admin']);
$router->post('/admin/pengguna', 'Admin\UserController@store', ['role:Super Admin']);
$router->get('/admin/pengguna/{nim}/ubah', 'Admin\UserController@editForm', ['role:Super Admin']);
$router->post('/admin/pengguna/{nim}', 'Admin\UserController@update', ['role:Super Admin']);
$router->post('/admin/pengguna/{nim}/hapus', 'Admin\UserController@destroy', ['role:Super Admin']);

$router->get('/admin/lokasi', 'Admin\LokasiController@index', ['role:Super Admin']);
$router->get('/admin/lokasi/baru', 'Admin\LokasiController@createForm', ['role:Super Admin']);
$router->post('/admin/lokasi', 'Admin\LokasiController@store', ['role:Super Admin']);
$router->get('/admin/lokasi/{id}/ubah', 'Admin\LokasiController@editForm', ['role:Super Admin']);
$router->post('/admin/lokasi/{id}', 'Admin\LokasiController@update', ['role:Super Admin']);
$router->post('/admin/lokasi/{id}/hapus', 'Admin\LokasiController@destroy', ['role:Super Admin']);

$router->get('/admin/analitik', 'Admin\AnalitikController@index', ['role:Admin,Super Admin']);
$router->get('/admin/pengaturan', 'Admin\PengaturanController@index', ['role:Super Admin']);
$router->post('/admin/pengaturan', 'Admin\PengaturanController@update', ['role:Super Admin']);

return $router;
