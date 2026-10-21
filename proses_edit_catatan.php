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

$id = (int)($_POST['id'] ?? 0);
$tanggal = $_POST['tanggal'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');
$status = $_POST['status'] ?? '';

$status_valid = [
    'Belum Ditindaklanjuti',
    'Diproses',
    'Selesai'
];

if (
    $id <= 0 ||
    $tanggal == '' ||
    $keterangan == '' ||
    !in_array($status, $status_valid, true)
) {
    die("Data edit tidak lengkap atau tidak valid.");
}

$stmt = mysqli_prepare($koneksi, "
    UPDATE t_pelanggaran_siswa
    SET tanggal = ?,
        keterangan = ?,
        tindakan = ?,
        status = ?
    WHERE id = ?
");

mysqli_stmt_bind_param(
    $stmt,
    "ssssi",
    $tanggal,
    $keterangan,
    $tindakan,
    $status,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: catatan_pelanggaran.php?pesan=edit");
    exit;
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
die("Gagal mengedit catatan: " . $error);