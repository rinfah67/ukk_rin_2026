
<?php
session_start();

include 'config/koneksi.php';

// =====================================
// CEK REQUEST
// =====================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

// =====================================
// AMBIL DATA FORM
// =====================================
$id = (int) ($_POST['id'] ?? 0);
$nama = trim($_POST['tahun_ajaran'] ?? '');
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = isset($_POST['status_aktif'])
    ? (int) $_POST['status_aktif']
    : -1;

// =====================================
// VALIDASI DATA
// =====================================
if (
    $id <= 0 ||
    $nama === '' ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array($status_aktif, [0, 1], true)
) {
    echo "<script>
        alert('Data belum lengkap atau tidak valid!');
        window.location='kelola_tahun_ajaran.php';
    </script>";
    exit;
}

if ($tanggal_selesai < $tanggal_mulai) {
    echo "<script>
        alert('Tanggal selesai tidak boleh sebelum tanggal mulai!');
        window.history.back();
    </script>";
    exit;
}

// =====================================
// PROSES UPDATE
// HANYA SATU TAHUN AJARAN AKTIF
// =====================================
mysqli_begin_transaction($koneksi);

try {

    // Pastikan data tersedia
    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_tahun_ajaran WHERE id = ?"
    );

    mysqli_stmt_bind_param($cek, "i", $id);
    mysqli_stmt_execute($cek);

    $hasil = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil) === 0) {
        throw new Exception("Data tahun ajaran tidak ditemukan!");
    }

    // Jika dipilih aktif, nonaktifkan tahun ajaran lainnya
    if ($status_aktif === 1) {

        $nonaktif = mysqli_prepare(
            $koneksi,
            "UPDATE t_tahun_ajaran
             SET status_aktif = 0
             WHERE id != ?"
        );

        mysqli_stmt_bind_param($nonaktif, "i", $id);
        mysqli_stmt_execute($nonaktif);
    }

    // Update data tahun ajaran
    $update = mysqli_prepare(
        $koneksi,
        "UPDATE t_tahun_ajaran
         SET nama = ?,
             tanggal_mulai = ?,
             tanggal_selesai = ?,
             status_aktif = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "sssii",
        $nama,
        $tanggal_mulai,
        $tanggal_selesai,
        $status_aktif,
        $id
    );

    mysqli_stmt_execute($update);

    mysqli_commit($koneksi);

    echo "<script>
        alert('Data Tahun Ajaran berhasil diperbarui!');
        window.location='kelola_tahun_ajaran.php';
    </script>";
    exit;

} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    error_log($e->getMessage());

    echo "<script>
        alert('Gagal memperbarui data Tahun Ajaran!');
        window.location='kelola_tahun_ajaran.php';
    </script>";
    exit;
}
