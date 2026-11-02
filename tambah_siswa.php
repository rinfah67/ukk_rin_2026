
<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Mengambil data kelas
$query_kelas = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_kelas
    ORDER BY nama ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid p-4">

    <div class="card border-0 shadow-sm rounded-4">

        <!-- Header -->
        <div class="card-header bg-primary text-white py-3 rounded-top-4">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-plus-circle me-2"></i>
                Form Tambah Siswa
            </h5>
        </div>

        <!-- Form -->
        <div class="card-body p-4">

            <form action="proses_tambah_siswa.php" method="POST">

                
<div class="row g-3">

    <!-- Kolom kiri -->
    <div class="col-md-6">
        <label class="form-label">NIS</label>
        <input type="text" name="nis" class="form-control"
               placeholder="Masukkan NIS" required>
    </div>

    <!-- Kolom kanan -->
    <div class="col-md-6">
        <label class="form-label">NISN</label>
        <input type="text" name="nisn" class="form-control"
               placeholder="Masukkan NISN"
               maxlength="10" required>
    </div>

    <!-- Kolom kiri -->
    <div class="col-md-6">
        <label class="form-label">Nama Siswa</label>
        <input type="text" name="nama" class="form-control"
               placeholder="Masukkan Nama Siswa" required>
    </div>

    <!-- Kolom kanan -->
    <div class="col-md-6">
        <label class="form-label">Jenis Kelamin</label>
        <select name="jenis_kelamin" class="form-select" required>
            <option value="">-- Pilih Jenis Kelamin --</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select>
    </div>

    <!-- Kolom kiri -->
    <div class="col-md-6">
        <label class="form-label">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir"
               class="form-control">
    </div>

    <!-- Kolom kanan -->
    <div class="col-md-6">
        <label class="form-label">Alamat</label>
        <textarea name="alamat" class="form-control"
                  rows="1" placeholder="Masukkan Alamat"></textarea>
    </div>

    <!-- Kolom kiri -->
    <div class="col-md-6">
        <label class="form-label">Kelas</label>
        <select name="kelas_id" class="form-select" required>
            <option value="">-- Pilih Kelas --</option>
            <!-- Pilihan kelas dari database -->
        </select>
    </div>

    <!-- Kolom kanan -->
    <div class="col-md-6">
        <label class="form-label">Status Siswa</label>
        <select name="status_aktif" class="form-select" required>
            <option value="1">Aktif</option>
            <option value="0">Tidak Aktif</option>
        </select>
    </div>

</div>

                <!-- Tombol -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i>
                        Simpan Siswa
                    </button>

                    <button type="reset" class="btn btn-outline-secondary px-4 ms-1">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Reset
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
