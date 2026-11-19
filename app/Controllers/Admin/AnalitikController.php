<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Services\StatistikService;

final class AnalitikController extends Controller
{
    public function index(): void
    {
        $this->view('admin/analitik', [
            'title'  => 'Analitik · TITIPIN',
            'active' => 'analitik',
            'statistik' => StatistikService::analitik(),
        ]);
    }
}
