<?php
// Tampilkan error jika terjadi masalah di PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek keberadaan file koneksi
if (file_exists('config/koneksi.php')) {
    include 'config/koneksi.php';
} else {
    die("<div style='padding:20px; color:red; font-family:sans-serif;'><strong>Error:</strong> File <code>config/koneksi.php</code> tidak ditemukan!</div>");
}

// Fungsi aman menghitung data (tahan error jika tabel belum dibuat di phpMyAdmin)
function hitungJumlahData($koneksi, $nama_tabel) {
    try {
        $query = mysqli_query($koneksi, "SELECT * FROM $nama_tabel");
        return ($query) ? mysqli_num_rows($query) : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

// Menghitung statistik real-time dari database
$total_siswa       = hitungJumlahData($koneksi, 't_siswa');
$total_guru        = hitungJumlahData($koneksi, 't_guru');
$total_kelas       = hitungJumlahData($koneksi, 't_kelas');
$total_pelanggaran = hitungJumlahData($koneksi, 't_catatan_pelanggaran');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Si Pelang</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 4px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }
        .card-custom:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <?php 
        if (file_exists('layout/sidebar.php')) {
            include 'layout/sidebar.php'; 
        } else {
            echo "<div class='col-md-3 p-3 bg-dark text-white'>Sidebar tidak ditemukan!</div>";
        }
        ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-gauge-high text-primary"></i> Dashboard Utama
                    </h2>
                    <p class="text-muted mb-0">Selamat datang di Sistem Kedisiplinan & Pelanggaran Siswa.</p>
                </div>
                <div>
                    <span class="badge bg-white text-secondary border px-3 py-2 shadow-sm" style="border-radius: 10px;">
                        <i class="fa-regular fa-calendar me-1"></i> <?= date('d M Y'); ?>
                    </span>
                </div>
            </div>

            <!-- Grid Cards Ringkasan Statistik -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-custom p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small fw-medium text-uppercase mb-1">Total Siswa</p>
                                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_siswa); ?></h3>
                            </div>
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-custom p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small fw-medium text-uppercase mb-1">Total Guru</p>
                                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_guru); ?></h3>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-custom p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small fw-medium text-uppercase mb-1">Data Kelas</p>
                                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_kelas); ?></h3>
                            </div>
                            <div class="stat-icon bg-warning-subtle text-warning">
                                <i class="fa-solid fa-school"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card card-custom p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small fw-medium text-uppercase mb-1">Pelanggaran</p>
                                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_pelanggaran); ?></h3>
                            </div>
                            <div class="stat-icon bg-danger-subtle text-danger">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Banner -->
            <div class="card card-custom p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon bg-primary text-white rounded-circle">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Aplikasi Si Pelang</h4>
                        <span class="text-muted small">Sistem Rekapitulasi & Kedisiplinan Siswa</span>
                    </div>
                </div>
                <p class="text-secondary leading-relaxed mb-4">
                    Sistem ini dirancang untuk mempermudah pemantauan, pencatatan, dan pelaporan tingkat kedisiplinan siswa secara real-time.
                </p>
                <div class="d-flex gap-2">
                    <a href="kelola_siswa.php" class="btn btn-primary fw-medium px-4" style="border-radius: 8px; background-color: #2563eb;">
                        <i class="fa-solid fa-users me-1"></i> Data Siswa
                    </a>
                    <a href="kelola_catatan_pelanggaran.php" class="btn btn-outline-secondary px-4 fw-medium" style="border-radius: 8px;">
                        <i class="fa-solid fa-clipboard-list me-1"></i> Input Pelanggaran
                    </a>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>