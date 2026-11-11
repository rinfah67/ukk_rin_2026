
<?php
// tambah_siswa.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Halaman hanya untuk admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (Untuk Logo Sidebar) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
        }

        .sidebar {
            width: 250px !important;
            min-width: 250px;
            flex: 0 0 250px !important;
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar .brand-title {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            white-space: nowrap;
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: 0.2s;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .sidebar .nav-link i {
            width: 20px;
            min-width: 20px;
            text-align: center;
            font-size: 15px;
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

        .sidebar .menu-title {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 20px 12px 10px;
            letter-spacing: 1px;
        }

        .page-title {
            color: #1e293b;
            font-weight: 800;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
        }

        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 20px 25px;
        }

        .card-header-custom p {
            color: #dbeafe;
            font-size: 13px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 11px 14px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .input-icon {
            color: #2563eb;
            margin-right: 5px;
        }

        .btn-primary-custom {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 10px 22px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-primary-custom:hover {
            background: #1d4ed8;
            color: white;
        }

        .btn-back {
            background: white;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-cancel {
            border-radius: 9px;
            padding: 10px 22px;
            font-weight: 600;
        }

        .form-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            border-radius: 10px;
            padding: 13px 16px;
            font-size: 13px;
        }

        main {
            min-width: 0;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                width: 250px !important;
                min-width: 250px;
                flex: 0 0 250px !important;
            }
        }

        @media (max-width: 576px) {
            main {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .header-page {
                align-items: flex-start !important;
                gap: 12px;
            }

            .btn-back {
                white-space: nowrap;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <?php
        // Menampilkan sidebar
        $current_page = basename($_SERVER['PHP_SELF']);
        include 'layout/sidebar.php';
        ?>

        <main class="col px-md-4 py-4">

            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 header-page">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="fa-solid fa-user-plus text-primary me-2"></i>
                        Tambah Data Siswa
                    </h3>

                    <p class="page-subtitle mb-0">
                        Tambahkan data siswa baru ke dalam sistem SI PELANG.
                    </p>
                </div>

                <a href="kelola_siswa.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- Form Tambah Siswa -->
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">

                    <div class="card-custom">

                        <!-- Header Card -->
                        <div class="card-header-custom">
                            <h5 class="fw-bold mb-1">
                                <i class="fa-solid fa-clipboard-list me-2"></i>
                                Formulir Data Siswa
                            </h5>

                            <p class="mb-0">
                                Lengkapi seluruh informasi siswa dengan benar.
                            </p>
                        </div>

                        <div class="p-4 p-md-5">

                            <div class="form-info mb-4">
                                <i class="fa-solid fa-circle-info me-2"></i>
                                Pastikan NIS dan NISN yang dimasukkan benar dan belum digunakan.
                            </div>

                            <form action="proses_tambah_siswa.php" method="POST">

                                <div class="row g-4">

                                    <!-- NIS -->
                                    <div class="col-md-6">
                                        <label for="nis" class="form-label">
                                            <i class="fa-solid fa-id-card input-icon"></i>
                                            NIS <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            id="nis"
                                            name="nis"
                                            placeholder="Masukkan NIS"
                                            maxlength="15"
                                            inputmode="numeric"
                                            pattern="[0-9]{1,15}"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                                            required
                                        >

                                        <small class="text-muted">
                                            Maksimal 15 angka.
                                        </small>
                                    </div>

                                    <!-- NISN -->
                                    <div class="col-md-6">
                                        <label for="nisn" class="form-label">
                                            <i class="fa-solid fa-id-badge input-icon"></i>
                                            NISN <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            id="nisn"
                                            name="nisn"
                                            placeholder="Masukkan NISN"
                                            maxlength="10"
                                            inputmode="numeric"
                                            pattern="[0-9]{1,10}"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            required
                                        >

                                        <small class="text-muted">
                                            Maksimal 10 angka.
                                        </small>
                                    </div>

                                    <!-- Nama -->
                                    <div class="col-12">
                                        <label for="nama" class="form-label">
                                            <i class="fa-solid fa-user input-icon"></i>
                                            Nama Lengkap <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            id="nama"
                                            name="nama"
                                            placeholder="Masukkan nama lengkap siswa"
                                            required
                                        >
                                    </div>

                                    <!-- Jenis Kelamin -->
                                    <div class="col-md-6">
                                        <label for="jenis_kelamin" class="form-label">
                                            <i class="fa-solid fa-venus-mars input-icon"></i>
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

                                    <!-- Tanggal Lahir -->
                                    <div class="col-md-6">
                                        <label for="tanggal_lahir" class="form-label">
                                            <i class="fa-solid fa-calendar-days input-icon"></i>
                                            Tanggal Lahir <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            id="tanggal_lahir"
                                            name="tanggal_lahir"
                                            required
                                        >
                                    </div>

                                    <!-- Alamat -->
                                    <div class="col-12">
                                        <label for="alamat" class="form-label">
                                            <i class="fa-solid fa-location-dot input-icon"></i>
                                            Alamat Lengkap <span class="text-danger">*</span>
                                        </label>

                                        <textarea
                                            class="form-control"
                                            id="alamat"
                                            name="alamat"
                                            rows="4"
                                            placeholder="Masukkan alamat lengkap siswa..."
                                            required
                                        ></textarea>
                                    </div>

                                </div>

                                <!-- Tombol -->
                                <div class="d-flex justify-content-end gap-2 mt-5 pt-4 border-top">

                                    <a href="kelola_siswa.php"
                                       class="btn btn-outline-secondary btn-cancel">
                                        <i class="fa-solid fa-xmark me-1"></i>
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="simpan"
                                        class="btn-primary-custom"
                                    >
                                        <i class="fa-solid fa-floppy-disk me-1"></i>
                                        Simpan Data
                                    </button>

                                </div>

                            </form>

                        </div>
                    </div>

                </div>
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
