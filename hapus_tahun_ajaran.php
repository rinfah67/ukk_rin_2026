<?php
session_start();
include 'config/koneksi.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    $query = "DELETE FROM t_tahun_ajaran WHERE id = '$id'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Tahun ajaran berhasil dihapus!'); window.location='kelola_tahun_ajaran.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data: " . mysqli_error($koneksi) . "'); window.location='kelola_tahun_ajaran.php';</script>";
    }
} else {
    header("Location: kelola_tahun_ajaran.php");
    exit();
}
?>