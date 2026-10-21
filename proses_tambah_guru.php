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

// CEK TOMBOL SIMPAN
if (!isset($_POST['simpan'])) {
    header("Location: tambah_guru.php");
    exit;
}

// AMBIL DATA
$nip = $_POST['nip'];
$nama = $_POST['nama'];
$email = $_POST['email'];
$password = $_POST['password'];
$status_aktif = $_POST['status_aktif'];

// HASH PASSWORD
$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// ============================
// SIMPAN KE t_users
// ============================

$sql_user = "INSERT INTO t_users
            (
                name,
                email,
                password,
                role
            )
            VALUES
            (
                '$nama',
                '$email',
                '$password_hash',
                'guru'
            )";

if (!mysqli_query($koneksi, $sql_user)) {

    echo "Gagal menambahkan akun guru: "
         . mysqli_error($koneksi);

    exit;
}


// AMBIL ID USER
$user_id = mysqli_insert_id($koneksi);


// ============================
// SIMPAN KE t_guru
// ============================

$sql_guru = "INSERT INTO t_guru
            (
                nip,
                nama,
                email,
                status_aktif,
                user_id
            )
            VALUES
            (
                '$nip',
                '$nama',
                '$email',
                '$status_aktif',
                '$user_id'
            )";

if (mysqli_query($koneksi, $sql_guru)) {

    header("Location: kelola_guru.php");
    exit;

} else {

    echo "Gagal menambahkan data guru: "
         . mysqli_error($koneksi);

}

?>