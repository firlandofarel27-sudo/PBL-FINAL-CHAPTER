<?php

declare(strict_types=1);

/**
 * Judul halaman dengan subjudul dan tombol aksi opsional.
 *
 * @var string  $judul
 * @var string  $sub
 * @var string  $aksi
 */
?>
<div class="welcome">
    <div>
        <div class="greeting"><?= e($sub) ?></div>
        <h1><?= e($judul) ?></h1>
    </div>

    <div class="welcome-actions"><?= $aksi ?? '' ?></div>
</div>
