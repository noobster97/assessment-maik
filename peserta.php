<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (($_POST['action'] ?? '') === 'hapus') {
        $pdo->prepare('DELETE FROM peserta WHERE id = ?')->execute([$_POST['id']]);
        $_SESSION['jenis'] = 'ok';
        $_SESSION['mesej'] = 'Rekod peserta telah dihapus.';
        header('Location: peserta.php');
        exit;
    }

    $id = $_POST['id'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $no_pekerja = trim($_POST['no_pekerja'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');

    // validasi
    $ralat = [];
    if ($nama === '') { $ralat[] = 'Nama peserta wajib diisi.'; }
    if ($no_pekerja === '') { $ralat[] = 'No. pekerja wajib diisi.'; }
    if ($jabatan === '') { $ralat[] = 'Jabatan wajib diisi.'; }

    if ($ralat) {
        $_SESSION['jenis'] = 'err';
        $_SESSION['mesej'] = implode(' ', $ralat);
    } elseif ($id) {
        $pdo->prepare('UPDATE peserta SET nama=?, no_pekerja=?, jabatan=? WHERE id=?')
            ->execute([$nama, $no_pekerja, $jabatan, $id]);
        $_SESSION['jenis'] = 'ok';
        $_SESSION['mesej'] = 'Rekod peserta telah dikemaskini.';
    } else {
        $pdo->prepare('INSERT INTO peserta (nama, no_pekerja, jabatan) VALUES (?, ?, ?)')
            ->execute([$nama, $no_pekerja, $jabatan]);
        $_SESSION['jenis'] = 'ok';
        $_SESSION['mesej'] = 'Peserta baharu telah didaftarkan.';
    }
    header('Location: peserta.php');
    exit;
}

// carian peserta
$carian = trim($_GET['carian'] ?? '');
if ($carian !== '') {
    $stmt = $pdo->prepare('SELECT * FROM peserta WHERE nama LIKE ? OR no_pekerja LIKE ? ORDER BY nama');
    $stmt->execute(["%$carian%", "%$carian%"]);
    $senarai = $stmt->fetchAll();
} else {
    $senarai = $pdo->query('SELECT * FROM peserta ORDER BY nama')->fetchAll();
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM peserta WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}

$page = 'peserta';
require 'includes/header.php';
?>

<?php if (!empty($_SESSION['mesej'])): ?>
    <div class="alert <?= $_SESSION['jenis'] === 'ok' ? 'alert-ok' : 'alert-err' ?>"><?= e($_SESSION['mesej']) ?></div>
    <?php unset($_SESSION['mesej'], $_SESSION['jenis']); ?>
<?php endif; ?>

<div class="card">
    <h2><?= $edit ? 'Kemaskini Peserta' : 'Daftar Peserta' ?></h2>
    <form method="post">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="row">
            <div class="field">
                <label>Nama Peserta</label>
                <input type="text" name="nama" value="<?= e($edit['nama'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label>No. Pekerja</label>
                <input type="text" name="no_pekerja" value="<?= e($edit['no_pekerja'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label>Jabatan</label>
                <input type="text" name="jabatan" value="<?= e($edit['jabatan'] ?? '') ?>" required>
            </div>
        </div>
        <button type="submit"><?= $edit ? 'Kemaskini' : 'Daftar' ?></button>
        <?php if ($edit): ?><a href="peserta.php" class="btn btn-grey">Batal</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h3>Senarai Peserta</h3>
    <form method="get" class="row" style="align-items:flex-end; margin-bottom:14px">
        <div class="field">
            <label>Cari (Nama atau No. Pekerja)</label>
            <input type="search" name="carian" value="<?= e($carian) ?>" placeholder="Contoh: Ahmad atau MAIK001">
        </div>
        <div class="field" style="flex:0">
            <button type="submit">Cari</button>
            <?php if ($carian !== ''): ?><a href="peserta.php" class="btn btn-grey">Reset</a><?php endif; ?>
        </div>
    </form>

    <?php if (!$senarai): ?>
        <p class="muted"><?= $carian !== '' ? 'Tiada peserta sepadan dengan carian.' : 'Tiada rekod lagi.' ?></p>
    <?php else: ?>
    <table>
        <tr><th>#</th><th>Nama</th><th>No. Pekerja</th><th>Jabatan</th><th>Tindakan</th></tr>
        <?php foreach ($senarai as $i => $p): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($p['nama']) ?></td>
            <td><?= e($p['no_pekerja']) ?></td>
            <td><?= e($p['jabatan']) ?></td>
            <td class="actions">
                <a href="peserta.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-grey">Kemaskini</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Hapus peserta ini?')">
                    <input type="hidden" name="action" value="hapus">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn-sm btn-red">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
