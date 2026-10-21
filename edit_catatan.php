<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin') {
    die("Akses ditolak.");
}

$id = (int)($_GET['id'] ?? 0);

$stmt = mysqli_prepare($koneksi, "
    SELECT id, tanggal, keterangan, tindakan, status
    FROM t_pelanggaran_siswa
    WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    die("Catatan tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Catatan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-warning">
            <h4 class="mb-0">Edit Catatan Pelanggaran</h4>
        </div>

        <div class="card-body">
            <form action="proses_edit_catatan.php" method="POST">
                <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                           value="<?= htmlspecialchars($data['tanggal']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control"
                              rows="3" required><?= htmlspecialchars($data['keterangan']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tindakan</label>
                    <textarea name="tindakan" class="form-control"
                              rows="3"><?= htmlspecialchars($data['tindakan'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <?php
                        $statuses = [
                            'Belum Ditindaklanjuti',
                            'Diproses',
                            'Selesai'
                        ];
                        foreach ($statuses as $status):
                        ?>
                            <option value="<?= htmlspecialchars($status) ?>"
                                <?= $data['status'] == $status ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                <a href="catatan_pelanggaran.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>