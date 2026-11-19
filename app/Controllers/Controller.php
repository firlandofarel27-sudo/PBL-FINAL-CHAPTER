<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;

/**
 * Perilaku bersama seluruh controller.
 *
 * Controller hanya mengurus alur request-response: memvalidasi masukan,
 * memanggil model/service, lalu memilih view atau redirect.
 */
abstract class Controller
{
    /**
     * Merender view dengan layout aplikasi.
     */
    protected function view(string $view, array $data = [], string $layout = 'layouts/app'): never
    {
        View::render($view, $data, $layout);
    }

    /**
     * Layout yang dipakai halaman yang boleh dibuka tanpa login.
     *
     * Pengunjung melihat shell publik (tanpa sidebar), sedangkan pengguna
     * yang sudah masuk tetap memakai shell aplikasi.
     */
    protected function layout(): string
    {
        return Auth::check() ? 'layouts/app' : 'layouts/public';
    }

    protected function redirect(string $path): never
    {
        Response::redirect($path);
    }

    protected function back(string $fallback = '/beranda'): never
    {
        Response::back($fallback);
    }

    /**
     * Menampilkan pesan sukses pada request berikutnya.
     */
    protected function success(string $message): void
    {
        Session::flash('success', $message);
    }

    /**
     * Menampilkan pesan gagal pada request berikutnya.
     */
    protected function error(string $message): void
    {
        Session::flash('error', $message);
    }

    /**
     * Menjalankan validasi; bila gagal, input lama disimpan lalu pengguna
     * dikembalikan ke halaman asal.
     *
     * @param  callable(Validator): void  $rules
     * @return array<string, mixed>
     */
    protected function validate(Request $request, callable $rules, string $fallback): array
    {
        $data      = $request->all();
        $validator = Validator::make($data);

        $rules($validator);

        if ($validator->fails()) {
            Session::flash('_errors', $validator->errors());
            Session::flash('_old', $data);

            Response::redirect($fallback);
        }

        return $data;
    }

    /**
     * Nilai integer dari parameter path.
     */
    protected function id(string $value): int
    {
        return max(0, (int) $value);
    }
}
