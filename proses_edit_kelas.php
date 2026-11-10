<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

if (isset($_POST['update'])) {
    $id         = mysqli_real_escape_string($koneksi, $_POST['id']);
    $nama_kelas = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $tingkat    = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $jurusan    = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $status     = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : 'Aktif';

    // Opsi 1: Coba update data lengkap beserta kolom status & nama_kelas
    $query = "UPDATE t_kelas SET 
                nama_kelas = '$nama_kelas', 
                tingkat    = '$tingkat', 
                jurusan    = '$jurusan', 
                status     = '$status' 
              WHERE id = '$id'";

    try {
        $update = mysqli_query($koneksi, $query);
    } catch (Throwable $e) {
        // Opsi 2: Fallback jika kolom status belum ada di database
        $query_fallback = "UPDATE t_kelas SET 
                            nama_kelas = '$nama_kelas', 
                            tingkat    = '$tingkat', 
                            jurusan    = '$jurusan' 
                          WHERE id = '$id'";
        try {
            $update = mysqli_query($koneksi, $query_fallback);
        } catch (Throwable $e2) {
            // Opsi 3: Fallback jika nama kolom di DB adalah 'nama' bukan 'nama_kelas'
            $query_fallback2 = "UPDATE t_kelas SET 
                                 nama    = '$nama_kelas', 
                                 tingkat = '$tingkat', 
                                 jurusan = '$jurusan' 
                               WHERE id = '$id'";
            $update = mysqli_query($koneksi, $query_fallback2);
        }
    }

    if ($update) {
        echo "<script>alert('Data kelas berhasil diperbarui!'); window.location='kelola_kelas.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data kelas: " . mysqli_error($koneksi) . "'); window.location='edit_kelas.php?id=$id';</script>";
    }
} else {
    header("Location: kelola_kelas.php");
    exit();
}
?>