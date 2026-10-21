
<?php
session_start(); // Memulai session login
include 'config/koneksi.php'; // Menghubungkan ke database

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Memeriksa hak akses admin
if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

// Memastikan data dikirim melalui form POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: tambah_pelanggaran_kategori.php");
    exit;
}

// Mengambil dan membersihkan data dari form
$nama = trim($_POST['nama']);
$deskripsi = trim($_POST['deskripsi']);
$status_aktif = (int) $_POST['status_aktif'];

// Memeriksa apakah nama dan deskripsi sudah diisi
if ($nama == '' || $deskripsi == '') {
    die("Nama dan deskripsi wajib diisi.");
}

// Menyiapkan query untuk menambahkan kategori
$query = "INSERT INTO t_pelanggaran_kategori
          (nama, deskripsi, status_aktif)
          VALUES (?, ?, ?)";

// Menyiapkan query agar data lebih aman
$stmt = mysqli_prepare($koneksi, $query);

// Memasukkan nilai ke dalam query
mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $nama,
    $deskripsi,
    $status_aktif
);

// Menjalankan query untuk menyimpan data
if (mysqli_stmt_execute($stmt)) {
    // Kembali ke halaman daftar jika berhasil
    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}

// Menampilkan pesan jika penyimpanan gagal
echo "Gagal menambahkan kategori: " . mysqli_error($koneksi);
?>
