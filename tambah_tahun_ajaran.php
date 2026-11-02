
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Memeriksa hak akses admin
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak!");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Tahun Ajaran - SI PELANG</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <div class="mb-4">
                <h2 class="fw-bold">Tambah Tahun Ajaran</h2>
                <p class="text-secondary">
                    Tambahkan data tahun ajaran baru.
                </p>
            </div>

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-plus me-2"></i>
                        Form Tambah Tahun Ajaran
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form action="proses_tambah_tahun_ajaran.php" method="POST">

                        <div class="row g-3">

                            <!-- Nama Tahun Ajaran -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Nama Tahun Ajaran
                                </label>

                                <input type="text"
                                       name="nama"
                                       class="form-control"
                                       placeholder="Contoh: 2026/2027"
                                       maxlength="20"
                                       required>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Status Tahun Ajaran
                                </label>

                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Tanggal Mulai
                                </label>

                                <input type="date"
                                       name="tanggal_mulai"
                                       class="form-control"
                                       required>
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Tanggal Selesai
                                </label>

                                <input type="date"
                                       name="tanggal_selesai"
                                       class="form-control"
                                       required>
                            </div>

                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" name="simpan" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan
                            </button>

                            <a href="kelola_tahun_ajaran.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Kembali
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
