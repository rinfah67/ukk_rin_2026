
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
    header("Location: kelola_walikelas.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_wali_kelas WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: kelola_walikelas.php");
    exit;
}

echo "Gagal menghapus data: " . mysqli_error($koneksi);
?>
