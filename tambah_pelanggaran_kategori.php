
<?php
session_start(); // Memulai session
include 'config/koneksi.php'; // Menghubungkan ke database

// Memeriksa status login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Halaman hanya boleh diakses admin
if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori Pelanggaran</title>
</head>
<body>

<h2>Tambah Kategori Pelanggaran</h2>

<!-- Form untuk mengirim data kategori ke file proses -->
<form action="proses_tambah_pelanggaran_kategori.php" method="POST">

    <!-- Input nama kategori -->
    <label>Nama Kategori</label><br>
    <input type="text" name="nama" maxlength="100" required>

    <br><br>

    <!-- Input deskripsi kategori -->
    <label>Deskripsi</label><br>
    <textarea name="deskripsi" rows="4" required></textarea>

    <br><br>

    <!-- Memilih status aktif atau tidak aktif -->
    <label>Status</label><br>
    <select name="status_aktif" required>
        <option value="1">Aktif</option>
        <option value="0">Tidak Aktif</option>
    </select>

    <br><br>

    <!-- Tombol untuk menyimpan data -->
    <button type="submit">Simpan</button>

    <!-- Tombol kembali ke daftar kategori -->
    <a href="kelola_pelanggaran_kategori.php">Kembali</a>

</form>

</body>
</html>
