
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

$user_id = (int)($_SESSION['id_user'] ?? 0);

$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$guru) {
    die("Data guru tidak ditemukan.");
}

$guru_id = (int)$guru['id'];
$id = (int)($_GET['id'] ?? 0);

// Hanya boleh mengedit catatan milik guru yang login
$stmt = mysqli_prepare($koneksi, "
    SELECT * FROM t_pelanggaran_siswa
    WHERE id = ? AND guru_id = ?
");
mysqli_stmt_bind_param($stmt, "ii", $id, $guru_id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    die("Catatan tidak ditemukan atau bukan milik akun kamu.");
}

function aman_edit_pelanggaran($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Catatan Pelanggaran</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { margin:0; background:#f8f9fa; }
.sidebar { position:fixed; top:0; left:0; bottom:0; height:100vh; overflow-y:auto; }
main { margin-left:16.666667%; min-width:0; }
@media(max-width:767.98px) {
    .sidebar { position:relative; height:auto; }
    main { margin-left:0; }
}
</style>
</head>
<body>
<div class="container-fluid">
<div class="row">

<!-- Sidebar sama seperti dashboard -->
<div class="col-md-3 col-lg-2 bg-dark p-3 sidebar">
    <h4 class="text-white mb-4">My Website</h4>
    <ul class="nav nav-pills flex-column">
        <li class="nav-item mb-2">
            <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a href="catatan_pelanggaran.php" class="nav-link active">Catat Pelanggaran</a>
        </li>
        <li class="nav-item mb-2">
            <a href="about_me.php" class="nav-link text-white">About Me</a>
        </li>
        <hr class="text-secondary">
        <li><a href="logout.php" class="nav-link text-danger">Logout</a></li>
    </ul>
</div>

<main class="col-md-9 col-lg-10 p-4">
    <h2>Edit Catatan Pelanggaran</h2>
    <p>Perbarui informasi catatan pelanggaran di bawah ini.</p>

    <form action="proses_edit_pelanggaran.php" method="POST"
          class="card card-body shadow-sm">

        <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">

        <div class="mb-3">
            <label class="form-label">Nama Siswa</label>
            <input class="form-control"
                   value="<?= aman_edit_pelanggaran($data['nama_siswa']) ?>"
                   readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Kelas</label>
            <input class="form-control"
                   value="<?= aman_edit_pelanggaran($data['nama_kelas']) ?>"
                   readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Jenis Pelanggaran</label>
            <input class="form-control"
                   value="<?= aman_edit_pelanggaran($data['nama_pelanggaran']) ?>"
                   readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Poin</label>
            <input class="form-control"
                   value="<?= (int)$data['poin'] ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control"
                   value="<?= aman_edit_pelanggaran($data['tanggal']) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control"
                      rows="3" required><?= aman_edit_pelanggaran($data['keterangan']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Tindakan</label>
            <textarea name="tindakan" class="form-control"
                      rows="3" required><?= aman_edit_pelanggaran($data['tindakan']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <?php foreach (['Belum diproses', 'Diproses', 'Selesai'] as $pilihan): ?>
                    <option value="<?= aman_edit_pelanggaran($pilihan) ?>"
                        <?= $data['status'] == $pilihan ? 'selected' : '' ?>>
                        <?= aman_edit_pelanggaran($pilihan) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="catatan_pelanggaran.php" class="btn btn-secondary mt-2">Batal</a>
    </form>
</main>
</div>
</div>
</body>
</html>
