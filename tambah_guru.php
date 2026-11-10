
<?php
// Tampilkan error jika terjadi masalah di server
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek file koneksi
if (file_exists('config/koneksi.php')) {
    include 'config/koneksi.php';
} else {
    die("Error: File config/koneksi.php tidak ditemukan!");
}

// Cek session
include 'includes/cek_session.php';

// Cek hak akses admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru - Pelanggaran Siswa</title>

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
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #2563eb;
        }

        .circle-r {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border: 2px solid currentColor;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
        }

        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
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
            echo "<div class='col-md-3 p-3 bg-dark text-white'>Sidebar tidak ditemukan</div>";
        }
        ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">

                    <!-- Judul Halaman -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-user-plus text-primary"></i>
                                Tambah Data Guru
                            </h2>

                            <p class="text-muted mb-0">
                                Isi formulir di bawah ini dengan data guru yang valid.
                            </p>
                        </div>

                        <a href="kelola_guru.php"
                           class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                           style="border-radius: 10px;">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Kembali
                        </a>
                    </div>

                    <!-- Card Form -->
                    <div class="card card-custom p-4 p-md-5">

                        <form action="proses_tambah_guru.php" method="POST">

                            <div class="row g-4">

                                <!-- NIP -->
                                <div class="col-md-6">
                                    <label for="nip" class="form-label">
                                        NIP <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="nip"
                                        name="nip"
                                        maxlength="18"
                                        placeholder="Masukkan NIP guru"
                                        required
                                    >
                                </div>

                                <!-- Jenis Kelamin -->
                                <div class="col-md-6">
                                    <label for="jenis_kelamin" class="form-label">
                                        Jenis Kelamin <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        class="form-select"
                                        id="jenis_kelamin"
                                        name="jenis_kelamin"
                                        required
                                    >
                                        <option value="" selected disabled>
                                            -- Pilih Jenis Kelamin --
                                        </option>

                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>

                                <!-- Nama -->
                                <div class="col-12">
                                    <label for="nama" class="form-label">
                                        Nama Lengkap dan Gelar <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="nama"
                                        name="nama"
                                        maxlength="150"
                                        placeholder="Masukkan nama lengkap beserta gelar"
                                        required
                                    >
                                </div>

                                <!-- Email -->
                                <div class="col-12">
                                    <label for="email" class="form-label">
                                        Email <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        maxlength="150"
                                        placeholder="Masukkan email guru"
                                        required
                                    >
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label">
                                        Password <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        minlength="6"
                                        placeholder="Masukkan password"
                                        required
                                    >
                                </div>

                                <!-- Konfirmasi Password -->
                                <div class="col-md-6">
                                    <label for="konfirmasi_password" class="form-label">
                                        Konfirmasi Password <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="konfirmasi_password"
                                        name="konfirmasi_password"
                                        minlength="6"
                                        placeholder="Ulangi password"
                                        required
                                    >
                                </div>

                                <!-- Status -->
                                <div class="col-12">
                                    <label for="status_aktif" class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        class="form-select"
                                        id="status_aktif"
                                        name="status_aktif"
                                        required
                                    >
                                        <option value="1" selected>Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>

                            </div>

                            <!-- Tombol -->
                            <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">

                                <a href="kelola_guru.php"
                                   class="btn btn-outline-secondary px-4 fw-medium"
                                   style="border-radius: 8px;">
                                    Batal
                                </a>

                                <button
                                    type="submit"
                                    name="simpan"
                                    class="btn btn-primary px-4 fw-medium shadow-sm"
                                    style="border-radius: 8px; background-color: #2563eb;"
                                >
                                    <i class="fa-solid fa-floppy-disk me-1"></i>
                                    Simpan Data
                                </button>

                            </div>

                        </form>

                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
