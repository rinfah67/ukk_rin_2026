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
if (!isset($_POST['simpan'])) {
    header("Location: tambah_kelas.php");
    exit;
}


// AMBIL DATA
$siswa_id = $_POST['siswa_id'];
$tahun_ajaran_id = $_POST['tahun_ajaran_id'];
$kelas_id = $_POST['kelas_id'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = $_POST['status_aktif'];


// INSERT DATA
$sql = "INSERT INTO t_kelas_siswa
        (
            siswa_id,
            tahun_ajaran_id,
            kelas_id,
            tanggal_mulai,
            tanggal_selesai,
            status_aktif
        )
        VALUES
        (
            '$siswa_id',
            '$tahun_ajaran_id',
            '$kelas_id',
            '$tanggal_mulai',
            '$tanggal_selesai',
            '$status_aktif'
        )";


if (mysqli_query($koneksi, $sql)) {

    header("Location: kelola_kelas.php");
    exit;

} else {

    echo "Gagal menambahkan kelas: "
         . mysqli_error($koneksi);

}

?>