
<?php
// Menampilkan error PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengecek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Koneksi database
require_once 'config/koneksi.php';

// Mengambil role dan ID pengguna
$role = $_SESSION['role'] ?? 'guru';
$id_user = (int) ($_SESSION['id_user'] ?? 0);

// Fungsi menghitung data
function hitungJumlahData($koneksi, $nama_tabel) {
    $query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM $nama_tabel");

    if ($query) {
        $data = mysqli_fetch_assoc($query);
        return (int) $data['total'];
    }

    return 0;
}

// Statistik admin
$total_siswa = hitungJumlahData($koneksi, 't_siswa');
$total_guru = hitungJumlahData($koneksi, 't_guru');
$total_kelas = hitungJumlahData($koneksi, 't_kelas');

// Menghitung catatan pelanggaran
$total_pelanggaran = hitungJumlahData($koneksi, 't_pelanggaran_siswa');

// Mengecek apakah guru merupakan wali kelas
$is_wali_kelas = false;

if ($role === 'guru' && $id_user > 0) {

    $query_wali = mysqli_query($koneksi, "
        SELECT tw.id
        FROM t_guru tg
        INNER JOIN t_wali_kelas tw ON tw.guru_id = tg.id
        WHERE tg.user_id = $id_user
        AND tw.status_aktif = 1
        LIMIT 1
    ");

    if ($query_wali && mysqli_num_rows($query_wali) > 0) {
        $is_wali_kelas = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Si Pelang</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
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
            height: 100%;
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
            flex-shrink: 0;
        }

        .card-menu {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
        }

        .card-menu:hover {
            color: inherit;
        }

        .btn-menu {
            border-radius: 8px;
            font-size: 13px;
            padding: 8px 15px;
        }

        .banner-card {
            background: linear-gradient(120deg, #ffffff, #eff6ff);
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-gauge-high text-primary me-2"></i>
                        Dashboard
                    </h2>

                    <p class="text-muted mb-0">
                        Selamat datang di Sistem Kedisiplinan & Pelanggaran Siswa.
                    </p>
                </div>

                <span class="badge bg-white text-secondary border px-3 py-2 shadow-sm">
                    <i class="fa-regular fa-calendar me-1"></i>
                    <?= date('d M Y'); ?>
                </span>
            </div>

            <?php if ($role === 'admin') : ?>

                <!-- CARD ADMIN -->

                <div class="row g-4 mb-4">

                    <div class="col-xl-3 col-md-6">
                        <div class="card card-custom p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small fw-medium text-uppercase mb-1">Total Siswa</p>
                                    <h3 class="fw-bold text-dark mb-0">
                                        <?= number_format($total_siswa); ?>
                                    </h3>
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
                                    <h3 class="fw-bold text-dark mb-0">
                                        <?= number_format($total_guru); ?>
                                    </h3>
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
                                    <h3 class="fw-bold text-dark mb-0">
                                        <?= number_format($total_kelas); ?>
                                    </h3>
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
                                    <h3 class="fw-bold text-dark mb-0">
                                        <?= number_format($total_pelanggaran); ?>
                                    </h3>
                                </div>

                                <div class="stat-icon bg-danger-subtle text-danger">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            <?php elseif ($role === 'guru') : ?>

                <!-- CARD GURU -->

                <div class="row g-4 mb-4">

                    <!-- CATATAN PELANGGARAN -->
                    <div class="col-xl-4 col-md-6">
                        <a href="kelola_catatan_pelanggaran.php" class="card-menu">
                            <div class="card card-custom p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                    </div>

                                    <i class="fa-solid fa-arrow-up-right-from-square text-muted"></i>
                                </div>

                                <h5 class="fw-bold text-dark">Catatan Pelanggaran</h5>

                                <p class="text-muted small mb-3">
                                    Mencatat dan mengelola pelanggaran siswa.
                                </p>

                                <span class="btn btn-primary btn-menu align-self-start">
                                    Buka Menu <i class="fa-solid fa-arrow-right ms-1"></i>
                                </span>
                            </div>
                        </a>
                    </div>

                    <!-- TINDAKAN -->
                    <div class="col-xl-4 col-md-6">
                        <a href="tindakan.php" class="card-menu">
                            <div class="card card-custom p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon bg-warning-subtle text-warning">
                                        <i class="fa-solid fa-hand-holding-heart"></i>
                                    </div>

                                    <i class="fa-solid fa-arrow-up-right-from-square text-muted"></i>
                                </div>

                                <h5 class="fw-bold text-dark">Tindakan</h5>

                                <p class="text-muted small mb-3">
                                    Mengelola tindak lanjut pelanggaran siswa.
                                </p>

                                <span class="btn btn-warning btn-menu text-dark">
                                    Buka Menu <i class="fa-solid fa-arrow-right ms-1"></i>
                                </span>
                            </div>
                        </a>
                    </div>

                    <!-- RIWAYAT -->
                    <div class="col-xl-4 col-md-6">
                        <a href="riwayat.php" class="card-menu">
                            <div class="card card-custom p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon bg-success-subtle text-success">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>

                                    <i class="fa-solid fa-arrow-up-right-from-square text-muted"></i>
                                </div>

                                <h5 class="fw-bold text-dark">Riwayat</h5>

                                <p class="text-muted small mb-3">
                                    Melihat riwayat catatan pelanggaran siswa.
                                </p>

                                <span class="btn btn-success btn-menu">
                                    Buka Menu <i class="fa-solid fa-arrow-right ms-1"></i>
                                </span>
                            </div>
                        </a>
                    </div>

                    <!-- LAPORAN KHUSUS WALI KELAS -->
                    <?php if ($is_wali_kelas) : ?>

                        <div class="col-xl-4 col-md-6">
                            <a href="laporan.php" class="card-menu">
                                <div class="card card-custom p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="stat-icon bg-danger-subtle text-danger">
                                            <i class="fa-solid fa-file-invoice"></i>
                                        </div>

                                        <i class="fa-solid fa-arrow-up-right-from-square text-muted"></i>
                                    </div>

                                    <h5 class="fw-bold text-dark">Laporan</h5>

                                    <p class="text-muted small mb-3">
                                        Melihat laporan pelanggaran siswa di kelas yang diampu.
                                    </p>

                                    <span class="btn btn-danger btn-menu">
                                        Buka Menu <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </span>
                                </div>
                            </a>
                        </div>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <!-- BANNER INFORMASI -->
            <div class="card card-custom banner-card p-4 p-md-5">

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon bg-primary text-white rounded-circle">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <h4 class="fw-bold text-dark mb-0">Aplikasi Si Pelang</h4>
                        <span class="text-muted small">
                            Sistem Rekapitulasi & Kedisiplinan Siswa
                        </span>
                    </div>
                </div>

                <p class="text-secondary mb-4">
                    Sistem ini dirancang untuk mempermudah pemantauan,
                    pencatatan, pengelolaan tindakan, dan pelaporan
                    tingkat kedisiplinan siswa.
                </p>

                <?php if ($role === 'admin') : ?>

                    <a href="kelola_siswa.php" class="btn btn-primary btn-menu">
                        <i class="fa-solid fa-users me-1"></i>
                        Data Siswa
                    </a>

                <?php else : ?>

                    <a href="kelola_catatan_pelanggaran.php" class="btn btn-primary btn-menu">
                        <i class="fa-solid fa-clipboard-list me-1"></i>
                        Catat Pelanggaran
                    </a>

                <?php endif; ?>

            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
