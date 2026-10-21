<?php

session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

if (!isset($_POST['simpan'])) {
    header("Location: tambah_siswa.php");
    exit;
}

$nis = $_POST['nis'];
$nisn = $_POST['nisn'];
$nama = $_POST['nama'];
$jenis_kelamin = $_POST['jenis_kelamin'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$alamat = $_POST['alamat'];
$status_aktif = $_POST['status_aktif'];

$sql = "INSERT INTO t_siswa
        (
            nis,
            nisn,
            nama,
            jenis_kelamin,
            tanggal_lahir,
            alamat,
            status_aktif
        )
        VALUES
        (
            '$nis',
            '$nisn',
            '$nama',
            '$jenis_kelamin',
            '$tanggal_lahir',
            '$alamat',
            '$status_aktif'
        )";

if (mysqli_query($koneksi, $sql)) {

    header("Location: kelola_siswa.php");
    exit;

} else {

    echo "Gagal menambahkan siswa: "
         . mysqli_error($koneksi);
}

?>