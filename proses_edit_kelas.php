
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

if (!isset($_POST['edit'])) {
    header("Location: kelola_kelas.php");
    exit;
}

$id = (int) $_POST['id'];
$siswa_id = (int) $_POST['siswa_id'];
$kelas_id = (int) $_POST['kelas_id'];
$tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = (int) $_POST['status_aktif'];

if ($tanggal_selesai < $tanggal_mulai) {
    die("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

$sql = "UPDATE t_kelas_siswa SET
            siswa_id='$siswa_id',
            kelas_id='$kelas_id',
            tahun_ajaran_id='$tahun_ajaran_id',
            tanggal_mulai='$tanggal_mulai',
            tanggal_selesai='$tanggal_selesai',
            status_aktif='$status_aktif',
            updated_at=CURRENT_TIMESTAMP
        WHERE id='$id'";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kelola_kelas.php");
    exit;
} else {
    echo "Gagal mengubah data: " . mysqli_error($koneksi);
}
?>
