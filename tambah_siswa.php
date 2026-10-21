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

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Siswa</title>
</head>

<body>

<h1>Tambah Siswa</h1>

<a href="kelola_siswa.php">
    Kembali ke Kelola Siswa
</a>

<hr>

<form action="proses_tambah_siswa.php" method="POST">

    <p>
        NIS<br>
        <input type="text" name="nis">
    </p>

    <p>
        NISN<br>
        <input type="text" name="nisn" required>
    </p>

    <p>
        Nama<br>
        <input type="text" name="nama">
    </p>

    <p>
        Jenis Kelamin<br>

        <select name="jenis_kelamin" required>

            <option value="">
                -- Pilih --
            </option>

            <option value="L">
                Laki-laki
            </option>

            <option value="P">
                Perempuan
            </option>

        </select>

    </p>

    <p>
        Tanggal Lahir<br>
        <input type="date" name="tanggal_lahir" required>
    </p>

    <p>
        Alamat<br>

        <textarea
            name="alamat"
            rows="4"
            cols="30"
            required
        ></textarea>

    </p>

    <p>
        Status<br>

        <select name="status_aktif" required>

            <option value="1">
                Aktif
            </option>

            <option value="0">
                Tidak Aktif
            </option>

        </select>

    </p>

    <button type="submit" name="simpan">
        Simpan Siswa
    </button>

</form>

</body>
</html>