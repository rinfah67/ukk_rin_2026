
<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

$guru = mysqli_query($koneksi, "SELECT id, nama FROM t_guru ORDER BY nama");
$kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas ORDER BY nama");
$tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Wali Kelas</title>
</head>
<body>

<h2>Tambah Wali Kelas</h2>

<form action="proses_tambah_walikelas.php" method="POST">

    <label>Guru</label><br>
    <select name="guru_id" required>
        <option value="">-- Pilih Guru --</option>
        <?php while ($g = mysqli_fetch_assoc($guru)) { ?>
            <option value="<?= $g['id']; ?>">
                <?= htmlspecialchars($g['nama']); ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <label>Kelas</label><br>
    <select name="kelas_id" required>
        <option value="">-- Pilih Kelas --</option>
        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
            <option value="<?= $k['id']; ?>">
                <?= htmlspecialchars($k['nama'] . ' - ' .
                    $k['tingkat'] . ' ' . $k['jurusan']); ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <label>Tahun Ajaran</label><br>
    <select name="tahun_ajaran_id" required>
        <option value="">-- Pilih Tahun Ajaran --</option>
        <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
            <option value="<?= $ta['id']; ?>">
                <?= htmlspecialchars($ta['nama']); ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <label>Tanggal Mulai</label><br>
    <input type="date" name="tanggal_mulai" required>

    <br><br>

    <label>Tanggal Selesai</label><br>
    <input type="date" name="tanggal_selesai" required>

    <br><br>

    <label>Status</label><br>
    <select name="status_aktif" required>
        <option value="1">Aktif</option>
        <option value="0">Tidak Aktif</option>
    </select>

    <br><br>

    <button type="submit">Simpan</button>
    <a href="kelola_walikelas.php">Kembali</a>

</form>

</body>
</html>
