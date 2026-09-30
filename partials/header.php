<?php
/** Variabel: $judul (string), $menu ('produk' | 'tambah' | 'log') */
$flash = flash_get();
$menu  = $menu ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - Inventaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&family=IBM+Plex+Sans:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="index.php">Inventaris</a>
        <nav class="nav" aria-label="Menu utama">
            <a href="index.php" <?= $menu === 'produk' ? 'aria-current="page"' : '' ?>>Daftar Produk</a>
            <a href="create.php" <?= $menu === 'tambah' ? 'aria-current="page"' : '' ?>>Tambah Produk</a>
            <a href="logs.php" <?= $menu === 'log' ? 'aria-current="page"' : '' ?>>Riwayat Aktivitas</a>
        </nav>
    </div>
</header>
<main class="container">
<?php if ($flash): ?>
    <div class="flash flash-<?= e($flash['tipe']) ?>" role="<?= $flash['tipe'] === 'gagal' ? 'alert' : 'status' ?>">
        <span><?= e($flash['pesan']) ?></span>
        <button type="button" class="flash-close" aria-label="Tutup pesan">&times;</button>
    </div>
<?php endif; ?>
