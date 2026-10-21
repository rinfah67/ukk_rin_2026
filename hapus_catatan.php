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

if ($id <= 0) {
    die("ID catatan tidak valid.");
}

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_pelanggaran_siswa WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: catatan_pelanggaran.php?pesan=hapus");
    exit;
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
die("Gagal menghapus catatan: " . $error);