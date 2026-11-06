
<?php

session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Memastikan hanya admin yang dapat mengakses halaman
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak!");
}

// Memeriksa apakah ID tersedia
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID kelas tidak ditemukan!");
}

// Mengambil ID kelas
$id = (int) $_GET['id'];

// Menghapus data kelas dari tabel t_kelas
$sql = "DELETE FROM t_kelas WHERE id = ?";

$stmt = mysqli_prepare($koneksi, $sql);

// Memasukkan ID ke query
mysqli_stmt_bind_param($stmt, "i", $id);

// Menjalankan query
if (mysqli_stmt_execute($stmt)) {

    // Memeriksa apakah ada data yang berhasil dihapus
    if (mysqli_stmt_affected_rows($stmt) > 0) {

        $_SESSION['pesan_sukses'] = "Data kelas berhasil dihapus.";

    } else {

        $_SESSION['pesan_error'] = "Data kelas tidak ditemukan.";

    }

} else {

    // Menampilkan pesan jika gagal
    $_SESSION['pesan_error'] = "Gagal menghapus kelas. Kelas mungkin masih digunakan oleh data siswa atau wali kelas.";

}

// Menutup statement
mysqli_stmt_close($stmt);

// Kembali ke halaman kelola kelas
header("Location: kelola_kelas.php");
exit;

?>
