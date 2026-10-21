
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

$siswa = mysqli_query($koneksi,
    "SELECT id, nis, nama FROM t_siswa
     WHERE status_aktif=1 ORDER BY nama");

$kelas = mysqli_query($koneksi,
    "SELECT id, nama, tingkat, jurusan FROM t_kelas
     WHERE status_aktif=1 ORDER BY nama");

$tahun = mysqli_query($koneksi,
    "SELECT id, nama FROM t_tahun_ajaran
     WHERE status_aktif=1 ORDER BY tanggal_mulai DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kelas Siswa</title>
</head>
<body>

<h1>Tambah Kelas Siswa</h1>
<a href="kelola_kelas.php">Kembali ke Kelola Kelas</a>
<hr>

<form action="proses_tambah_kelas.php" method="POST">

    <p>Nama Siswa</p>
    <select name="siswa_id" required>
        <option value="">-- Pilih Siswa --</option>
        <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
            <option value="<?php echo $s['id']; ?>">
                <?php echo htmlspecialchars($s['nis'] . ' - ' . $s['nama']); ?>
            </option>
        <?php } ?>
    </select>

    <p>Kelas</p>
    <select name="kelas_id" required>
        <option value="">-- Pilih Kelas --</option>
        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
            <option value="<?php echo $k['id']; ?>">
                <?php
                echo htmlspecialchars(
                    $k['nama'] . ' - ' . $k['tingkat'] . ' ' . $k['jurusan']
                );
                ?>
            </option>
        <?php } ?>
    </select>

    <p>Tahun Ajaran</p>
    <select name="tahun_ajaran_id" required>
        <option value="">-- Pilih Tahun Ajaran --</option>
        <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
            <option value="<?php echo $ta['id']; ?>">
                <?php echo htmlspecialchars($ta['nama']); ?>
            </option>
        <?php } ?>
    </select>

    <p>Tanggal Mulai</p>
    <input type="date" name="tanggal_mulai" required>

    <p>Tanggal Selesai</p>
    <input type="date" name="tanggal_selesai" required>

    <p>Status</p>
    <select name="status_aktif" required>
        <option value="1">Aktif</option>
        <option value="0">Tidak Aktif</option>
    </select>

    <p><button type="submit" name="simpan">Simpan</button></p>
</form>

</body>
</html>
