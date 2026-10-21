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
    echo "ID siswa tidak ditemukan!";
    exit;
}

$id = $_GET['id'];

$sql = "SELECT *
        FROM t_siswa
        WHERE id='$id'";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

$siswa = mysqli_fetch_assoc($hasil);

if (!$siswa) {
    echo "Data siswa tidak ditemukan!";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Siswa</title>
</head>

<body>

<h1>Edit Siswa</h1>

<a href="kelola_siswa.php">
    Kembali ke Kelola Siswa
</a>

<hr>

<form action="proses_edit_siswa.php" method="POST">

    <input
        type="hidden"
        name="id"
        value="<?php echo $siswa['id']; ?>"
    >

    <p>
        NIS<br>
        <input
            type="text"
            name="nis"
            value="<?php echo $siswa['nis']; ?>"
        >
    </p>

    <p>
        NISN<br>
        <input
            type="text"
            name="nisn"
            value="<?php echo $siswa['nisn']; ?>"
            required
        >
    </p>

    <p>
        Nama<br>
        <input
            type="text"
            name="nama"
            value="<?php echo $siswa['nama']; ?>"
        >
    </p>

    <p>
        Jenis Kelamin<br>

        <select name="jenis_kelamin" required>

            <option
                value="L"
                <?php
                if ($siswa['jenis_kelamin'] == 'L') {
                    echo "selected";
                }
                ?>
            >
                Laki-laki
            </option>

            <option
                value="P"
                <?php
                if ($siswa['jenis_kelamin'] == 'P') {
                    echo "selected";
                }
                ?>
            >
                Perempuan
            </option>

        </select>

    </p>

    <p>
        Tanggal Lahir<br>

        <input
            type="date"
            name="tanggal_lahir"
            value="<?php echo $siswa['tanggal_lahir']; ?>"
            required
        >

    </p>

    <p>
        Alamat<br>

        <textarea
            name="alamat"
            rows="4"
            cols="30"
            required
        ><?php echo $siswa['alamat']; ?></textarea>

    </p>

    <p>
        Status<br>

        <select name="status_aktif">

            <option
                value="1"
                <?php
                if ($siswa['status_aktif'] == 1) {
                    echo "selected";
                }
                ?>
            >
                Aktif
            </option>

            <option
                value="0"
                <?php
                if ($siswa['status_aktif'] == 0) {
                    echo "selected";
                }
                ?>
            >
                Tidak Aktif
            </option>

        </select>

    </p>

    <p>
        Created At:
        <?php echo $siswa['created_at']; ?>
    </p>

    <p>
        Updated At:
        <?php echo $siswa['updated_at']; ?>
    </p>

    <button type="submit" name="edit">
        Simpan Perubahan
    </button>

</form>

</body>
</html>