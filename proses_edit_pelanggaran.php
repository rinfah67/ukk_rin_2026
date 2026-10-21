
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

$user_id = (int)($_SESSION['id_user'] ?? 0);

// Cari guru yang sedang login
$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$guru) {
    die("Data guru tidak ditemukan.");
}

$guru_id = (int)$guru['id'];
$id = (int)($_POST['id'] ?? 0);
$tanggal = $_POST['tanggal'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');
$status = $_POST['status'] ?? '';

$status_valid = ['Belum diproses', 'Diproses', 'Selesai'];

if (
    $id <= 0 ||
    $tanggal === '' ||
    $keterangan === '' ||
    $tindakan === '' ||
    !in_array($status, $status_valid, true)
) {
    die("Data tidak lengkap atau tidak valid.");
}

// Validasi tanggal
$tanggal_obj = DateTime::createFromFormat('!Y-m-d', $tanggal);
if (!$tanggal_obj || $tanggal_obj->format('Y-m-d') !== $tanggal) {
    die("Tanggal tidak valid.");
}

// Update hanya catatan milik guru yang login
$stmt = mysqli_prepare($koneksi, "
    UPDATE t_pelanggaran_siswa
    SET tanggal = ?, keterangan = ?, tindakan = ?,
        status = ?, updated_at = NOW()
    WHERE id = ? AND guru_id = ?
");

mysqli_stmt_bind_param(
    $stmt,
    "ssssii",
    $tanggal,
    $keterangan,
    $tindakan,
    $status,
    $id,
    $guru_id
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: catatan_pelanggaran.php?pesan=" . urlencode("Catatan berhasil diperbarui."));
    exit;
}

die("Gagal memperbarui catatan: " . mysqli_stmt_error($stmt));
?>
