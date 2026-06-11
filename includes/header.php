<?php $page = $page ?? ''; ?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kehadiran Kursus & Mesyuarat — MAIK</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="container">
        <h1>Sistem Kehadiran Kursus &amp; Mesyuarat</h1>
        <nav>
            <a href="index.php"     class="<?= $page === 'home'      ? 'active' : '' ?>">Ringkasan</a>
            <a href="kursus.php"    class="<?= $page === 'kursus'    ? 'active' : '' ?>">Kursus / Mesyuarat</a>
            <a href="peserta.php"   class="<?= $page === 'peserta'   ? 'active' : '' ?>">Peserta</a>
            <a href="kehadiran.php" class="<?= $page === 'kehadiran' ? 'active' : '' ?>">Kehadiran</a>
        </nav>
    </div>
</header>
<main class="container">
