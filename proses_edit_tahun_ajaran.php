```php
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin yang boleh mengedit
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Pastikan menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

// Mengambil data dari form
$id = (int) ($_POST['id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = $_POST['status_aktif'] ?? '';

// Validasi data
if (
    $id <= 0 ||
    $nama === '' ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array($status_aktif, ['0', '1'], true)
) {
    header("Location: kelola_tahun_ajaran.php?pesan=data_kosong");
    exit;
}

// Validasi tanggal
if ($tanggal_selesai < $tanggal_mulai) {
    header("Location: kelola_tahun_ajaran.php?pesan=tanggal_tidak_valid");
    exit;
}

// Memulai transaksi
mysqli_begin_transaction($koneksi);

try {

    // Jika tahun ajaran ini diaktifkan,
    // nonaktifkan semua tahun ajaran lainnya
    if ($status_aktif === '1') {
        $query_nonaktif = mysqli_prepare($koneksi, "
            UPDATE t_tahun_ajaran
            SET status_aktif = 0
            WHERE id != ?
        ");

        mysqli_stmt_bind_param($query_nonaktif, "i", $id);
        mysqli_stmt_execute($query_nonaktif);
        mysqli_stmt_close($query_nonaktif);
    }

    // Update tahun ajaran yang dipilih
    $query = mysqli_prepare($koneksi, "
        UPDATE t_tahun_ajaran
        SET
            nama = ?,
            tanggal_mulai = ?,
            tanggal_selesai = ?,
            status_aktif = ?
        WHERE id = ?
    ");

    $status = (int) $status_aktif;

    mysqli_stmt_bind_param(
        $query,
        "sssii",
        $nama,
        $tanggal_mulai,
        $tanggal_selesai,
        $status,
        $id
    );

    mysqli_stmt_execute($query);
    mysqli_stmt_close($query);

    // Simpan perubahan
    mysqli_commit($koneksi);

    header("Location: kelola_tahun_ajaran.php?pesan=edit_berhasil");
    exit;

} catch (Exception $e) {

    // Batalkan perubahan jika gagal
    mysqli_rollback($koneksi);

    die("Gagal mengedit tahun ajaran: " . $e->getMessage());
}
?>
```