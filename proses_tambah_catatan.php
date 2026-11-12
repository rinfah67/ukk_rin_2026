
<?php
// proses_simpan_pelanggaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// ============================================
// CEK HAK AKSES
// ============================================

if (($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

// ============================================
// MENGAMBIL ID USER LOGIN
// ============================================

$user_id = (int) ($_SESSION['id_user'] ?? 0);

if ($user_id <= 0) {
    die("ID pengguna tidak valid.");
}

// ============================================
// MENCARI DATA GURU BERDASARKAN AKUN LOGIN
// ============================================

$stmt = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ?
");

if (!$stmt) {
    die("Query guru gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$hasil_guru = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($hasil_guru);

mysqli_stmt_close($stmt);

if (!$guru) {
    die("Data guru tidak ditemukan.");
}

$guru_id = (int) $guru['id'];
$nama_guru = $guru['nama'];

// ============================================
// MENGAMBIL DATA DARI FORM
// ============================================

$penempatan_id = (int) ($_POST['penempatan_id'] ?? 0);
$pelanggaran_id = (int) ($_POST['pelanggaran_id'] ?? 0);

$tanggal = trim($_POST['tanggal'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

// Tindakan belum diisi saat catatan dibuat
$tindakan = null;

// ============================================
// VALIDASI INPUT
// ============================================


if ($penempatan_id <= '') {
    die("ERROR: ID penempatan siswa tidak terkirim.");
}

if ($pelanggaran_id <= 0) {
    die("ERROR: ID pelanggaran tidak terkirim.");
}

if ($tanggal === '') {
    die("ERROR: Tanggal tidak terkirim.");
}

if ($keterangan === '') {
    die("ERROR: Keterangan tidak terkirim.");
}


// ============================================
// VALIDASI FORMAT TANGGAL
// ============================================

$tanggal_obj = DateTime::createFromFormat('!Y-m-d', $tanggal);

if (
    !$tanggal_obj ||
    $tanggal_obj->format('Y-m-d') !== $tanggal
) {
    die("Tanggal tidak valid.");
}

// ============================================
// MENGAMBIL DATA PENEMPATAN SISWA
// ============================================

$stmt = mysqli_prepare($koneksi, "
    SELECT
        ks.siswa_id,
        ks.tahun_ajaran_id,
        ks.kelas_id,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON s.id = ks.siswa_id
    JOIN t_kelas k ON k.id = ks.kelas_id
    WHERE ks.id = ?
      AND ks.status_aktif = 1
      AND s.status_aktif = 1
");

if (!$stmt) {
    die("Query penempatan gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $penempatan_id);
mysqli_stmt_execute($stmt);

$hasil_siswa = mysqli_stmt_get_result($stmt);
$s = mysqli_fetch_assoc($hasil_siswa);

mysqli_stmt_close($stmt);

if (!$s) {
    die("Penempatan siswa tidak ditemukan atau tidak aktif.");
}

// ============================================
// MENGAMBIL DATA JENIS PELANGGARAN
// ============================================

$stmt = mysqli_prepare($koneksi, "
    SELECT
        id,
        nama,
        poin,
        pelanggaran_kategori_id
    FROM t_pelanggaran
    WHERE id = ?
      AND status_aktif = 1
");

if (!$stmt) {
    die("Query pelanggaran gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $pelanggaran_id);
mysqli_stmt_execute($stmt);

$hasil_pelanggaran = mysqli_stmt_get_result($stmt);
$p = mysqli_fetch_assoc($hasil_pelanggaran);

mysqli_stmt_close($stmt);

if (!$p) {
    die("Jenis pelanggaran tidak ditemukan atau tidak aktif.");
}

// ============================================
// MENYIAPKAN DATA UNTUK DISIMPAN
// ============================================

$tahun_id = (int) $s['tahun_ajaran_id'];
$siswa_id = (int) $s['siswa_id'];
$kelas_id = (int) $s['kelas_id'];

$nama_siswa = $s['nama_siswa'];
$nama_kelas = $s['nama_kelas'];

$nama_pelanggaran = $p['nama'];
$kategori_id = (int) ($p['pelanggaran_kategori_id'] ?? 0);

$poin = (int) $p['poin'];

$status = 'Belum diproses';

// ============================================
// MENYIMPAN CATATAN PELANGGARAN
// ============================================

$sql = "
    INSERT INTO t_pelanggaran_siswa
    (
        tahun_ajaran_id,
        siswa_id,
        nama_siswa,
        kelas_id,
        nama_kelas,
        pelanggaran_id,
        nama_pelanggaran,
        pelanggaran_kategori_id,
        guru_id,
        nama_guru,
        tanggal,
        keterangan,
        poin,
        tindakan,
        status,
        created_at,
        updated_at
    )
    VALUES
    (
        ?, ?, ?, ?, ?, ?, ?, NULLIF(?, 0),
        ?, ?, ?, ?, ?, NULL, ?, NOW(), NOW()
    )
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query penyimpanan gagal: " . mysqli_error($koneksi));
}

// ============================================
// MENGHUBUNGKAN PARAMETER
// ============================================

mysqli_stmt_bind_param(
    $stmt,
    "iisisisiisssis",
    $tahun_id,
    $siswa_id,
    $nama_siswa,
    $kelas_id,
    $nama_kelas,
    $pelanggaran_id,
    $nama_pelanggaran,
    $kategori_id,
    $guru_id,
    $nama_guru,
    $tanggal,
    $keterangan,
    $poin,
    $status
);

// ============================================
// EKSEKUSI PENYIMPANAN
// ============================================

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header(
        "Location: kelola_catatan_pelanggaran.php?pesan=" .
        urlencode("Catatan berhasil ditambahkan.")
    );

    exit;

} else {

    $pesan_error = mysqli_stmt_error($stmt);

    mysqli_stmt_close($stmt);

    die("Gagal menyimpan catatan: " . $pesan_error);
}
?>
