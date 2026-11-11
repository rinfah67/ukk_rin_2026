
<?php
// Menampilkan error saat proses debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Mengambil data session
$role = $_SESSION['role'] ?? 'guru';
$id_user = (int) ($_SESSION['id_user'] ?? 0);

// Fungsi menghitung jumlah data
function hitungJumlahData($koneksi, $nama_tabel)
{
    $query = mysqli_query($koneksi, "
        SELECT COUNT(*) AS total
        FROM $nama_tabel
    ");

    if ($query) {
        $data = mysqli_fetch_assoc($query);
        return $data['total'];
    }

    return 0;
}

// Nilai awal statistik
$total_siswa = 0;
$total_guru = 0;
$total_kelas = 0;
$total_pelanggaran = 0;

// Statistik hanya diambil untuk admin
if ($role === 'admin') {
    $total_siswa = hitungJumlahData($koneksi, 't_siswa');
    $total_guru = hitungJumlahData($koneksi, 't_guru');
    $total_kelas = hitungJumlahData($koneksi, 't_kelas');
    $total_pelanggaran = hitungJumlahData($koneksi, 't_pelanggaran_siswa');
}

// Memeriksa apakah guru merupakan wali kelas aktif
$is_wali_kelas = false;

if ($role === 'guru') {
    $query_wali = mysqli_query($koneksi, "
        SELECT tw.id
        FROM t_guru tg
        INNER JOIN t_wali_kelas tw
            ON tw.guru_id = tg.id
        WHERE tg.user_id = $id_user
        AND tw.status_aktif = 1
        LIMIT 1
    ");

    if ($query_wali && mysqli_num_rows($query_wali) > 0) {
        $is_wali_kelas = true;
    }
}

// Mengambil tanggal hari ini
$tanggal_hari_ini = date('d F Y');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Si Pelang</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Membuat sidebar dan konten memiliki tinggi yang seimbang */
        .container-fluid > .row {
            align-items: stretch;
        }

        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: 0.2s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #2563eb;
            color: #ffffff;
        }

        .sidebar .nav-link.text-danger:hover {
            background-color: #dc2626;
            color: #ffffff !important;
        }

        /* Judul halaman */
        .page-title {
            font-weight: 800;
            color: #1e293b;
        }

        /* Card utama */
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }

        /* Card statistik */
        .stat-card {
            padding: 22px;
            height: 100%;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 22px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }

        /* Card menu */
        .menu-card {
            padding: 22px;
            height: 100%;
            transition: 0.2s;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }

        .menu-icon {
            font-size: 28px;
            margin-bottom: 12px;
        }

        /* Banner Aplikasi Si Pelang */
        .banner-card {
            background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
            border: 1px solid #dbeafe;
        }

        .banner-title {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
        }

        .banner-description {
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .stat-number {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        include 'layout/sidebar.php';
        ?>

        <main class="col px-md-4 py-4">

                <!-- Header Dashboard -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                    <div>
                        <h3 class="page-title mb-1">Dashboard</h3>
                        <p class="text-secondary mb-0">
                            Selamat datang di Sistem Informasi Pelanggaran Siswa.
                        </p>
                    </div>

                    <div class="text-secondary small">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= htmlspecialchars($tanggal_hari_ini); ?>
                    </div>

                </div>

                <?php if ($role === 'admin') : ?>

                    <!-- Statistik Admin -->
                    <div class="row g-3 mb-4">

                        <!-- Total Siswa -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="card-custom stat-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                </div>

                                <div class="stat-number">
                                    <?= $total_siswa; ?>
                                </div>

                                <div class="stat-label">Total Siswa</div>
                            </div>
                        </div>

                        <!-- Total Guru -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="card-custom stat-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon bg-success-subtle text-success">
                                        <i class="bi bi-person-badge-fill"></i>
                                    </div>
                                </div>

                                <div class="stat-number">
                                    <?= $total_guru; ?>
                                </div>

                                <div class="stat-label">Total Guru</div>
                            </div>
                        </div>

                        <!-- Total Kelas -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="card-custom stat-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon bg-warning-subtle text-warning">
                                        <i class="bi bi-building"></i>
                                    </div>
                                </div>

                                <div class="stat-number">
                                    <?= $total_kelas; ?>
                                </div>

                                <div class="stat-label">Total Kelas</div>
                            </div>
                        </div>

                        <!-- Total Pelanggaran -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="card-custom stat-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="stat-icon bg-danger-subtle text-danger">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                </div>

                                <div class="stat-number">
                                    <?= $total_pelanggaran; ?>
                                </div>

                                <div class="stat-label">Total Pelanggaran</div>
                            </div>
                        </div>

                    </div>

                <?php else : ?>

                    <!-- Menu Guru -->
                    <div class="row g-3 mb-4">

                        <div class="col-md-4">
                            <div class="card-custom menu-card">
                                <div class="menu-icon text-primary">
                                    <i class="bi bi-journal-text"></i>
                                </div>

                                <h6 class="fw-bold">Catatan Pelanggaran</h6>

                                <p class="text-secondary small mb-3">
                                    Mengelola dan mencatat pelanggaran siswa.
                                </p>

                                <a href="kelola_catatan_pelanggaran.php" class="btn btn-primary btn-sm">
                                    Buka Menu
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card-custom menu-card">
                                <div class="menu-icon text-warning">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>

                                <h6 class="fw-bold">Tindakan</h6>

                                <p class="text-secondary small mb-3">
                                    Mengelola tindakan atas pelanggaran siswa.
                                </p>

                                <a href="tindakan.php" class="btn btn-warning btn-sm text-white">
                                    Buka Menu
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card-custom menu-card">
                                <div class="menu-icon text-success">
                                    <i class="bi bi-clock-history"></i>
                                </div>

                                <h6 class="fw-bold">Riwayat</h6>

                                <p class="text-secondary small mb-3">
                                    Melihat riwayat catatan pelanggaran.
                                </p>

                                <a href="riwayat.php" class="btn btn-success btn-sm">
                                    Buka Menu
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                    </div>

                <?php endif; ?>

                <!-- Banner Aplikasi Si Pelang -->
                <div class="card card-custom banner-card p-3 p-md-4 mb-4">

                    <div class="row align-items-center">

                        <div class="col-lg-8">

                            <h4 class="banner-title mb-2">
                                <i class="bi bi-shield-check text-primary me-2"></i>
                                Aplikasi Si Pelang
                            </h4>

                            <p class="banner-description mb-3">
                                Sistem Informasi Pelanggaran Siswa merupakan aplikasi
                                yang dirancang untuk mempermudah pemantauan,
                                pencatatan, pengelolaan tindakan, dan pelaporan
                                tingkat kedisiplinan siswa di sekolah.
                            </p>

                            <?php if ($role === 'admin') : ?>

                                <a href="kelola_siswa.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-people me-1"></i>
                                    Kelola Data Siswa
                                </a>

                            <?php else : ?>

                                <a href="kelola_catatan_pelanggaran.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-journal-text me-1"></i>
                                    Catat Pelanggaran
                                </a>

                            <?php endif; ?>

                        </div>

                        <div class="col-lg-4 text-center d-none d-lg-block">
                            <i class="bi bi-shield-lock-fill text-primary"
                               style="font-size: 85px; opacity: 0.15;"></i>
                        </div>

                    </div>

                </div>

                <!-- Footer -->
                <div class="text-center text-secondary small mt-4">
                    &copy; <?= date('Y'); ?> Si Pelang - Sistem Informasi Pelanggaran Siswa
                </div>

            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
