
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

if (!isset($_GET['id'])) {
    die("ID data tidak ditemukan!");
}

$id = (int) $_GET['id'];

$hasil = mysqli_query($koneksi,
    "SELECT * FROM t_kelas_siswa WHERE id='$id'");

$data = mysqli_fetch_assoc($hasil);

if (!$data) {
    die("Data kelas siswa tidak ditemukan!");
}

$siswa = mysqli_query($koneksi,
    "SELECT id, nis, nama FROM t_siswa ORDER BY nama");

$kelas = mysqli_query($koneksi,
    "SELECT id, nama, tingkat, jurusan FROM t_kelas ORDER BY nama");

$tahun = mysqli_query($koneksi,
    "SELECT id, nama FROM t_tahun_ajaran ORDER BY tanggal_mulai DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kelas Siswa</title>
</head>
<body>

<h1>Edit Kelas Siswa</h1>
<a href="kelola_kelas.php">Kembali ke Kelola Kelas</a>
<hr>

<form action="proses_edit_kelas.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

    <p>Nama Siswa</p>
    <select name="siswa_id" required>
        <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
            <option value="<?php echo $s['id']; ?>"
                <?php if ($s['id'] == $data['siswa_id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($s['nis'] . ' - ' . $s['nama']); ?>
            </option>
        <?php } ?>
    </select>

    <p>Kelas</p>
    <select name="kelas_id" required>
        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
            <option value="<?php echo $k['id']; ?>"
                <?php if ($k['id'] == $data['kelas_id']) echo 'selected'; ?>>
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
        <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
            <option value="<?php echo $ta['id']; ?>"
                <?php if ($ta['id'] == $data['tahun_ajaran_id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($ta['nama']); ?>
            </option>
        <?php } ?>
    </select>

    <p>Tanggal Mulai</p>
    <input type="date" name="tanggal_mulai"
           value="<?php echo $data['tanggal_mulai']; ?>" required>

    <p>Tanggal Selesai</p>
    <input type="date" name="tanggal_selesai"
           value="<?php echo $data['tanggal_selesai']; ?>" required>

    <p>Status</p>
    <select name="status_aktif" required>
        <option value="1" <?php if ($data['status_aktif'] == 1) echo 'selected'; ?>>
            Aktif
        </option>
        <option value="0" <?php if ($data['status_aktif'] == 0) echo 'selected'; ?>>
            Tidak Aktif
        </option>
    </select>

    <p><button type="submit" name="edit">Simpan Perubahan</button></p>
</form>

</body>
</html>
