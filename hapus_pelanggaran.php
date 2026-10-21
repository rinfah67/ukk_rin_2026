
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

// Penghapusan hanya boleh melalui form POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: catatan_pelanggaran.php");
    exit;
}

$user_id = (int)($_SESSION['id_user'] ?? 0);

// Cari ID guru berdasarkan akun pengguna
$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$guru) {
    die("Data guru tidak ditemukan.");
}

$guru_id = (int)$guru['id'];
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    die("ID catatan tidak valid.");
}

// Hapus catatan milik guru yang sedang login
$stmt = mysqli_prepare($koneksi, "
    DELETE FROM t_pelanggaran_siswa
    WHERE id = ? AND guru_id = ?
");
mysqli_stmt_bind_param($stmt, "ii", $id, $guru_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        header("Location: catatan_pelanggaran.php?pesan=" . urlencode("Catatan berhasil dihapus."));
        exit;
    }

    die("Catatan tidak ditemukan atau bukan milik akun kamu.");
}

die("Gagal menghapus catatan: " . mysqli_stmt_error($stmt));
?>
