
<?php
// proses_tambah_wali_kelas.php

session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die("Akses ditolak!");
}

// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_wali_kelas.php");
    exit;
}

// Ambil data dari form
$tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$guru_id = (int) ($_POST['guru_id'] ?? 0);
$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = isset($_POST['status_aktif'])
    ? (int) $_POST['status_aktif']
    : -1;

// Fungsi untuk kembali dengan pesan error
function kembaliDenganError($pesan) {
    $_SESSION['pesan_error'] = $pesan;
    header("Location: tambah_wali_kelas.php");
    exit;
}

// Validasi data wajib
if (
    $tahun_ajaran_id <= 0 ||
    $kelas_id <= 0 ||
    $guru_id <= 0 ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === ''
) {
    kembaliDenganError("Semua data wali kelas wajib diisi.");
}

// Validasi status
if (!in_array($status_aktif, [0, 1], true)) {
    kembaliDenganError("Status wali kelas tidak valid.");
}

// Validasi format tanggal
$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (
    !$mulai || $mulai->format('Y-m-d') !== $tanggal_mulai ||
    !$selesai || $selesai->format('Y-m-d') !== $tanggal_selesai
) {
    kembaliDenganError("Format tanggal tidak valid.");
}

// Validasi tanggal selesai
if ($tanggal_selesai < $tanggal_mulai) {
    kembaliDenganError("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

// 1. Pastikan tahun ajaran tersedia dan aktif
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM t_tahun_ajaran
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) === 0) {
    mysqli_stmt_close($stmt);
    kembaliDenganError("Tahun ajaran tidak ditemukan atau tidak aktif.");
}

mysqli_stmt_close($stmt);

// 2. Pastikan kelas tersedia dan aktif
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM t_kelas
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $kelas_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) === 0) {
    mysqli_stmt_close($stmt);
    kembaliDenganError("Kelas tidak ditemukan atau tidak aktif.");
}

mysqli_stmt_close($stmt);

// 3. Pastikan guru tersedia dan aktif
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM t_guru
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) === 0) {
    mysqli_stmt_close($stmt);
    kembaliDenganError("Guru tidak ditemukan atau tidak aktif.");
}

mysqli_stmt_close($stmt);

// 4. Jika status wali kelas aktif, cek kelas dan guru
if ($status_aktif === 1) {

    // Cek apakah kelas sudah memiliki wali kelas aktif
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_wali_kelas
         WHERE kelas_id = ?
         AND status_aktif = 1
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $kelas_id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($hasil) > 0) {
        mysqli_stmt_close($stmt);
        kembaliDenganError("Kelas tersebut sudah memiliki wali kelas aktif.");
    }

    mysqli_stmt_close($stmt);

    // Cek apakah guru sudah menjadi wali kelas aktif
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_wali_kelas
         WHERE guru_id = ?
         AND status_aktif = 1
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $guru_id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($hasil) > 0) {
        mysqli_stmt_close($stmt);
        kembaliDenganError("Guru tersebut sudah menjadi wali kelas aktif.");
    }

    mysqli_stmt_close($stmt);
}

// 5. Simpan data wali kelas
$query = "INSERT INTO t_wali_kelas
          (
              tahun_ajaran_id,
              kelas_id,
              guru_id,
              tanggal_mulai,
              tanggal_selesai,
              status_aktif
          )
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $query);

if (!$stmt) {
    kembaliDenganError("Gagal menyiapkan penyimpanan data.");
}

mysqli_stmt_bind_param(
    $stmt,
    "iiissi",
    $tahun_ajaran_id,
    $kelas_id,
    $guru_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif
);

// Jalankan penyimpanan
if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    $_SESSION['pesan_sukses'] = "Wali kelas berhasil ditambahkan.";

    header("Location: kelola_wali_kelas.php");
    exit;
}

// Jika penyimpanan gagal
mysqli_stmt_close($stmt);

$_SESSION['pesan_error'] = "Gagal menyimpan data wali kelas.";

header("Location: tambah_wali_kelas.php");
exit;
?>
