
<?php
// hapus_catatan_pelanggaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// ============================================
// CEK HAK AKSES GURU
// ============================================

if (($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

// ============================================
// CEK METODE REQUEST
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

// ============================================
// CEK TOKEN CSRF
// ============================================

$csrf_token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrf_token)
) {
    $_SESSION['pesan_error'] = "Permintaan tidak valid.";
    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

// ============================================
// AMBIL ID CATATAN
// ============================================

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan_error'] = "ID catatan tidak valid.";
    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

// ============================================
// HAPUS DATA
// ============================================

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_pelanggaran_siswa WHERE id = ?"
);

if (!$stmt) {
    $_SESSION['pesan_error'] = "Query gagal disiapkan.";
    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['pesan_sukses'] = "Catatan pelanggaran berhasil dihapus.";
    } else {
        $_SESSION['pesan_error'] = "Data catatan tidak ditemukan.";
    }

} else {
    $_SESSION['pesan_error'] = "Catatan gagal dihapus. Periksa apakah data masih terhubung dengan tabel lain.";
}

mysqli_stmt_close($stmt);

header("Location: kelola_catatan_pelanggaran.php");
exit;
?>
