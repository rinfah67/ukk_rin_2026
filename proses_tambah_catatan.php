```php
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek hak akses
if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['guru', 'admin'])
) {
    die("Akses ditolak.");
}

// Ambil data dari form
$registrasi = explode('|', $_POST['registrasi'] ?? '');
$pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);
$tanggal = $_POST['tanggal'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');
$status = $_POST['status'] ?? '';

if (
    count($registrasi) != 3 ||
    $pelanggaran_id <= 0 ||
    $tanggal == '' ||
    $keterangan == '' ||
    $status == ''
) {
    die("Data catatan belum lengkap.");
}

$siswa_id = (int)$registrasi[0];
$kelas_id = (int)$registrasi[1];
$tahun_ajaran_id = (int)$registrasi[2];

// Cek status catatan
if (!in_array($status, [
    'Belum Ditindaklanjuti',
    'Diproses',
    'Selesai'
], true)) {
    die("Status tidak valid.");
}

// Ambil data siswa dan kelas
$stmt = mysqli_prepare($koneksi, "
    SELECT s.nama AS nama_siswa, k.nama AS nama_kelas
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON s.id = ks.siswa_id
    JOIN t_kelas k ON k.id = ks.kelas_id
    WHERE ks.siswa_id = ?
      AND ks.kelas_id = ?
      AND ks.tahun_ajaran_id = ?
      AND ks.status_aktif = 1
      AND s.status_aktif = 1
    LIMIT 1
");

if (!$stmt) {
    die("Query siswa gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param(
    $stmt, "iii",
    $siswa_id, $kelas_id, $tahun_ajaran_id
);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$siswa) {
    die("Siswa tidak terdaftar di kelas dan tahun ajaran tersebut.");
}

// Ambil data pelanggaran
$stmt = mysqli_prepare($koneksi, "
    SELECT id, nama, poin, pelanggaran_kategori_id
    FROM t_pelanggaran
    WHERE id = ? AND status_aktif = 1
    LIMIT 1
");

if (!$stmt) {
    die("Query pelanggaran gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $pelanggaran_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pelanggaran = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$pelanggaran) {
    die("Data pelanggaran tidak ditemukan.");
}

// Ambil data guru yang login
$user_id = (int)($_SESSION['id_user'] ?? 0);
$guru_id = 0;
$nama_guru = $_SESSION['name'] ?? 'Admin';

if ($_SESSION['role'] == 'guru') {
    $stmt = mysqli_prepare($koneksi, "
        SELECT id, nama
        FROM t_guru
        WHERE user_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Query guru gagal: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $guru = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$guru) {
        die("Data guru belum terhubung dengan akun pengguna.");
    }

    $guru_id = (int)$guru['id'];
    $nama_guru = $guru['nama'];
}

// Siapkan data yang akan disimpan
$nama_siswa = $siswa['nama_siswa'];
$nama_kelas = $siswa['nama_kelas'];
$nama_pelanggaran = $pelanggaran['nama'];
$poin = (int)$pelanggaran['poin'];
$kategori_id = (int)($pelanggaran['pelanggaran_kategori_id'] ?? 0);

// Simpan catatan
$stmt = mysqli_prepare($koneksi, "
    INSERT INTO t_pelanggaran_siswa (
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
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Query penyimpanan gagal: " . mysqli_error($koneksi));
}

// i = angka, s = teks
mysqli_stmt_bind_param(
    $stmt,
    "iisisisiisssiss",
    $tahun_ajaran_id,
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
    $tindakan,
    $status
);


// Jalankan penyimpanan
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: catatan_pelanggaran.php?pesan=tambah");
    exit;
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

die("Gagal menyimpan catatan: " . $error);
?>
```