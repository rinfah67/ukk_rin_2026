
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Memeriksa hak akses admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Memastikan data dikirim melalui form POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_pelanggaran_kategori.php");
    exit;
}

// Mengambil data dari form
$nama = trim($_POST['nama'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$status_aktif = isset($_POST['status_aktif'])
    ? (int) $_POST['status_aktif']
    : 1;

// Memeriksa data wajib
if ($nama === '' || $deskripsi === '') {
    $_SESSION['pesan_error'] = "Nama dan deskripsi wajib diisi.";
    header("Location: tambah_pelanggaran_kategori.php");
    exit;
}

// Memeriksa status aktif
if (!in_array($status_aktif, [0, 1], true)) {
    $_SESSION['pesan_error'] = "Status kategori tidak valid.";
    header("Location: tambah_pelanggaran_kategori.php");
    exit;
}

// Memeriksa apakah nama kategori sudah terdaftar
$query = "SELECT id
          FROM t_pelanggaran_kategori
          WHERE nama = ?
          LIMIT 1";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $nama);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$kategori_ada = mysqli_num_rows($hasil) > 0;

mysqli_stmt_close($stmt);

if ($kategori_ada) {
    $_SESSION['pesan_error'] = "Nama kategori tersebut sudah terdaftar.";
    header("Location: tambah_pelanggaran_kategori.php");
    exit;
}

// Menambahkan kategori pelanggaran
$query = "INSERT INTO t_pelanggaran_kategori
          (nama, deskripsi, status_aktif)
          VALUES (?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $query);

if (!$stmt) {
    $_SESSION['pesan_error'] = "Query gagal disiapkan.";
    header("Location: tambah_pelanggaran_kategori.php");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ssi",
    $nama,
    $deskripsi,
    $status_aktif
);

// Menjalankan query
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    $_SESSION['pesan_sukses'] =
        "Kategori pelanggaran berhasil ditambahkan.";

    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}

// Jika gagal menyimpan
mysqli_stmt_close($stmt);

$_SESSION['pesan_error'] =
    "Gagal menambahkan kategori pelanggaran.";

header("Location: tambah_pelanggaran_kategori.php");
exit;
?>
