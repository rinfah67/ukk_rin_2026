
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// =====================================
// CEK SESSION LOGIN
// =====================================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// =====================================
// CEK AKSES ADMIN
// =====================================
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

// =====================================
// CEK METHOD POST
// =====================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_guru.php");
    exit;
}

// =====================================
// AMBIL DATA FORM
// =====================================
$nip = trim($_POST['nip'] ?? '');
$jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
$status = (int)($_POST['status_aktif'] ?? 1);

// =====================================
// VALIDASI DATA
// =====================================
if (
    $nip === '' ||
    $jenis_kelamin === '' ||
    $nama === '' ||
    $email === '' ||
    $password === '' ||
    $konfirmasi_password === ''
) {
    exit("Semua data wajib diisi.");
}

if (!preg_match('/^[0-9]{1,18}$/', $nip)) {
    exit("NIP harus berupa angka maksimal 18 digit.");
}

if (!in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'], true)) {
    exit("Jenis kelamin tidak valid.");
}

if (mb_strlen($nama) > 150) {
    exit("Nama maksimal 150 karakter.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Format email tidak valid.");
}

if (strlen($password) < 6) {
    exit("Password minimal 6 karakter.");
}

if ($password !== $konfirmasi_password) {
    exit("Konfirmasi password tidak cocok.");
}

if (!in_array($status, [0, 1], true)) {
    exit("Status tidak valid.");
}

// =====================================
// CEK NIP DUPLIKAT
// =====================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_guru WHERE nip = ? LIMIT 1"
);

if (!$stmt) {
    exit("Gagal memeriksa NIP: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "s", $nip);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) > 0) {
    mysqli_stmt_close($stmt);
    exit("NIP sudah terdaftar.");
}

mysqli_stmt_close($stmt);

// =====================================
// CEK EMAIL DUPLIKAT
// =====================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_users WHERE email = ? LIMIT 1"
);

if (!$stmt) {
    exit("Gagal memeriksa email: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) > 0) {
    mysqli_stmt_close($stmt);
    exit("Email sudah digunakan.");
}

mysqli_stmt_close($stmt);

// =====================================
// HASH PASSWORD
// =====================================
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$remember_token = bin2hex(random_bytes(25));

// =====================================
// MULAI TRANSAKSI
// =====================================
mysqli_begin_transaction($koneksi);

try {

    // SIMPAN AKUN LOGIN
    $sql_user = "INSERT INTO t_users
        (name, email, password, remember_token, role)
        VALUES (?, ?, ?, ?, 'guru')";

    $stmt = mysqli_prepare($koneksi, $sql_user);

    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $nama,
        $email,
        $password_hash,
        $remember_token
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    $user_id = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    // SIMPAN DATA GURU
    $sql_guru = "INSERT INTO t_guru
        (nip, jenis_kelamin, nama, email, status_aktif, user_id)
        VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($koneksi, $sql_guru);

    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssii",
        $nip,
        $jenis_kelamin,
        $nama,
        $email,
        $status,
        $user_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    // SIMPAN PERUBAHAN
    mysqli_commit($koneksi);

    header("Location: kelola_guru.php?pesan=berhasil");
    exit;

} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    http_response_code(500);

    echo "<h3>Gagal Menambahkan Guru</h3>";
    echo "<p>" .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') .
        "</p>";
    echo '<a href="tambah_guru.php">Kembali</a>';
    exit;
}
?>
