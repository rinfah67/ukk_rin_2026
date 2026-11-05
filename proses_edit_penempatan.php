
<?php
// proses_edit_penempatan.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Fungsi kembali dengan pesan error
function kembaliDenganError($pesan)
{
    $_SESSION['pesan_error'] = $pesan;
    header("Location: penempatan_siswa.php");
    exit;
}

// Pastikan menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

// Periksa token keamanan
$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    kembaliDenganError("Permintaan tidak valid. Silakan coba kembali.");
}

// Ambil data form
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$tahun_ajaran_id = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);
$kelas_id = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);

$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';

// Validasi data wajib
if (
    !$id ||
    !$siswa_id ||
    !$tahun_ajaran_id ||
    !$kelas_id ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array((string)$status_aktif, ['0', '1'], true)
) {
    kembaliDenganError("Semua data penempatan wajib diisi.");
}

$status_aktif = (int)$status_aktif;

// Periksa format tanggal
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

// Periksa urutan tanggal
if ($tanggal_selesai < $tanggal_mulai) {
    kembaliDenganError("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

// Pastikan data penempatan tersedia
$stmt = mysqli_prepare($koneksi, "
    SELECT id, tahun_ajaran_id
    FROM t_kelas_siswa
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    kembaliDenganError("Gagal memeriksa data penempatan.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data_lama = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data_lama) {
    kembaliDenganError("Data penempatan tidak ditemukan.");
}

// Pastikan siswa aktif
$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM t_siswa
    WHERE id = ?
    AND status_aktif = 1
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$siswa_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$siswa_valid) {
    kembaliDenganError("Siswa tidak ditemukan atau tidak aktif.");
}

// Pastikan tahun ajaran aktif atau merupakan tahun ajaran lama milik data ini
$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM t_tahun_ajaran
    WHERE id = ?
    AND (status_aktif = 1 OR id = ?)
    LIMIT 1
");

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $tahun_ajaran_id,
    $data_lama['tahun_ajaran_id']
);

mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$tahun_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$tahun_valid) {
    kembaliDenganError("Tahun ajaran tidak ditemukan atau tidak aktif.");
}

// Pastikan kelas aktif atau merupakan kelas lama milik data ini
$stmt = mysqli_prepare($koneksi, "
    SELECT id
    FROM t_kelas
    WHERE id = ?
    AND (status_aktif = 1 OR id = (
        SELECT kelas_id
        FROM t_kelas_siswa
        WHERE id = ?
    ))
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "ii", $kelas_id, $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$kelas_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$kelas_valid) {
    kembaliDenganError("Kelas tidak ditemukan atau tidak aktif.");
}

// Cek apakah siswa sudah memiliki penempatan aktif lain
// pada tahun ajaran yang sama
if ($status_aktif === 1) {

    $stmt = mysqli_prepare($koneksi, "
        SELECT id
        FROM t_kelas_siswa
        WHERE siswa_id = ?
        AND tahun_ajaran_id = ?
        AND status_aktif = 1
        AND id != ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "iii",
        $siswa_id,
        $tahun_ajaran_id,
        $id
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $duplikat = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($duplikat) {
        kembaliDenganError(
            "Siswa sudah memiliki penempatan aktif pada tahun ajaran tersebut."
        );
    }
}

// Update data penempatan
$stmt = mysqli_prepare($koneksi, "
    UPDATE t_kelas_siswa
    SET
        siswa_id = ?,
        tahun_ajaran_id = ?,
        kelas_id = ?,
        tanggal_mulai = ?,
        tanggal_selesai = ?,
        status_aktif = ?
    WHERE id = ?
");

if (!$stmt) {
    kembaliDenganError("Gagal menyiapkan query perubahan data.");
}

mysqli_stmt_bind_param(
    $stmt,
    "iiissii",
    $siswa_id,
    $tahun_ajaran_id,
    $kelas_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif,
    $id
);

// Jalankan update
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    $_SESSION['pesan_sukses'] = "Data penempatan siswa berhasil diperbarui.";

    header("Location: penempatan_siswa.php");
    exit;
}

$pesan = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

kembaliDenganError("Gagal mengubah data penempatan: " . $pesan);
?>
