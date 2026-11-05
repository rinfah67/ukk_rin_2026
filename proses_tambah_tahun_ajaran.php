
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tambah_tahun_ajaran.php");
    exit;
}

// Ambil data dari form
$nama = trim($_POST['nama'] ?? '');
$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = isset($_POST['status_aktif'])
    ? (int) $_POST['status_aktif']
    : 1;

// Validasi data wajib
if ($nama === '' || $tanggal_mulai === '' || $tanggal_selesai === '') {
    $_SESSION['pesan_error'] = "Semua data tahun ajaran wajib diisi.";
    header("Location: tambah_tahun_ajaran.php");
    exit;
}

// Validasi format tanggal
$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (
    !$mulai || $mulai->format('Y-m-d') !== $tanggal_mulai ||
    !$selesai || $selesai->format('Y-m-d') !== $tanggal_selesai
) {
    $_SESSION['pesan_error'] = "Format tanggal tidak valid.";
    header("Location: tambah_tahun_ajaran.php");
    exit;
}

// Validasi tanggal selesai
if ($tanggal_selesai < $tanggal_mulai) {
    $_SESSION['pesan_error'] = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    header("Location: tambah_tahun_ajaran.php");
    exit;
}

// Validasi status aktif
if (!in_array($status_aktif, [0, 1], true)) {
    $_SESSION['pesan_error'] = "Status tahun ajaran tidak valid.";
    header("Location: tambah_tahun_ajaran.php");
    exit;
}

// Cek apakah nama tahun ajaran sudah terdaftar
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran WHERE nama = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $nama);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($hasil) > 0) {
    mysqli_stmt_close($stmt);

    $_SESSION['pesan_error'] = "Tahun ajaran tersebut sudah terdaftar.";
    header("Location: tambah_tahun_ajaran.php");
    exit;
}

mysqli_stmt_close($stmt);

// Cek apakah sudah ada tahun ajaran yang aktif
if ($status_aktif === 1) {

    $cek_aktif = mysqli_query(
        $koneksi,
        "SELECT id FROM t_tahun_ajaran
         WHERE status_aktif = 1
         LIMIT 1"
    );

    if (!$cek_aktif) {
        $_SESSION['pesan_error'] = "Gagal memeriksa status tahun ajaran.";
        header("Location: tambah_tahun_ajaran.php");
        exit;
    }

    if (mysqli_num_rows($cek_aktif) > 0) {
        $_SESSION['pesan_error'] =
            "Tidak dapat menambahkan tahun ajaran aktif karena sudah ada tahun ajaran lain yang aktif.";

        header("Location: kelola_tahun_ajaran.php");
        exit;
    }
}

// Simpan data tahun ajaran
$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO t_tahun_ajaran
        (nama, tanggal_mulai, tanggal_selesai, status_aktif)
     VALUES (?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "sssi",
    $nama,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif
);

// Jalankan proses penyimpanan
if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    $_SESSION['pesan_sukses'] = "Tahun ajaran berhasil ditambahkan.";

    header("Location: kelola_tahun_ajaran.php");
    exit;
}

// Jika gagal menyimpan
mysqli_stmt_close($stmt);

$_SESSION['pesan_error'] = "Tahun ajaran gagal ditambahkan.";

header("Location: tambah_tahun_ajaran.php");
exit;
?>
