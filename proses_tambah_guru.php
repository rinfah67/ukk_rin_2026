
<?php
// proses_tambah_guru.php

session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die("Akses ditolak!");
}

// Memastikan request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_guru.php");
    exit;
}

// Mengambil data dari form
$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
$status = $_POST['status_aktif'] ?? '';

// Validasi data wajib
if (
    $nip === '' ||
    $nama === '' ||
    $email === '' ||
    $password === '' ||
    $konfirmasi_password === '' ||
    !in_array((string)$status, ['0', '1'], true)
) {
    die("Semua data wajib diisi.");
}

// Validasi NIP maksimal 18 digit
if (!preg_match('/^[0-9]{1,18}$/', $nip)) {
    die("NIP harus berupa angka maksimal 18 digit.");
}

// Validasi nama
if (strlen($nama) > 255) {
    die("Nama guru terlalu panjang.");
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Format email tidak valid.");
}

// Validasi password
if (strlen($password) < 6) {
    die("Password minimal 6 karakter.");
}

// Validasi konfirmasi password
if ($password !== $konfirmasi_password) {
    die("Konfirmasi password tidak sama.");
}

// Cek NIP sudah terdaftar
$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM t_guru
    WHERE nip = ?
");

if (!$stmt) {
    die("Query NIP gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "s", $nip);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) > 0) {
    mysqli_stmt_close($stmt);
    die("NIP sudah terdaftar.");
}

mysqli_stmt_close($stmt);

// Cek email sudah terdaftar
$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM t_users
    WHERE email = ?
");

if (!$stmt) {
    die("Query email gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) > 0) {
    mysqli_stmt_close($stmt);
    die("Email sudah terdaftar.");
}

mysqli_stmt_close($stmt);

// Mengenkripsi password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Memulai transaksi database
mysqli_begin_transaction($koneksi);

try {

    // 1. Menyimpan akun pengguna ke t_users
    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO t_users
        (name, email, password, role)
        VALUES (?, ?, ?, 'guru')
    ");

    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $nama,
        $email,
        $password_hash
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    // Mengambil ID akun pengguna
    $user_id = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmt);

    // 2. Menyimpan data guru ke t_guru
    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO t_guru
        (user_id, nip, nama, status_aktif)
        VALUES (?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "issi",
        $user_id,
        $nip,
        $nama,
        $status
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    // 3. Menyimpan transaksi
    mysqli_commit($koneksi);

    // Kembali ke halaman kelola guru
    header("Location: kelola_guru.php?pesan=berhasil");
    exit;

} catch (Exception $e) {

    // Membatalkan transaksi jika gagal
    mysqli_rollback($koneksi);

    die("Gagal menambahkan guru: " . $e->getMessage());
}
?>
