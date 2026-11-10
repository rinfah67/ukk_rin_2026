<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

if (isset($_POST['simpan'])) {
    $nama_kelas = mysqli_real_escape_string($koneksi, $_POST['nama_kelas']);
    $tingkat    = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $jurusan    = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $status     = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : 'Aktif';

    // Opsi 1: Coba simpan lengkap dengan kolom status
    $query = "INSERT INTO t_kelas (nama_kelas, tingkat, jurusan, status) 
              VALUES ('$nama_kelas', '$tingkat', '$jurusan', '$status')";
    
    try {
        $simpan = mysqli_query($koneksi, $query);
    } catch (Throwable $e) {
        // Opsi 2: Fallback jika kolom status belum ada di database
        $query_fallback = "INSERT INTO t_kelas (nama_kelas, tingkat, jurusan) 
                           VALUES ('$nama_kelas', '$tingkat', '$jurusan')";
        try {
            $simpan = mysqli_query($koneksi, $query_fallback);
        } catch (Throwable $e2) {
            // Opsi 3: Fallback jika nama kolom primary nama adalah 'nama' bukan 'nama_kelas'
            $query_fallback2 = "INSERT INTO t_kelas (nama, tingkat, jurusan) 
                                VALUES ('$nama_kelas', '$tingkat', '$jurusan')";
            $simpan = mysqli_query($koneksi, $query_fallback2);
        }
    }

    if ($simpan) {
        echo "<script>alert('Data kelas berhasil ditambahkan!'); window.location='kelola_kelas.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan kelas: " . mysqli_error($koneksi) . "'); window.location='tambah_kelas.php';</script>";
    }
} else {
    header("Location: kelola_kelas.php");
    exit();
}
?>