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

// Mengambil semua catatan pelanggaran
$query = mysqli_query($koneksi, "
    SELECT *
    FROM t_pelanggaran_siswa
    ORDER BY tanggal DESC, id DESC
");

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catatan Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

<div class="container-fluid">
    <?php include "layout/sidebar.php"; ?>

    <main class="row">
        <div class="col-9 offset-3 col-lg-10 offset-lg-2 p-4">
            
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3>Catatan Pelanggaran Siswa</h3>
            <p class="text-muted mb-0">Daftar catatan pelanggaran siswa</p>
        </div>

        <a href="tambah_catatan.php" class="btn btn-primary">
            + Tambah Catatan
        </a>
    </div>

    <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] == 'tambah'): ?>
            <div class="alert alert-success">Catatan berhasil ditambahkan.</div>
        <?php elseif ($_GET['pesan'] == 'edit'): ?>
            <div class="alert alert-success">Catatan berhasil diperbarui.</div>
        <?php elseif ($_GET['pesan'] == 'hapus'): ?>
            <div class="alert alert-success">Catatan berhasil dihapus.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru</th>
                            <th>Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['tanggal'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['nama_siswa'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['nama_kelas'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggaran'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['poin'] ?? '0') ?></td>
                                <td><?= htmlspecialchars($row['nama_guru'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['status'] ?? '') ?></td>
                                <td>
                                    <a href="edit_catatan.php?id=<?= (int)$row['id'] ?>"
                                       class="btn btn-warning btn-sm mb-1">
                                        Edit
                                    </a>

                                    <a href="hapus_catatan.php?id=<?= (int)$row['id'] ?>"
                                       class="btn btn-danger btn-sm mb-1"
                                       onclick="return confirm('Yakin ingin menghapus catatan ini?')">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                Belum ada catatan pelanggaran.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>