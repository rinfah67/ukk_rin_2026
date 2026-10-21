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

// SIMPAN DATA GURU
if (isset($_POST['simpan'])) {

    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // HASH PASSWORD
    $password_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // SIMPAN AKUN KE t_users
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

    if (mysqli_query($koneksi, $sql_user)) {

        // AMBIL ID USER
        $user_id = mysqli_insert_id($koneksi);

        // SIMPAN DATA KE t_guru
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
                        1,
                        '$user_id'
                    )";

        if (mysqli_query($koneksi, $sql_guru)) {

            header("Location: kelola_guru.php");
            exit;

        } else {

            echo "Gagal menyimpan data guru: "
                 . mysqli_error($koneksi);

        }

    } else {

        echo "Gagal membuat akun guru: "
             . mysqli_error($koneksi);

    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Tambah Guru</title>

</head>

<body>

<h1>Tambah Guru</h1>

<a href="kelola_guru.php">
    Kembali ke Kelola Guru
</a>

<hr>

<form method="POST">

    <table>

        <tr>
            <td>NIP</td>
            <td>:</td>
            <td>
                <input
                    type="text"
                    name="nip"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>Nama Guru</td>
            <td>:</td>
            <td>
                <input
                    type="text"
                    name="nama"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>Email</td>
            <td>:</td>
            <td>
                <input
                    type="email"
                    name="email"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>Password</td>
            <td>:</td>
            <td>
                <input
                    type="password"
                    name="password"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>Status</td>
            <td>:</td>
            <td>
                <select name="status_aktif">

                    <option value="1">
                        Aktif
                    </option>

                    <option value="0">
                        Tidak Aktif
                    </option>

                </select>
            </td>
        </tr>

        <tr>
            <td></td>
            <td></td>
            <td>
                <button
                    type="submit"
                    name="simpan"
                >
                    Simpan Guru
                </button>
            </td>
        </tr>

    </table>

</form>

</body>

</html>