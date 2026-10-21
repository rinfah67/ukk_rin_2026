
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
    die("ID tahun ajaran tidak ditemukan!");
}

$id = (int) $_GET['id'];

$sql = "SELECT * FROM t_tahun_ajaran WHERE id='$id'";
$hasil = mysqli_query($koneksi, $sql);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
    die("Data tahun ajaran tidak ditemukan!");
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Tahun Ajaran</title>
</head>
<body>

<h1>Edit Tahun Ajaran</h1>

<a href="kelola_tahun_ajaran.php">Kembali</a>
<hr>

<form action="proses_edit_tahun_ajaran.php" method="POST">

    <input type="hidden" name="id"
           value="<?php echo $data['id']; ?>">

    <p>Nama Tahun Ajaran</p>
    <input type="text" name="nama"
           value="<?php echo htmlspecialchars($data['nama']); ?>"
           required>

    <p>Tanggal Mulai</p>
    <input type="date" name="tanggal_mulai"
           value="<?php echo $data['tanggal_mulai']; ?>" required>

    <p>Tanggal Selesai</p>
    <input type="date" name="tanggal_selesai"
           value="<?php echo $data['tanggal_selesai']; ?>" required>

    <p>Status</p>
    <select name="status_aktif" required>
        <option value="1"
            <?php if ($data['status_aktif'] == 1) echo 'selected'; ?>>
            Aktif
        </option>
        <option value="0"
            <?php if ($data['status_aktif'] == 0) echo 'selected'; ?>>
            Tidak Aktif
        </option>
    </select>

    <p>Created At: <?php echo $data['created_at']; ?></p>
    <p>Updated At: <?php echo $data['updated_at']; ?></p>

    <button type="submit" name="edit">Simpan Perubahan</button>

</form>

</body>
</html>

