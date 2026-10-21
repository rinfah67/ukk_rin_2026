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


// AMBIL DATA
$sql = "SELECT *
        FROM t_kelas_siswa
        WHERE id='$id'";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

$kelas = mysqli_fetch_assoc($hasil);

if (!$kelas) {
    echo "Data kelas tidak ditemukan!";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Kelas</title>
</head>

<body>

<h1>Edit Kelas</h1>

<a href="kelola_kelas.php">
    Kembali ke Kelola Kelas
</a>

<hr>

<form action="proses_edit_kelas.php" method="POST">

    <input
        type="hidden"
        name="id"
        value="<?php echo $kelas['id']; ?>"
    >

    <table>

        <tr>
            <td>ID Siswa</td>
            <td>:</td>
            <td>

                <input
                    type="number"
                    name="siswa_id"
                    value="<?php echo $kelas['siswa_id']; ?>"
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
                    value="<?php echo $kelas['tahun_ajaran_id']; ?>"
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
                    value="<?php echo $kelas['kelas_id']; ?>"
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
                    value="<?php echo $kelas['tanggal_mulai']; ?>"
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
                    value="<?php echo $kelas['tanggal_selesai']; ?>"
                    required
                >

            </td>
        </tr>

        <tr>
            <td>Status</td>
            <td>:</td>
            <td>

                <select name="status_aktif">

                    <option
                        value="1"
                        <?php
                        if ($kelas['status_aktif'] == 1) {
                            echo "selected";
                        }
                        ?>
                    >
                        Aktif
                    </option>

                    <option
                        value="0"
                        <?php
                        if ($kelas['status_aktif'] == 0) {
                            echo "selected";
                        }
                        ?>
                    >
                        Tidak Aktif
                    </option>

                </select>

            </td>
        </tr>

        <tr>
            <td>Created At</td>
            <td>:</td>
            <td>
                <?php echo $kelas['created_at']; ?>
            </td>
        </tr>

        <tr>
            <td>Updated At</td>
            <td>:</td>
            <td>
                <?php echo $kelas['updated_at']; ?>
            </td>
        </tr>

        <tr>
            <td></td>
            <td></td>
            <td>

                <button
                    type="submit"
                    name="edit"
                >
                    Simpan Perubahan
                </button>

            </td>
        </tr>

    </table>

</form>

</body>

</html>