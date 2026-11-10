
<?php
// tambah_pelanggaran_kategori.php

include 'includes/cek_session.php';

// Menentukan halaman aktif untuk sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Kategori Pelanggaran - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Styling Sidebar */
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

        .sidebar .nav-link.text-danger:hover {
            background-color: #ef4444;
            color: #fff !important;
        }

        /* Card Form */
        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        /* Input Form */
        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: #334155;
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

            <!-- HEADER HALAMAN -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-folder-plus text-primary"></i>
                        Tambah Kategori Pelanggaran
                    </h2>

                    <p class="text-muted mb-0">
                        Tambahkan data kategori pelanggaran siswa.
                    </p>
                </div>

                <a href="kelola_pelanggaran_kategori.php"
                   class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                   style="border-radius: 10px;">

                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- NOTIFIKASI ERROR -->
            <?php if (isset($_SESSION['pesan_error'])) : ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <i class="fa-solid fa-circle-exclamation me-2"></i>

                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

                <?php unset($_SESSION['pesan_error']); ?>

            <?php endif; ?>

            <!-- NOTIFIKASI SUKSES -->
            <?php if (isset($_SESSION['pesan_sukses'])) : ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">

                    <i class="fa-solid fa-circle-check me-2"></i>

                    <?= htmlspecialchars($_SESSION['pesan_sukses']); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

                <?php unset($_SESSION['pesan_sukses']); ?>

            <?php endif; ?>

            <!-- CARD FORM -->
            <div class="card card-custom p-4">

                <div class="border-bottom pb-3 mb-4">

                    <h5 class="fw-bold text-dark mb-1">
                        Form Tambah Kategori
                    </h5>

                    <p class="text-muted small mb-0">
                        Lengkapi informasi kategori pelanggaran di bawah ini.
                    </p>

                </div>

                <!-- FORM TAMBAH -->
                <form action="proses_tambah_pelanggaran_kategori.php"
                      method="POST">

                    <!-- NAMA KATEGORI -->
                    <div class="mb-4">

                        <label for="nama" class="form-label">
                            Nama Kategori
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="nama"
                               id="nama"
                               class="form-control"
                               placeholder="Contoh: Pelanggaran Ringan"
                               maxlength="100"
                               required>

                    </div>

                    <!-- DESKRIPSI -->
                    <div class="mb-4">

                        <label for="deskripsi" class="form-label">
                            Deskripsi
                        </label>

                        <textarea name="deskripsi"
                                  id="deskripsi"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Masukkan deskripsi kategori pelanggaran"></textarea>

                    </div>

                    <!-- STATUS -->
                    <div class="mb-4">

                        <label for="status_aktif" class="form-label">
                            Status Aktif
                            <span class="text-danger">*</span>
                        </label>

                        <select name="status_aktif"
                                id="status_aktif"
                                class="form-select"
                                required>

                            <option value="1" selected>Aktif</option>
                            <option value="0">Tidak Aktif</option>

                        </select>

                    </div>

                    <!-- TOMBOL -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-3">

                        <a href="kelola_pelanggaran_kategori.php"
                           class="btn btn-light border px-4"
                           style="border-radius: 8px;">

                            <i class="fa-solid fa-xmark me-1"></i>
                            Batal
                        </a>

                        <button type="submit"
                                class="btn btn-primary px-4"
                                style="background-color: #2563eb; border-radius: 8px;">

                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Simpan Kategori
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
