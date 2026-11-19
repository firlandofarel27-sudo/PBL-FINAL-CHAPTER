<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Kesalahan tingkat aplikasi yang aman ditampilkan ke pengguna.
 *
 * Controller menangkap kelas ini, menampilkan pesannya sebagai flash, lalu
 * mengalihkan halaman. Exception lain tetap dicatat sebagai error server.
 */
class AppException extends \RuntimeException
{
}
