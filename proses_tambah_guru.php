
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin yang boleh menambahkan guru
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak!");
}

// Mengambil data dari form
$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
$status = $_POST['status'] ?? '';

// Validasi data wajib
if (
    $nip == '' ||
    $nama == '' ||
    $email == '' ||
    $password == '' ||
    $konfirmasi_password == '' ||
    $status == ''
) {
    die("Semua data wajib diisi.");
}

// Validasi NIP maksimal 10 digit
if (!preg_match('/^[0-9]{1,10}$/', $nip)) {
    die("NIP harus berupa angka maksimal 10 digit.");
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

// Validasi status
if (!in_array($status, ['1', '0'], true)) {
    die("Status guru tidak valid.");
}

// Cek NIP sudah terdaftar
$stmt = mysqli_prepare($koneksi,
    "SELECT id FROM t_guru WHERE nip = ?"
);

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
$stmt = mysqli_prepare($koneksi,
    "SELECT id FROM t_users WHERE email = ?"
);

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

// Enkripsi password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Mulai transaksi database
mysqli_begin_transaction($koneksi);

try {

    // 1. Simpan akun pengguna
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO t_users (name, email, password, role)
         VALUES (?, ?, ?, 'guru')"
    );

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

    // Ambil ID akun yang baru dibuat
    $user_id = mysqli_insert_id($koneksi);

    mysqli_stmt_close($stmt);

    // 2. Simpan data guru
    $stmt = mysqli_prepare($koneksi,
        "INSERT INTO t_guru (user_id, nip, nama, status_aktif)
         VALUES (?, ?, ?, ?)"
    );

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

    // 3. Simpan semua perubahan
    mysqli_commit($koneksi);

    header("Location: kelola_guru.php?pesan=berhasil");
    exit;

} catch (Exception $e) {

    // Batalkan jika terjadi kesalahan
    mysqli_rollback($koneksi);

    die("Gagal menambahkan guru: " . $e->getMessage());
}
?>
