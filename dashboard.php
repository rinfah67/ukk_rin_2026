
<?php
// Memulai session dan memeriksa login
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Mengambil jumlah seluruh siswa
$query_siswa = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM t_siswa
");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total'];

// Mengambil jumlah guru yang memiliki akun guru
$query_guru = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_guru g
    INNER JOIN t_users u ON g.user_id = u.id
    WHERE u.role = 'guru'
");
$data_guru = mysqli_fetch_assoc($query_guru);
$total_guru = $data_guru['total'];

// Mengambil jumlah kelas
$query_kelas = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM t_kelas
");
$data_kelas = mysqli_fetch_assoc($query_kelas);
$total_kelas = $data_kelas['total'];

// Mengambil jumlah tahun ajaran
$query_tahun = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM t_tahun_ajaran
");
$data_tahun = mysqli_fetch_assoc($query_tahun);
$total_tahun = $data_tahun['total'];

// Mengambil jumlah catatan pelanggaran
$query_pelanggaran = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM t_pelanggaran_siswa
");
$data_pelanggaran = mysqli_fetch_assoc($query_pelanggaran);
$total_pelanggaran = $data_pelanggaran['total'];

// Mengambil total poin pelanggaran
$query_poin = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(poin), 0) AS total
    FROM t_pelanggaran_siswa
");
$data_poin = mysqli_fetch_assoc($query_poin);
$total_poin = $data_poin['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - SI PELANG</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include "layout/sidebar.php"; ?>

        <!-- Konten Dashboard -->
        <main class="col-md-9 col-lg-10 p-4">

            <!-- Judul -->
            <div class="mb-4">
                <h2 class="fw-bold">Dashboard</h2>
                <p class="text-secondary mb-0">
                    Selamat datang,
                    <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?></strong>
                    di Sistem Informasi Pelanggaran Siswa.
                </p>
            </div>

            <!-- Notifikasi berhasil tambah guru -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'guru_berhasil') { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Guru dan akun login berhasil ditambahkan.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- Notifikasi berhasil tambah siswa -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'siswa_berhasil') { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Data siswa berhasil ditambahkan.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- Ringkasan Data -->
            <div class="row g-3">

                <!-- Data Siswa -->
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-secondary mb-1">Total Siswa</p>
                                    <h3 class="fw-bold mb-0">
                                        <?= $total_siswa; ?>
                                    </h3>
                                </div>
                                <div class="bg-primary-subtle text-primary rounded p-3">
                                    <i class="bi bi-people-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Guru -->
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-secondary mb-1">Total Guru</p>
                                    <h3 class="fw-bold mb-0">
                                        <?= $total_guru; ?>
                                    </h3>
                                </div>
                                <div class="bg-success-subtle text-success rounded p-3">
                                    <i class="bi bi-person-badge-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Kelas -->
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-secondary mb-1">Total Kelas</p>
                                    <h3 class="fw-bold mb-0">
                                        <?= $total_kelas; ?>
                                    </h3>
                                </div>
                                <div class="bg-warning-subtle text-warning rounded p-3">
                                    <i class="bi bi-building fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tahun Ajaran -->
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-secondary mb-1">Tahun Ajaran</p>
                                    <h3 class="fw-bold mb-0">
                                        <?= $total_tahun; ?>
                                    </h3>
                                </div>
                                <div class="bg-info-subtle text-info rounded p-3">
                                    <i class="bi bi-calendar-event-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Pelanggaran -->
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-secondary mb-1">Catatan Pelanggaran</p>
                                    <h3 class="fw-bold mb-0">
                                        <?= $total_pelanggaran; ?>
                                    </h3>
                                </div>
                                <div class="bg-danger-subtle text-danger rounded p-3">
                                    <i class="bi bi-journal-text fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Poin -->
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-secondary mb-1">Total Poin Pelanggaran</p>
                                    <h3 class="fw-bold mb-0">
                                        <?= $total_poin; ?>
                                    </h3>
                                </div>
                                <div class="bg-secondary-subtle text-secondary rounded p-3">
                                    <i class="bi bi-clipboard-data-fill fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Informasi -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="fw-bold">
                        <i class="bi bi-info-circle me-2"></i>
                        Informasi Sistem
                    </h5>
                    <p class="text-secondary mb-0">
                        SI PELANG merupakan sistem informasi yang digunakan
                        untuk mengelola data siswa, guru, serta pencatatan
                        dan rekapitulasi pelanggaran siswa.
                    </p>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
