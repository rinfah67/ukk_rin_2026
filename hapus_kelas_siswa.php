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

// CEK ID
if (!isset($_GET['id'])) {
    echo "ID kelas tidak ditemukan!";
    exit;
}

$id = $_GET['id'];


// HAPUS DATA
$sql = "DELETE FROM t_kelas_siswa
        WHERE id='$id'";


if (mysqli_query($koneksi, $sql)) {

    header("Location: kelola_kelas.php");
    exit;

} else {

    echo "Gagal menghapus data kelas: "
         . mysqli_error($koneksi);

}

?>