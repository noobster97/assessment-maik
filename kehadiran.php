<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mesyuarat_id = $_POST['mesyuarat_id'] ?? '';
    $status_semua = $_POST['status'] ?? [];

    $pdo->prepare('DELETE FROM kehadiran WHERE mesyuarat_id = ?')->execute([$mesyuarat_id]);

    $stmt = $pdo->prepare('INSERT INTO kehadiran (mesyuarat_id, peserta_id, status) VALUES (?, ?, ?)');
    foreach ($status_semua as $peserta_id => $status) {
        $stmt->execute([$mesyuarat_id, $peserta_id, $status]);
    }

    $_SESSION['jenis'] = 'ok';
    $_SESSION['mesej'] = 'Kehadiran telah disimpan.';
    header('Location: kehadiran.php?mesyuarat_id=' . urlencode($mesyuarat_id));
    exit;
}

$mesyuarat_id = $_GET['mesyuarat_id'] ?? '';
$senaraiMesyuarat = $pdo->query('SELECT * FROM mesyuarat ORDER BY tarikh DESC')->fetchAll();

$peserta = [];
$ringkasan = ['jumlah' => 0, 'hadir' => 0, 'tidak' => 0];
if ($mesyuarat_id !== '') {
    $stmt = $pdo->prepare(
        'SELECT p.*, k.status
           FROM peserta p
           LEFT JOIN kehadiran k ON k.peserta_id = p.id AND k.mesyuarat_id = ?
          ORDER BY p.nama'
    );
    $stmt->execute([$mesyuarat_id]);
    $peserta = $stmt->fetchAll();

    foreach ($peserta as $p) {
        if ($p['status'] === 'Hadir') { $ringkasan['hadir']++; $ringkasan['jumlah']++; }
        elseif ($p['status'] === 'Tidak Hadir') { $ringkasan['tidak']++; $ringkasan['jumlah']++; }
    }
}

$page = 'kehadiran';
require 'includes/header.php';
?>

<?php if (!empty($_SESSION['mesej'])): ?>
    <div class="alert <?= $_SESSION['jenis'] === 'ok' ? 'alert-ok' : 'alert-err' ?>"><?= e($_SESSION['mesej']) ?></div>
    <?php unset($_SESSION['mesej'], $_SESSION['jenis']); ?>
<?php endif; ?>

<div class="card">
    <h2>Rekod Kehadiran</h2>
    <form method="get">
        <div class="field">
            <label>Pilih Kursus / Mesyuarat</label>
            <select name="mesyuarat_id" onchange="this.form.submit()">
                <option value="">Sila pilih</option>
                <?php foreach ($senaraiMesyuarat as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $m['id'] == $mesyuarat_id ? 'selected' : '' ?>>
                        <?= e($m['tajuk']) ?> (<?= e($m['tarikh']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if ($mesyuarat_id !== ''): ?>
    <?php if (!$peserta): ?>
        <div class="card"><p class="muted">Tiada peserta didaftarkan lagi. Sila daftar peserta dahulu.</p></div>
    <?php else: ?>

    <div class="stats">
        <div class="stat"><div class="num"><?= count($peserta) ?></div><div class="lbl">Jumlah Peserta</div></div>
        <div class="stat"><div class="num"><?= $ringkasan['hadir'] ?></div><div class="lbl">Hadir</div></div>
        <div class="stat"><div class="num"><?= $ringkasan['tidak'] ?></div><div class="lbl">Tidak Hadir</div></div>
    </div>
    <br>

    <div class="card">
        <h3>Tanda Kehadiran</h3>
        <form method="post">
            <input type="hidden" name="mesyuarat_id" value="<?= e($mesyuarat_id) ?>">
            <table>
                <tr><th>Nama</th><th>No. Pekerja</th><th>Jabatan</th><th>Status</th></tr>
                <?php foreach ($peserta as $p): ?>
                <tr>
                    <td><?= e($p['nama']) ?></td>
                    <td><?= e($p['no_pekerja']) ?></td>
                    <td><?= e($p['jabatan']) ?></td>
                    <td class="actions">
                        <label style="display:inline; font-weight:normal; margin-right:12px">
                            <input type="radio" name="status[<?= $p['id'] ?>]" value="Hadir"
                                <?= $p['status'] === 'Hadir' ? 'checked' : '' ?>> Hadir
                        </label>
                        <label style="display:inline; font-weight:normal">
                            <input type="radio" name="status[<?= $p['id'] ?>]" value="Tidak Hadir"
                                <?= $p['status'] === 'Tidak Hadir' ? 'checked' : '' ?>> Tidak Hadir
                        </label>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <br>
            <button type="submit">Simpan Kehadiran</button>
        </form>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
