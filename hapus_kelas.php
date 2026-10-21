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

if (!isset($_GET['id'])) {
    die("ID data tidak ditemukan!");
}

$id = (int) $_GET['id'];

$sql = "DELETE FROM t_kelas_siswa WHERE id='$id'";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kelola_kelas.php");
    exit;
} else {
    echo "Gagal menghapus data: " . mysqli_error($koneksi);
}

?>