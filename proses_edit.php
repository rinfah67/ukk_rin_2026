<?php

session_start();

include 'config/koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// CEK ROLE
if ($_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

// CEK FORM
if (!isset($_POST['edit'])) {
    header("Location: kelola_kelas.php");
    exit;
}


// AMBIL DATA
$id = $_POST['id'];
$siswa_id = $_POST['siswa_id'];
$tahun_ajaran_id = $_POST['tahun_ajaran_id'];
$kelas_id = $_POST['kelas_id'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = $_POST['status_aktif'];


// UPDATE DATA
$sql = "UPDATE t_kelas_siswa
        SET
            siswa_id='$siswa_id',
            tahun_ajaran_id='$tahun_ajaran_id',
            kelas_id='$kelas_id',
            tanggal_mulai='$tanggal_mulai',
            tanggal_selesai='$tanggal_selesai',
            status_aktif='$status_aktif',
            updated_at=CURRENT_TIMESTAMP
        WHERE id='$id'";


if (mysqli_query($koneksi, $sql)) {

    header("Location: kelola_kelas.php");
    exit;

} else {

    echo "Gagal mengubah data kelas: "
         . mysqli_error($koneksi);

}

?>