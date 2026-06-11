<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (($_POST['action'] ?? '') === 'hapus') {
        $stmt = $pdo->prepare('DELETE FROM mesyuarat WHERE id = ?');
        $stmt->execute([$_POST['id']]);
        $_SESSION['jenis'] = 'ok';
        $_SESSION['mesej'] = 'Rekod kursus/mesyuarat telah dihapus.';
        header('Location: kursus.php');
        exit;
    }

    $id     = $_POST['id'] ?? '';
    $tajuk  = trim($_POST['tajuk'] ?? '');
    $tarikh = trim($_POST['tarikh'] ?? '');

    // validasi
    $ralat = [];
    if ($tajuk === '')  { $ralat[] = 'Tajuk wajib diisi.'; }
    if ($tarikh === '') { $ralat[] = 'Tarikh wajib diisi.'; }

    // upload pdf (kalau ada)
    $namaPdf = null;
    if (!empty($_FILES['bahan_pdf']['name'])) {
        $f = $_FILES['bahan_pdf'];
        if ($f['error'] === UPLOAD_ERR_OK && strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) === 'pdf') {
            $namaPdf = 'bahan_' . time() . '.pdf';
            move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $namaPdf);
        } else {
            $ralat[] = 'Bahan mestilah fail PDF sahaja.';
        }
    }

    if ($ralat) {
        $_SESSION['jenis'] = 'err';
        $_SESSION['mesej'] = implode(' ', $ralat);
    } elseif ($id) {
        if ($namaPdf) {
            $pdo->prepare('UPDATE mesyuarat SET tajuk=?, tarikh=?, bahan_pdf=? WHERE id=?')
                ->execute([$tajuk, $tarikh, $namaPdf, $id]);
        } else {
            $pdo->prepare('UPDATE mesyuarat SET tajuk=?, tarikh=? WHERE id=?')
                ->execute([$tajuk, $tarikh, $id]);
        }
        $_SESSION['jenis'] = 'ok';
        $_SESSION['mesej'] = 'Rekod telah dikemaskini.';
    } else {
        $pdo->prepare('INSERT INTO mesyuarat (tajuk, tarikh, bahan_pdf) VALUES (?, ?, ?)')
            ->execute([$tajuk, $tarikh, $namaPdf]);
        $_SESSION['jenis'] = 'ok';
        $_SESSION['mesej'] = 'Kursus/mesyuarat baharu telah didaftarkan.';
    }
    header('Location: kursus.php');
    exit;
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM mesyuarat WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

$senarai = $pdo->query('SELECT * FROM mesyuarat ORDER BY tarikh DESC')->fetchAll();

$page = 'kursus';
require 'includes/header.php';
?>

<?php if (!empty($_SESSION['mesej'])): ?>
    <div class="alert <?= $_SESSION['jenis'] === 'ok' ? 'alert-ok' : 'alert-err' ?>"><?= e($_SESSION['mesej']) ?></div>
    <?php unset($_SESSION['mesej'], $_SESSION['jenis']); ?>
<?php endif; ?>

<div class="card">
    <h2><?= $edit ? 'Kemaskini Kursus / Mesyuarat' : 'Daftar Kursus / Mesyuarat' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="row">
            <div class="field">
                <label>Tajuk</label>
                <input type="text" name="tajuk" value="<?= e($edit['tajuk'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label>Tarikh</label>
                <input type="date" name="tarikh" value="<?= e($edit['tarikh'] ?? '') ?>" required>
            </div>
        </div>
        <div class="field">
            <label>Bahan Kursus (PDF) <span class="muted">(pilihan)</span></label>
            <input type="file" name="bahan_pdf" accept="application/pdf">
        </div>
        <button type="submit"><?= $edit ? 'Kemaskini' : 'Daftar' ?></button>
        <?php if ($edit): ?><a href="kursus.php" class="btn btn-grey">Batal</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>Senarai Kursus / Mesyuarat</h3>
    <?php if (!$senarai): ?>
        <p class="muted">Tiada rekod lagi.</p>
    <?php else: ?>
    <table>
        <tr><th>#</th><th>Tajuk</th><th>Tarikh</th><th>Bahan</th><th>Tindakan</th></tr>
        <?php foreach ($senarai as $i => $m): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($m['tajuk']) ?></td>
            <td><?= e($m['tarikh']) ?></td>
            <td>
                <?php if ($m['bahan_pdf']): ?>
                    <a href="uploads/<?= e($m['bahan_pdf']) ?>" target="_blank">Lihat PDF</a>
                <?php else: ?><span class="muted">-</span><?php endif; ?>
            </td>
            <td class="actions">
                <a href="kursus.php?edit=<?= $m['id'] ?>" class="btn btn-sm btn-grey">Kemaskini</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Hapus rekod ini?')">
                    <input type="hidden" name="action" value="hapus">
                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                    <button type="submit" class="btn-sm btn-red">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
