
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

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: tambah_walikelas.php");
    exit;
}

$tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];
$kelas_id = (int) $_POST['kelas_id'];
$guru_id = (int) $_POST['guru_id'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = (int) $_POST['status_aktif'];

if ($tanggal_selesai < $tanggal_mulai) {
    die("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

$query = "INSERT INTO t_wali_kelas
          (tahun_ajaran_id, kelas_id, guru_id,
           tanggal_mulai, tanggal_selesai, status_aktif)
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param(
    $stmt,
    "iiissi",
    $tahun_ajaran_id,
    $kelas_id,
    $guru_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: kelola_walikelas.php");
    exit;
}

echo "Gagal menyimpan data: " . mysqli_error($koneksi);
?>
