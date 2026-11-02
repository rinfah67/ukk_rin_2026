
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Mengambil data form
$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
$status_aktif = $_POST['status'] ?? '';

// Validasi data
if (
    $nip == '' || $nama == '' || $email == '' ||
    $password == '' || $konfirmasi_password == ''
) {
    die("Semua data wajib diisi.");
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Format email tidak valid.");
}

// Validasi password
if ($password !== $konfirmasi_password) {
    die("Konfirmasi password tidak sesuai.");
}

if (strlen($password) < 6) {
    die("Password minimal 6 karakter.");
}

// Validasi status
if (!in_array($status_aktif, ['1', '0'], true)) {
    die("Status guru tidak valid.");
}

// Cek email
$stmt = mysqli_prepare($koneksi,
    "SELECT id FROM t_users WHERE email = ?"
);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) > 0) {
    die("Email sudah digunakan.");
}
mysqli_stmt_close($stmt);

// Cek NIP
$stmt = mysqli_prepare($koneksi,
    "SELECT id FROM t_guru WHERE nip = ?"
);
mysqli_stmt_bind_param($stmt, "s", $nip);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) > 0) {
    die("NIP sudah terdaftar.");
}
mysqli_stmt_close($stmt);

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {

    // 1. Membuat akun pengguna
    $role = "guru";

    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO t_users (name, email, password, role)
         VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt, "ssss",
        $nama, $email, $password_hash, $role
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($koneksi));
    }

    // Mengambil ID akun yang baru dibuat
    $user_id = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmt);

    // 2. Membuat data guru
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO t_guru
        (nip, nama, email, status_aktif, user_id)
        VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt, "sssii",
        $nip, $nama, $email, $status_aktif, $user_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_close($stmt);

    // 3. Simpan transaksi
    mysqli_commit($koneksi);

    header("Location: tambah_guru.php?pesan=berhasil");
    exit;

} catch (Exception $e) {

    mysqli_rollback($koneksi);

    die("Gagal menambahkan guru: " . $e->getMessage());
}
