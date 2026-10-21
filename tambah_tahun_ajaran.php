
<?php

session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Tahun Ajaran</title>
</head>
<body>

<h1>Tambah Tahun Ajaran</h1>

<a href="kelola_tahun_ajaran.php">Kembali</a>
<hr>

<form action="proses_tambah_tahun_ajaran.php" method="POST">

    <p>Nama Tahun Ajaran</p>
    <input type="text" name="nama"
           placeholder="Contoh: 2026/2027" required>

    <p>Tanggal Mulai</p>
    <input type="date" name="tanggal_mulai" required>

    <p>Tanggal Selesai</p>
    <input type="date" name="tanggal_selesai" required>

    <p>Status</p>
    <select name="status_aktif" required>
        <option value="1">Aktif</option>
        <option value="0">Tidak Aktif</option>
    </select>

    <p>
        <button type="submit" name="simpan">Simpan</button>
    </p>

</form>

</body>
</html>
