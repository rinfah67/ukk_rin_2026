
<?php
session_start(); // Memulai session
include 'config/koneksi.php'; // Menghubungkan ke database

// Memeriksa status login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Halaman hanya boleh diakses admin
if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Kategori Pelanggaran</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten utama -->
        <main class="col-md-9 col-lg-10 p-4">

            <div class="mb-4">
                <h3 class="fw-semibold">Tambah Kategori Pelanggaran</h3>
                <p class="text-muted mb-0">
                    Tambahkan kategori pelanggaran siswa.
                </p>
            </div>

            <!-- Form -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle me-2"></i>
                        Form Kategori Pelanggaran
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form action="proses_tambah_pelanggaran_kategori.php" method="POST">

                        <!-- Nama Kategori -->
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>

                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                maxlength="100"
                                placeholder="Masukkan nama kategori"
                                required
                            >
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>

                            <textarea
                                name="deskripsi"
                                class="form-control"
                                rows="4"
                                placeholder="Masukkan deskripsi kategori"
                                required
                            ></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label">Status</label>

                            <select name="status_aktif" class="form-select" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan
                            </button>

                            <a href="kelola_pelanggaran_kategori.php" class="btn btn-secondary">
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
