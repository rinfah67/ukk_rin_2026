<?php

session_start();

include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID guru tidak ditemukan!";
    exit;
}

$id = $_GET['id'];

// CARI GURU DI t_guru
$sql = "SELECT *
        FROM t_guru
        WHERE id='$id'";

$hasil = mysqli_query($koneksi, $sql);

$guru = mysqli_fetch_assoc($hasil);

if (!$guru) {
    echo "Data guru tidak ditemukan!";
    exit;
}

// HAPUS DARI t_guru
$sql = "DELETE FROM t_guru
        WHERE id='$id'";

if (mysqli_query($koneksi, $sql)) {

    // HAPUS AKUN DI t_users
    $user_id = $guru['user_id'];

    $sql_user = "DELETE FROM t_users
                 WHERE id='$user_id'
                 AND role='guru'";

    mysqli_query($koneksi, $sql_user);

    header("Location: kelola_guru.php");
    exit;

} else {

    echo "Gagal menghapus guru: "
         . mysqli_error($koneksi);

}

?>