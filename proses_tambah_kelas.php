
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin yang boleh menambah kelas
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_kelas.php");
    exit;
}

// Ambil data dari form
$nama = trim($_POST['nama'] ?? '');
$tingkat = trim($_POST['tingkat'] ?? '');
$jurusan = trim($_POST['jurusan'] ?? '');
$status_aktif = isset($_POST['status_aktif'])
    ? (int) $_POST['status_aktif']
    : 1;

// Validasi
if ($nama === '' || $tingkat === '' || $jurusan === '') {
    $_SESSION['pesan_error'] = "Nama kelas, tingkat, dan jurusan wajib diisi.";
    header("Location: tambah_kelas.php");
    exit;
}

if (!in_array($status_aktif, [0, 1], true)) {
    $_SESSION['pesan_error'] = "Status kelas tidak valid.";
    header("Location: tambah_kelas.php");
    exit;
}

// Cek apakah nama kelas sudah ada
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_kelas WHERE nama = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $nama);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) > 0) {
    mysqli_stmt_close($stmt);
    $_SESSION['pesan_error'] = "Nama kelas sudah terdaftar.";
    header("Location: tambah_kelas.php");
    exit;
}
mysqli_stmt_close($stmt);

// Simpan data kelas
$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO t_kelas (nama, tingkat, jurusan, status_aktif)
     VALUES (?, ?, ?, ?)"
);
mysqli_stmt_bind_param(
    $stmt,
    "sssi",
    $nama,
    $tingkat,
    $jurusan,
    $status_aktif
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = "Data kelas berhasil ditambahkan.";
    mysqli_stmt_close($stmt);
    header("Location: kelola_kelas.php");
    exit;
}

mysqli_stmt_close($stmt);
$_SESSION['pesan_error'] = "Data kelas gagal ditambahkan.";
header("Location: tambah_kelas.php");
exit;
