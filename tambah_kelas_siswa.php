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

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Kelas</title>
</head>

<body>

<h1>Tambah Kelas</h1>

<a href="kelola_kelas.php">
    Kembali ke Kelola Kelas
</a>

<hr>

<form action="proses_tambah_kelas.php" method="POST">

    <table>

        <tr>
            <td>ID Siswa</td>
            <td>:</td>
            <td>
                <input
                    type="number"
                    name="siswa_id"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>ID Tahun Ajaran</td>
            <td>:</td>
            <td>
                <input
                    type="number"
                    name="tahun_ajaran_id"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>ID Kelas</td>
            <td>:</td>
            <td>
                <input
                    type="number"
                    name="kelas_id"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>Tanggal Mulai</td>
            <td>:</td>
            <td>
                <input
                    type="date"
                    name="tanggal_mulai"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>Tanggal Selesai</td>
            <td>:</td>
            <td>
                <input
                    type="date"
                    name="tanggal_selesai"
                    required
                >
            </td>
        </tr>

        <tr>
            <td>Status</td>
            <td>:</td>
            <td>

                <select name="status_aktif" required>

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
                    Simpan Kelas
                </button>

            </td>
        </tr>

    </table>

</form>

</body>

</html>