
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
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

$id = (int) $_POST['id'];
$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = (int) $_POST['status_aktif'];

if ($tanggal_selesai < $tanggal_mulai) {
    die("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

$sql = "UPDATE t_tahun_ajaran SET
            nama='$nama',
            tanggal_mulai='$tanggal_mulai',
            tanggal_selesai='$tanggal_selesai',
            status_aktif='$status_aktif',
            updated_at=CURRENT_TIMESTAMP
        WHERE id='$id'";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
} else {
    echo "Gagal mengubah tahun ajaran: " . mysqli_error($koneksi);
}

?>
