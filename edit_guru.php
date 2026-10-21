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

$sql = "SELECT *
        FROM t_guru
        WHERE id='$id'";

$hasil = mysqli_query($koneksi, $sql);

$guru = mysqli_fetch_assoc($hasil);

if (!$guru) {
    echo "Data guru tidak ditemukan!";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Guru</title>
</head>

<body>

<h1>Edit Guru</h1>

<a href="kelola_guru.php">
    Kembali ke Kelola Guru
</a>

<hr>

<form action="proses_edit_guru.php" method="POST">

    <input
        type="hidden"
        name="id"
        value="<?php echo $guru['id']; ?>"
    >

    <p>
        NIP
        <br>
        <input
            type="text"
            name="nip"
            value="<?php echo $guru['nip']; ?>"
            required
        >
    </p>

    <p>
        Nama Guru
        <br>
        <input
            type="text"
            name="nama"
            value="<?php echo $guru['nama']; ?>"
            required
        >
    </p>

    <p>
        Email
        <br>
        <input
            type="email"
            name="email"
            value="<?php echo $guru['email']; ?>"
            required
        >
    </p>

    <p>
        Status
        <br>

        <select name="status_aktif">

            <option
                value="1"
                <?php
                if ($guru['status_aktif'] == 1) {
                    echo "selected";
                }
                ?>
            >
                Aktif
            </option>

            <option
                value="0"
                <?php
                if ($guru['status_aktif'] == 0) {
                    echo "selected";
                }
                ?>
            >
                Tidak Aktif
            </option>

        </select>

    </p>

    <button type="submit" name="edit">
        Simpan Perubahan
    </button>

</form>

</body>

</html>