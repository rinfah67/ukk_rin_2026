
<?php
session_start();
include 'config/koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Mengambil data pengguna
$nama = $_SESSION['name'] ?? 'Muhamad Fahrin Adam';
$role = $_SESSION['role'] ?? 'guru';

// Menentukan halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Me - SI PELANG</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
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

        /* Header */
        .page-header {
            margin-bottom: 25px;
        }

        .page-header h3 {
            font-weight: 800;
            color: #1e293b;
        }

        /* Profile */
        .profile-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.06);
        }

        .profile-cover {
            height: 190px;
            background: linear-gradient(120deg, #1d4ed8, #2563eb, #60a5fa);
            position: relative;
        }

        .profile-cover::before {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border: 35px solid rgba(255,255,255,0.08);
            border-radius: 50%;
            right: -40px;
            top: -100px;
        }

        .profile-cover::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            border: 25px solid rgba(255,255,255,0.08);
            border-radius: 50%;
            left: 35%;
            bottom: -100px;
        }

        .profile-content {
            padding: 0 30px 30px;
            position: relative;
        }

        .profile-avatar {
            width: 115px;
            height: 115px;
            border-radius: 50%;
            background: white;
            border: 6px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: -58px;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .profile-avatar i {
            font-size: 48px;
            color: #2563eb;
        }

        .profile-name {
            font-size: 26px;
            font-weight: 800;
            color: #1e293b;
            margin-top: 15px;
        }

        .profile-role {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .profile-description {
            color: #64748b;
            line-height: 1.8;
            margin-top: 15px;
            max-width: 650px;
        }

        /* Informasi */
        .info-card {
            background: white;
            border: none;
            border-radius: 16px;
            padding: 22px;
            height: 100%;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.05);
            transition: 0.3s;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.09);
        }

        .info-icon {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .icon-blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .icon-purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .icon-orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .icon-green {
            background: #dcfce7;
            color: #16a34a;
        }

        .info-title {
            font-size: 13px;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        /* Hobi */
        .hobby-card {
            background: white;
            border-radius: 18px;
            padding: 25px;
            border: none;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.05);
        }

        .hobby-item {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 13px;
            border: 1px solid #e2e8f0;
            transition: 0.2s;
        }

        .hobby-item:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .hobby-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .hobby-item h6 {
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }

        .hobby-item small {
            color: #94a3b8;
        }

        .section-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 18px;
        }

        .footer-text {
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
            margin-top: 30px;
        }

        @media (max-width: 767px) {
            .profile-content {
                padding: 0 20px 25px;
            }

            .profile-cover {
                height: 150px;
            }

            .profile-name {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- Header -->
            <div class="page-header">
                <h3><i class="fas fa-user-circle me-2 text-primary"></i>About Me</h3>
                <p class="text-secondary mb-0">
                    Kenali lebih dekat tentang diri saya.
                </p>
            </div>

            <!-- Profile Card -->
            <div class="profile-card mb-4">

                <div class="profile-cover"></div>

                <div class="profile-content">

                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>

                    <h4 class="profile-name">
                        <?= htmlspecialchars($nama); ?>
                    </h4>

                    <span class="profile-role">
                        <?= htmlspecialchars($role); ?>
                    </span>

                    <p class="profile-description">
                        Halo! Perkenalkan, saya merupakan seorang pelajar
                        di SMK Muhammadiyah Kota Tasikmalaya. Saya senang
                        mempelajari hal-hal baru, mencoba berbagai pengalaman,
                        dan mengembangkan kemampuan di bidang teknologi.
                    </p>

                </div>
            </div>

            <!-- Informasi Diri -->
            <h5 class="section-title">
                <i class="fas fa-id-card me-2 text-primary"></i>
                Informasi Diri
            </h5>

            <div class="row g-3 mb-4">

                <div class="col-md-6 col-xl-3">
                    <div class="info-card">
                        <div class="info-icon icon-blue">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="info-title">Status</div>
                        <p class="info-value">Pelajar</p>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="info-card">
                        <div class="info-icon icon-purple">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="info-title">Sekolah</div>
                        <p class="info-value">SMK Muhammadiyah</p>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="info-card">
                        <div class="info-icon icon-orange">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="info-title">Umur</div>
                        <p class="info-value">17 Tahun</p>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="info-card">
                        <div class="info-icon icon-green">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-title">Kota</div>
                        <p class="info-value">Tasikmalaya</p>
                    </div>
                </div>

            </div>

            <!-- Hobi -->
            <div class="hobby-card">

                <h5 class="section-title">
                    <i class="fas fa-heart me-2 text-danger"></i>
                    Hobi Saya
                </h5>

                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="hobby-item">
                            <div class="hobby-icon">
                                <i class="fas fa-gamepad"></i>
                            </div>
                            <div>
                                <h6>Main Game</h6>
                                <small>Menikmati waktu luang dengan bermain game.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="hobby-item">
                            <div class="hobby-icon">
                                <i class="fas fa-film"></i>
                            </div>
                            <div>
                                <h6>Nonton</h6>
                                <small>Menonton film, video, dan hiburan lainnya.</small>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="footer-text">
                <p>© <?= date('Y'); ?> About Me | SI PELANG</p>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
