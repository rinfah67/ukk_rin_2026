
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

if (!isset($_POST['simpan'])) {
    header("Location: tambah_kelas.php");
    exit;
}

$siswa_id = (int) $_POST['siswa_id'];
$kelas_id = (int) $_POST['kelas_id'];
$tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = (int) $_POST['status_aktif'];

if ($tanggal_selesai < $tanggal_mulai) {
    die("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

$sql = "INSERT INTO t_kelas_siswa
        (siswa_id, tahun_ajaran_id, kelas_id,
         tanggal_mulai, tanggal_selesai, status_aktif)
        VALUES
        ('$siswa_id', '$tahun_ajaran_id', '$kelas_id',
         '$tanggal_mulai', '$tanggal_selesai', '$status_aktif')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kelola_kelas.php");
    exit;
} else {
    echo "Gagal menambahkan data: " . mysqli_error($koneksi);
}
?>
