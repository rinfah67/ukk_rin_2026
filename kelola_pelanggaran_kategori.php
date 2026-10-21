
<?php
session_start(); // Memulai session login
include 'config/koneksi.php'; // Menghubungkan ke database

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php"); // Kembali ke halaman login
    exit; // Menghentikan program
}

// Memastikan hanya admin yang bisa mengakses halaman
if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!"); // Menolak akses selain admin
}

// Mengambil semua kategori pelanggaran dari database
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_pelanggaran_kategori ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <meta charset="UTF-8">
    <title>Kelola Kategori Pelanggaran</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<div class="container-fluid">
    <?php include "layout/sidebar.php"; ?>

    <main class="row">
        <div class="col-9 offset-3 col-lg-10 offset-lg-2 p-4">

<h2>Data Kategori Pelanggaran</h2>

<!-- Tombol kembali ke dashboard dan tambah kategori -->
<a href="dashboard.php">Kembali ke Dashboard</a> |
<a href="tambah_pelanggaran_kategori.php">Tambah Kategori</a>

<br><br>

<!-- Tabel untuk menampilkan data kategori -->
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>No</th>
        <th>Nama Kategori</th>
        <th>Deskripsi</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    <?php
    $no = 1; // Nomor urut tabel

    // Mengulang data kategori satu per satu
    while ($data = mysqli_fetch_assoc($query)) {
    ?>
    <tr>
        <td><?= $no++; ?></td>

        <!-- Menampilkan nama kategori -->
        <td><?= htmlspecialchars($data['nama']); ?></td>

        <!-- Menampilkan deskripsi kategori -->
        <td><?= htmlspecialchars($data['deskripsi']); ?></td>

        <!-- Menampilkan status kategori -->
        <td>
            <?= $data['status_aktif'] == 1 ? 'Aktif' : 'Tidak Aktif'; ?>
        </td>

        <td>
            <!-- Tombol edit berdasarkan ID kategori -->
            <a href="edit_pelanggaran_kategori.php?id=<?= $data['id']; ?>">
                Edit
            </a> |

            <!-- Tombol hapus berdasarkan ID kategori -->
            <a href="hapus_pelanggaran_kategori.php?id=<?= $data['id']; ?>"
               onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                Hapus
            </a>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>
