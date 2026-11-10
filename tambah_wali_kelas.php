
<?php
// Menampilkan error jika terjadi masalah
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memanggil koneksi database
include 'config/koneksi.php';

// Memeriksa login
include 'includes/cek_session.php';

// Memeriksa hak akses admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

// =====================================
// 1. MENGAMBIL TAHUN AJARAN AKTIF
// =====================================
$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY id DESC"
);

// =====================================
// 2. MENGAMBIL KELAS YANG BELUM PUNYA
// WALI KELAS AKTIF
// =====================================
$kelas = mysqli_query(
    $koneksi,
    "SELECT k.id, k.nama
     FROM t_kelas k
     WHERE k.status_aktif = 1
     AND NOT EXISTS (
         SELECT 1
         FROM t_wali_kelas wk
         WHERE wk.kelas_id = k.id
         AND wk.status_aktif = 1
     )
     ORDER BY k.nama ASC"
);

// =====================================
// 3. MENGAMBIL GURU YANG BELUM MENJADI
// WALI KELAS AKTIF
// =====================================
$guru = mysqli_query(
    $koneksi,
    "SELECT g.id, g.nama
     FROM t_guru g
     WHERE g.status_aktif = 1
     AND NOT EXISTS (
         SELECT 1
         FROM t_wali_kelas wk
         WHERE wk.guru_id = g.id
         AND wk.status_aktif = 1
     )
     ORDER BY g.nama ASC"
);

// Memeriksa apakah query berhasil
if (!$tahun || !$kelas || !$guru) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Wali Kelas - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

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

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">

                    <!-- JUDUL HALAMAN -->
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>
                            <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-user-tie text-primary"></i>
                                Tambah Wali Kelas
                            </h2>

                            <p class="text-muted mb-0">
                                Isi formulir di bawah ini untuk menentukan wali kelas.
                            </p>
                        </div>

                        <a href="kelola_wali_kelas.php"
                           class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                           style="border-radius: 10px;">

                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Kembali

                        </a>

                    </div>

                    <!-- CARD FORM -->
                    <div class="card card-custom p-4 p-md-5">

                        <form action="proses_tambah_wali_kelas.php" method="POST">

                            <div class="row g-4">

                                <!-- TAHUN AJARAN -->
                                <div class="col-md-6">

                                    <label for="tahun_ajaran_id" class="form-label">
                                        Tahun Ajaran <span class="text-danger">*</span>
                                    </label>

                                    <select name="tahun_ajaran_id"
                                            id="tahun_ajaran_id"
                                            class="form-select"
                                            required>

                                        <option value="" selected disabled>
                                            -- Pilih Tahun Ajaran --
                                        </option>

                                        <?php while ($row = mysqli_fetch_assoc($tahun)) { ?>

                                            <option value="<?= htmlspecialchars((string)$row['id']) ?>">
                                                <?= htmlspecialchars($row['nama']) ?>
                                            </option>

                                        <?php } ?>

                                    </select>

                                </div>

                                <!-- KELAS -->
                                <div class="col-md-6">

                                    <label for="kelas_id" class="form-label">
                                        Kelas <span class="text-danger">*</span>
                                    </label>

                                    <select name="kelas_id"
                                            id="kelas_id"
                                            class="form-select"
                                            required>

                                        <option value="" selected disabled>
                                            -- Pilih Kelas --
                                        </option>

                                        <?php while ($row = mysqli_fetch_assoc($kelas)) { ?>

                                            <option value="<?= htmlspecialchars((string)$row['id']) ?>">
                                                <?= htmlspecialchars($row['nama']) ?>
                                            </option>

                                        <?php } ?>

                                    </select>

                                </div>

                                <!-- GURU -->
                                <div class="col-12">

                                    <label for="guru_id" class="form-label">
                                        Guru <span class="text-danger">*</span>
                                    </label>

                                    <select name="guru_id"
                                            id="guru_id"
                                            class="form-select"
                                            required>

                                        <option value="" selected disabled>
                                            -- Pilih Guru --
                                        </option>

                                        <?php while ($row = mysqli_fetch_assoc($guru)) { ?>

                                            <option value="<?= htmlspecialchars((string)$row['id']) ?>">
                                                <?= htmlspecialchars($row['nama']) ?>
                                            </option>

                                        <?php } ?>

                                    </select>

                                </div>

                                <!-- TANGGAL MULAI -->
                                <div class="col-md-6">

                                    <label for="tanggal_mulai" class="form-label">
                                        Tanggal Mulai <span class="text-danger">*</span>
                                    </label>

                                    <input type="date"
                                           name="tanggal_mulai"
                                           id="tanggal_mulai"
                                           class="form-control"
                                           required>

                                </div>

                                <!-- TANGGAL SELESAI -->
                                <div class="col-md-6">

                                    <label for="tanggal_selesai" class="form-label">
                                        Tanggal Selesai <span class="text-danger">*</span>
                                    </label>

                                    <input type="date"
                                           name="tanggal_selesai"
                                           id="tanggal_selesai"
                                           class="form-control"
                                           required>

                                </div>

                                <!-- STATUS -->
                                <div class="col-12">

                                    <label for="status_aktif" class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>

                                    <select name="status_aktif"
                                            id="status_aktif"
                                            class="form-select"
                                            required>

                                        <option value="1" selected>Aktif</option>
                                        <option value="0">Tidak Aktif</option>

                                    </select>

                                </div>

                            </div>

                            <!-- TOMBOL -->
                            <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">

                                <a href="kelola_wali_kelas.php"
                                   class="btn btn-outline-secondary px-4 fw-medium"
                                   style="border-radius: 8px;">

                                    Batal

                                </a>

                                <button type="submit"
                                        name="simpan"
                                        class="btn btn-primary px-4 fw-medium shadow-sm"
                                        style="border-radius: 8px; background-color: #2563eb;">

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
