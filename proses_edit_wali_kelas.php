
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
    header("Location: kelola_walikelas.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_wali_kelas WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data wali kelas tidak ditemukan.");
}

$guru = mysqli_query($koneksi, "SELECT id, nama FROM t_guru ORDER BY nama");
$kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas ORDER BY nama");
$tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Wali Kelas</title>
</head>
<body>

<h2>Edit Wali Kelas</h2>

<form action="proses_edit_walikelas.php" method="POST">

    <input type="hidden" name="id" value="<?= $data['id']; ?>">

    <label>Guru</label><br>
    <select name="guru_id" required>
        <?php while ($g = mysqli_fetch_assoc($guru)) { ?>
            <option value="<?= $g['id']; ?>"
                <?= $g['id'] == $data['guru_id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($g['nama']); ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <label>Kelas</label><br>
    <select name="kelas_id" required>
        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
            <option value="<?= $k['id']; ?>"
                <?= $k['id'] == $data['kelas_id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($k['nama'] . ' - ' .
                    $k['tingkat'] . ' ' . $k['jurusan']); ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <label>Tahun Ajaran</label><br>
    <select name="tahun_ajaran_id" required>
        <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
            <option value="<?= $ta['id']; ?>"
                <?= $ta['id'] == $data['tahun_ajaran_id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($ta['nama']); ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <label>Tanggal Mulai</label><br>
    <input type="date" name="tanggal_mulai"
           value="<?= $data['tanggal_mulai']; ?>" required>

    <br><br>

    <label>Tanggal Selesai</label><br>
    <input type="date" name="tanggal_selesai"
           value="<?= $data['tanggal_selesai']; ?>" required>

    <br><br>

    <label>Status</label><br>
    <select name="status_aktif" required>
        <option value="1"
            <?= $data['status_aktif'] == 1 ? 'selected' : ''; ?>>
            Aktif
        </option>
        <option value="0"
            <?= $data['status_aktif'] == 0 ? 'selected' : ''; ?>>
            Tidak Aktif
        </option>
    </select>

    <br><br>

    <button type="submit">Simpan Perubahan</button>
    <a href="kelola_walikelas.php">Kembali</a>

</form>

</body>
</html>
