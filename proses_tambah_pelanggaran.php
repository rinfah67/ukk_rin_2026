
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

$user_id = (int)($_SESSION['id_user'] ?? 0);

// Cari guru berdasarkan akun login
$stmt = mysqli_prepare($koneksi, "SELECT id, nama FROM t_guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$guru) {
    die("Data guru tidak ditemukan.");
}

$guru_id = (int)$guru['id'];
$penempatan_id = (int)($_POST['penempatan_id'] ?? 0);
$pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);
$tanggal = $_POST['tanggal'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');

if (!$penempatan_id || !$pelanggaran_id || !$tanggal ||
    $keterangan === '' || $tindakan === '') {
    die("Semua kolom wajib diisi.");
}

// Validasi tanggal
$tanggal_obj = DateTime::createFromFormat('!Y-m-d', $tanggal);
if (!$tanggal_obj || $tanggal_obj->format('Y-m-d') !== $tanggal) {
    die("Tanggal tidak valid.");
}

// Ambil penempatan siswa
$stmt = mysqli_prepare($koneksi, "
    SELECT ks.siswa_id, ks.tahun_ajaran_id, ks.kelas_id,
           s.nama AS nama_siswa, k.nama AS nama_kelas
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON s.id = ks.siswa_id
    JOIN t_kelas k ON k.id = ks.kelas_id
    WHERE ks.id = ? AND ks.status_aktif = 1 AND s.status_aktif = 1
");
mysqli_stmt_bind_param($stmt, "i", $penempatan_id);
mysqli_stmt_execute($stmt);
$s = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$s) {
    die("Penempatan siswa tidak ditemukan atau tidak aktif.");
}

// Ambil jenis pelanggaran
$stmt = mysqli_prepare($koneksi, "
    SELECT id, nama, poin, pelanggaran_kategori_id
    FROM t_pelanggaran
    WHERE id = ? AND status_aktif = 1
");
mysqli_stmt_bind_param($stmt, "i", $pelanggaran_id);
mysqli_stmt_execute($stmt);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$p) {
    die("Jenis pelanggaran tidak ditemukan atau tidak aktif.");
}

$tahun_id = (int)$s['tahun_ajaran_id'];
$siswa_id = (int)$s['siswa_id'];
$kelas_id = (int)$s['kelas_id'];
$nama_siswa = $s['nama_siswa'];
$nama_kelas = $s['nama_kelas'];
$nama_pelanggaran = $p['nama'];
$kategori_id = (int)($p['pelanggaran_kategori_id'] ?? 0);
$nama_guru = $guru['nama'];
$poin = (int)$p['poin'];
$status = 'Belum diproses';

// Simpan catatan baru
$stmt = mysqli_prepare($koneksi, "
    INSERT INTO t_pelanggaran_siswa
    (tahun_ajaran_id, siswa_id, nama_siswa, kelas_id, nama_kelas,
     pelanggaran_id, nama_pelanggaran, pelanggaran_kategori_id,
     guru_id, nama_guru, tanggal, keterangan, poin, tindakan, status,
     created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NULLIF(?, 0), ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
");

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

if (mysqli_stmt_execute($stmt)) {
    header("Location: catatan_pelanggaran.php?pesan=" . urlencode("Catatan berhasil ditambahkan."));
    exit;
}

die("Gagal menyimpan catatan: " . mysqli_stmt_error($stmt));
?>
