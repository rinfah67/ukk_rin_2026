```php
<?php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya guru yang boleh menambahkan catatan
if (($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

// Proses harus melalui form POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

// Mengambil ID akun pengguna
$user_id = (int) ($_SESSION['id_user'] ?? 0);

if ($user_id <= 0) {
    $_SESSION['pesan_error'] = "ID akun pengguna tidak valid.";
    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

// Mencari data guru berdasarkan akun login
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama
     FROM t_guru
     WHERE user_id = ?
     LIMIT 1"
);

if (!$stmt) {
    $_SESSION['pesan_error'] = "Query data guru gagal: " . mysqli_error($koneksi);
    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$hasil_guru = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($hasil_guru);

mysqli_stmt_close($stmt);

if (!$guru) {
    $_SESSION['pesan_error'] =
        "Data guru tidak ditemukan. Periksa kolom user_id di tabel t_guru.";

    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

$guru_id = (int) $guru['id'];
$nama_guru = $guru['nama'];

// Mengambil data dari form
$penempatan_id = (int) ($_POST['penempatan_id'] ?? 0);
$pelanggaran_id = (int) ($_POST['pelanggaran_id'] ?? 0);
$tanggal = trim($_POST['tanggal'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');

// Memeriksa kolom wajib
if (
    $penempatan_id <= 0 ||
    $pelanggaran_id <= 0 ||
    $tanggal === '' ||
    $keterangan === '' ||
    $tindakan === ''
) {
    $_SESSION['pesan_error'] = "Semua kolom wajib harus diisi.";
    header("Location: tambah_catatan.php");
    exit;
}

// Memvalidasi tanggal
$tanggal_obj = DateTime::createFromFormat('!Y-m-d', $tanggal);

if (!$tanggal_obj || $tanggal_obj->format('Y-m-d') !== $tanggal) {
    $_SESSION['pesan_error'] = "Format tanggal tidak valid.";
    header("Location: tambah_catatan.php");
    exit;
}

// Mengambil data penempatan siswa
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        ks.siswa_id,
        ks.tahun_ajaran_id,
        ks.kelas_id,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas
     FROM t_kelas_siswa ks
     INNER JOIN t_siswa s ON s.id = ks.siswa_id
     INNER JOIN t_kelas k ON k.id = ks.kelas_id
     WHERE ks.id = ?
       AND ks.status_aktif = 1
       AND s.status_aktif = 1
       AND k.status_aktif = 1
     LIMIT 1"
);

if (!$stmt) {
    $_SESSION['pesan_error'] = "Query penempatan gagal: " . mysqli_error($koneksi);
    header("Location: tambah_catatan.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $penempatan_id);
mysqli_stmt_execute($stmt);

$hasil_siswa = mysqli_stmt_get_result($stmt);
$s = mysqli_fetch_assoc($hasil_siswa);

mysqli_stmt_close($stmt);

if (!$s) {
    $_SESSION['pesan_error'] =
        "Penempatan siswa tidak ditemukan atau tidak aktif.";

    header("Location: tambah_catatan.php");
    exit;
}

// Mengambil jenis pelanggaran
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama, poin, pelanggaran_kategori_id
     FROM t_pelanggaran
     WHERE id = ?
       AND status_aktif = 1
     LIMIT 1"
);

if (!$stmt) {
    $_SESSION['pesan_error'] = "Query jenis pelanggaran gagal: " . mysqli_error($koneksi);
    header("Location: tambah_catatan.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $pelanggaran_id);
mysqli_stmt_execute($stmt);

$hasil_pelanggaran = mysqli_stmt_get_result($stmt);
$p = mysqli_fetch_assoc($hasil_pelanggaran);

mysqli_stmt_close($stmt);

if (!$p) {
    $_SESSION['pesan_error'] =
        "Jenis pelanggaran tidak ditemukan atau tidak aktif.";

    header("Location: tambah_catatan.php");
    exit;
}

// Menyiapkan data yang akan disimpan
$tahun_id = (int) $s['tahun_ajaran_id'];
$siswa_id = (int) $s['siswa_id'];
$kelas_id = (int) $s['kelas_id'];

$nama_siswa = $s['nama_siswa'];
$nama_kelas = $s['nama_kelas'];
$nama_pelanggaran = $p['nama'];

$kategori_id = (int) ($p['pelanggaran_kategori_id'] ?? 0);
$poin = (int) $p['poin'];
$status = 'Belum diproses';

// Menyiapkan query INSERT
$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO t_pelanggaran_siswa (
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
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NULLIF(?, 0), ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
);

if (!$stmt) {
    $_SESSION['pesan_error'] =
        "Query INSERT gagal disiapkan: " . mysqli_error($koneksi);

    header("Location: tambah_catatan.php");
    exit;
}

// Mengikat 15 nilai sesuai urutan kolom
mysqli_stmt_bind_param(
    $stmt,
    "iisisisiisssiss",
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
    $tindakan,
    $status
);

// Menyimpan catatan
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    $_SESSION['pesan_sukses'] =
        "Catatan pelanggaran berhasil disimpan.";

    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

// Menampilkan alasan kegagalan agar mudah diperbaiki
$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

$_SESSION['pesan_error'] = "Gagal menyimpan catatan: " . $error;

header("Location: tambah_catatan.php");
exit;
?>
```