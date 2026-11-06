
<?php

session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Memastikan hanya admin yang dapat mengakses proses
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak!");
}

// Memeriksa data dari form
if (
    !isset($_POST['id']) ||
    !isset($_POST['nama']) ||
    !isset($_POST['tingkat']) ||
    !isset($_POST['jurusan']) ||
    !isset($_POST['status_aktif'])
) {
    $_SESSION['pesan_error'] = "Data form belum lengkap.";
    header("Location: kelola_kelas.php");
    exit;
}

// Mengambil data dari form
$id = (int) $_POST['id'];
$nama = trim($_POST['nama']);
$tingkat = trim($_POST['tingkat']);
$jurusan = trim($_POST['jurusan']);
$status_aktif = (int) $_POST['status_aktif'];

// Memeriksa data kosong
if ($id <= 0 || $nama == '' || $tingkat == '' || $jurusan == '') {
    $_SESSION['pesan_error'] = "Semua data kelas wajib diisi.";
    header("Location: edit_kelas.php?id=" . $id);
    exit;
}

// Memastikan tingkat kelas sesuai
if (!in_array($tingkat, ['X', 'XI', 'XII'], true)) {
    $_SESSION['pesan_error'] = "Tingkat kelas tidak valid.";
    header("Location: edit_kelas.php?id=" . $id);
    exit;
}

// Memastikan status hanya 0 atau 1
if (!in_array($status_aktif, [0, 1], true)) {
    $_SESSION['pesan_error'] = "Status kelas tidak valid.";
    header("Location: edit_kelas.php?id=" . $id);
    exit;
}

// Memeriksa apakah ID kelas tersedia
$cek = mysqli_prepare($koneksi, "
    SELECT id FROM t_kelas WHERE id = ?
");

mysqli_stmt_bind_param($cek, "i", $id);
mysqli_stmt_execute($cek);
$hasil = mysqli_stmt_get_result($cek);

if (mysqli_num_rows($hasil) == 0) {
    mysqli_stmt_close($cek);

    $_SESSION['pesan_error'] = "Data kelas tidak ditemukan.";
    header("Location: kelola_kelas.php");
    exit;
}

mysqli_stmt_close($cek);

// Memperbarui data kelas
$sql = "
    UPDATE t_kelas
    SET nama = ?,
        tingkat = ?,
        jurusan = ?,
        status_aktif = ?
    WHERE id = ?
";

$stmt = mysqli_prepare($koneksi, $sql);

// Memasukkan data ke query
mysqli_stmt_bind_param(
    $stmt,
    "sssii",
    $nama,
    $tingkat,
    $jurusan,
    $status_aktif,
    $id
);

// Menjalankan query
if (mysqli_stmt_execute($stmt)) {

    // Menyimpan pesan berhasil
    $_SESSION['pesan_sukses'] = "Data kelas berhasil diperbarui.";

    // Kembali ke halaman kelola kelas
    header("Location: kelola_kelas.php");
    exit;

} else {

    // Menyimpan pesan gagal
    $_SESSION['pesan_error'] = "Gagal memperbarui data kelas: " . mysqli_error($koneksi);

    header("Location: edit_kelas.php?id=" . $id);
    exit;
}

?>
