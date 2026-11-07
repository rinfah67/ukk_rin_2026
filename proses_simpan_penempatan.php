
<?php
// proses_simpan_penempatan.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Pastikan data dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

// Fungsi untuk kembali dengan pesan error
function kembaliDenganError($pesan)
{
    $_SESSION['pesan_error'] = $pesan;
    header("Location: penempatan_siswa.php");
    exit;
}

// Ambil data dari form
$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$tahun_ajaran_id = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);
$kelas_id = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);

$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';

// 1. Validasi data wajib
if (
    !$siswa_id ||
    !$tahun_ajaran_id ||
    !$kelas_id ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array((string) $status_aktif, ['0', '1'], true)
) {
    kembaliDenganError("Semua data penempatan wajib diisi.");
}

// Ubah status menjadi integer
$status_aktif = (int) $status_aktif;

// 2. Validasi format tanggal
$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (
    !$mulai ||
    !$selesai ||
    $mulai->format('Y-m-d') !== $tanggal_mulai ||
    $selesai->format('Y-m-d') !== $tanggal_selesai
) {
    kembaliDenganError("Format tanggal tidak valid.");
}

// 3. Validasi urutan tanggal
if ($tanggal_selesai < $tanggal_mulai) {
    kembaliDenganError("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

// 4. Pastikan siswa tersedia dan aktif
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM t_siswa
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

if (!$stmt) {
    kembaliDenganError("Gagal memeriksa data siswa.");
}

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$siswa_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$siswa_valid) {
    kembaliDenganError("Siswa tidak ditemukan atau tidak aktif.");
}

// 5. Pastikan siswa belum memiliki penempatan aktif pada tahun ajaran yang sama
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM t_kelas_siswa
     WHERE siswa_id = ?
     AND tahun_ajaran_id = ?
     AND status_aktif = 1
     LIMIT 1"
);

if (!$stmt) {
    kembaliDenganError("Gagal memeriksa penempatan siswa.");
}

mysqli_stmt_bind_param($stmt, "ii", $siswa_id, $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$siswa_sudah_ditempatkan = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if ($siswa_sudah_ditempatkan) {
    kembaliDenganError(
        "Siswa tersebut sudah memiliki penempatan aktif pada tahun ajaran ini."
    );
}

// 6. Pastikan tahun ajaran tersedia dan aktif
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM t_tahun_ajaran
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

if (!$stmt) {
    kembaliDenganError("Gagal memeriksa tahun ajaran.");
}

mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$tahun_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$tahun_valid) {
    kembaliDenganError("Tahun ajaran tidak ditemukan atau tidak aktif.");
}

// 7. Pastikan kelas tersedia dan aktif
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id
     FROM t_kelas
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

if (!$stmt) {
    kembaliDenganError("Gagal memeriksa kelas.");
}

mysqli_stmt_bind_param($stmt, "i", $kelas_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$kelas_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$kelas_valid) {
    kembaliDenganError("Kelas tidak ditemukan atau tidak aktif.");
}

// 8. Simpan data penempatan siswa
$sql = "INSERT INTO t_kelas_siswa
        (
            siswa_id,
            tahun_ajaran_id,
            kelas_id,
            tanggal_mulai,
            tanggal_selesai,
            status_aktif
        )
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    kembaliDenganError("Gagal menyiapkan query penyimpanan.");
}

// Hubungkan parameter
mysqli_stmt_bind_param(
    $stmt,
    "iiissi",
    $siswa_id,
    $tahun_ajaran_id,
    $kelas_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif
);

// 9. Jalankan penyimpanan
if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    $_SESSION['pesan_sukses'] =
        "Penempatan siswa berhasil disimpan.";

    header("Location: penempatan_siswa.php");
    exit;
}

// Jika gagal menyimpan
$pesan = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

$_SESSION['pesan_error'] =
    "Gagal menyimpan penempatan siswa: " . $pesan;

header("Location: penempatan_siswa.php");
exit;
?>
