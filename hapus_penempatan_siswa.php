
<?php
// hapus_penempatan_siswa.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Pastikan menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

// Periksa token keamanan
$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    $_SESSION['pesan_error'] = "Permintaan tidak valid.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Ambil ID penempatan
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['pesan_error'] = "ID penempatan tidak valid.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Periksa apakah data tersedia
$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM t_kelas_siswa
    WHERE id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$data_ada = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$data_ada) {
    $_SESSION['pesan_error'] = "Data penempatan tidak ditemukan.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Hapus data penempatan
$stmt = mysqli_prepare($koneksi, "
    DELETE FROM t_kelas_siswa
    WHERE id = ?
");

if (!$stmt) {
    $_SESSION['pesan_error'] = "Gagal menyiapkan query hapus.";
    header("Location: penempatan_siswa.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);

// Jalankan penghapusan
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    $_SESSION['pesan_sukses'] = "Data penempatan siswa berhasil dihapus.";
    header("Location: penempatan_siswa.php");
    exit;
}

$pesan = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

$_SESSION['pesan_error'] = "Gagal menghapus data penempatan: " . $pesan;
header("Location: penempatan_siswa.php");
exit;
?>
