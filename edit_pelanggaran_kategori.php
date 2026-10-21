
<?php
session_start(); // Memulai session login
include 'config/koneksi.php'; // Menghubungkan ke database

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Memastikan hanya admin yang dapat mengedit
if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

// Memeriksa apakah ID kategori tersedia di URL
if (!isset($_GET['id'])) {
    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}

// Mengubah ID menjadi bilangan bulat
$id = (int) $_GET['id'];

// Menyiapkan query untuk mengambil kategori berdasarkan ID
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_pelanggaran_kategori WHERE id = ?"
);

// Memasukkan ID ke dalam query
mysqli_stmt_bind_param($stmt, "i", $id);

// Menjalankan query
mysqli_stmt_execute($stmt);

// Mengambil hasil query
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Memeriksa apakah kategori ditemukan
if (!$data) {
    die("Data kategori tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori Pelanggaran</title>
</head>
<body>

<h2>Edit Kategori Pelanggaran</h2>

<!-- Form untuk mengirim perubahan kategori -->
<form action="proses_edit_pelanggaran_kategori.php" method="POST">

    <!-- Mengirim ID kategori yang sedang diedit -->
    <input type="hidden" name="id" value="<?= $data['id']; ?>">

    <!-- Input nama kategori dengan nilai sebelumnya -->
    <label>Nama Kategori</label><br>
    <input type="text" name="nama" maxlength="100"
           value="<?= htmlspecialchars($data['nama']); ?>" required>

    <br><br>

    <!-- Input deskripsi dengan nilai sebelumnya -->
    <label>Deskripsi</label><br>
    <textarea name="deskripsi" rows="4" required><?= htmlspecialchars($data['deskripsi']); ?></textarea>

    <br><br>

    <!-- Menampilkan status yang tersimpan sebelumnya -->
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

    <!-- Tombol untuk menyimpan perubahan -->
    <button type="submit">Simpan Perubahan</button>

    <!-- Tombol kembali ke daftar kategori -->
    <a href="kelola_pelanggaran_kategori.php">Kembali</a>

</form>

</body>
</html>
