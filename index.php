<?php
require 'config/db.php';

$jumKursus = $pdo->query('SELECT COUNT(*) FROM mesyuarat')->fetchColumn();
$jumPeserta = $pdo->query('SELECT COUNT(*) FROM peserta')->fetchColumn();
$jumHadir = $pdo->query("SELECT COUNT(*) FROM kehadiran WHERE status = 'Hadir'")->fetchColumn();

$ringkasan = $pdo->query(
    "SELECT m.tajuk, m.tarikh,
            SUM(CASE WHEN k.status = 'Hadir'       THEN 1 ELSE 0 END) AS hadir,
            SUM(CASE WHEN k.status = 'Tidak Hadir' THEN 1 ELSE 0 END) AS tidak,
            COUNT(k.id)                                               AS jumlah
       FROM mesyuarat m
       LEFT JOIN kehadiran k ON k.mesyuarat_id = m.id
      GROUP BY m.id
      ORDER BY m.tarikh DESC"
)->fetchAll();

$page = 'home';
require 'includes/header.php';
?>

<h2>Ringkasan Sistem</h2>

<div class="stats">
    <div class="stat"><div class="num"><?= $jumKursus ?></div><div class="lbl">Kursus / Mesyuarat</div></div>
    <div class="stat"><div class="num"><?= $jumPeserta ?></div><div class="lbl">Peserta Berdaftar</div></div>
    <div class="stat"><div class="num"><?= $jumHadir ?></div><div class="lbl">Jumlah Kehadiran (Hadir)</div></div>
</div>
<br>

<div class="card">
    <h3>Ringkasan Kehadiran Mengikut Kursus / Mesyuarat</h3>
    <?php if (!$ringkasan): ?>
        <p class="muted">Tiada kursus/mesyuarat lagi. Mula dengan <a href="kursus.php">mendaftar satu</a>.</p>
    <?php else: ?>
    <table>
        <tr><th>Tajuk</th><th>Tarikh</th><th>Hadir</th><th>Tidak Hadir</th><th>Jumlah Ditanda</th></tr>
        <?php foreach ($ringkasan as $r): ?>
        <tr>
            <td><?= e($r['tajuk']) ?></td>
            <td><?= e($r['tarikh']) ?></td>
            <td><span class="badge badge-hadir"><?= (int) $r['hadir'] ?></span></td>
            <td><span class="badge badge-tidak"><?= (int) $r['tidak'] ?></span></td>
            <td><?= (int) $r['jumlah'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
<?php require 'includes/footer.php'; ?>
