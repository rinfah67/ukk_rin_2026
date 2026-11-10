
<?php
// edit_penempatan_siswa.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// =====================================
// CEK HAK AKSES
// =====================================
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// =====================================
// TOKEN KEAMANAN
// =====================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// =====================================
// FUNGSI AMANKAN OUTPUT
// =====================================
function e($nilai) {
    return htmlspecialchars(
        (string)($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// =====================================
// CEK ID PENEMPATAN
// =====================================
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['pesan_error'] = "ID penempatan tidak valid!";
    header("Location: penempatan_siswa.php");
    exit;
}

// =====================================
// AMBIL DATA PENEMPATAN
// =====================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT *
     FROM t_kelas_siswa
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['pesan_error'] = "Data penempatan tidak ditemukan!";
    header("Location: penempatan_siswa.php");
    exit;
}

// =====================================
// AMBIL DATA SISWA
// =====================================
$stmt_siswa = mysqli_prepare(
    $koneksi,
    "SELECT s.id, s.nis, s.nama
     FROM t_siswa s
     WHERE s.status_aktif = 1
     AND (
        s.id = ?
        OR NOT EXISTS (
            SELECT 1
            FROM t_kelas_siswa ks
            WHERE ks.siswa_id = s.id
            AND ks.tahun_ajaran_id = ?
            AND ks.status_aktif = 1
            AND ks.id != ?
        )
     )
     ORDER BY s.nama ASC"
);

mysqli_stmt_bind_param(
    $stmt_siswa,
    "iii",
    $data['siswa_id'],
    $data['tahun_ajaran_id'],
    $id
);

mysqli_stmt_execute($stmt_siswa);
$siswa = mysqli_stmt_get_result($stmt_siswa);

// =====================================
// AMBIL DATA TAHUN AJARAN
// =====================================
$stmt_tahun = mysqli_prepare(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1 OR id = ?
     ORDER BY id DESC"
);

mysqli_stmt_bind_param(
    $stmt_tahun,
    "i",
    $data['tahun_ajaran_id']
);

mysqli_stmt_execute($stmt_tahun);
$tahun = mysqli_stmt_get_result($stmt_tahun);

// =====================================
// AMBIL DATA KELAS
// =====================================
$stmt_kelas = mysqli_prepare(
    $koneksi,
    "SELECT id, nama
     FROM t_kelas
     WHERE status_aktif = 1 OR id = ?
     ORDER BY nama ASC"
);

mysqli_stmt_bind_param(
    $stmt_kelas,
    "i",
    $data['kelas_id']
);

mysqli_stmt_execute($stmt_kelas);
$kelas = mysqli_stmt_get_result($stmt_kelas);

// =====================================
// DATA FORM
// =====================================
$tanggal_mulai = $data['tanggal_mulai'] ?? '';
$tanggal_selesai = $data['tanggal_selesai'] ?? '';

if ($tanggal_mulai === '0000-00-00') {
    $tanggal_mulai = '';
}

if (
    $tanggal_selesai === '0000-00-00' ||
    $tanggal_selesai === '9999-12-31'
) {
    $tanggal_selesai = '';
}

$status_aktif = (int) $data['status_aktif'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Penempatan Siswa - Si Pelang</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
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
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
    </style>
</head>

<body>

<div class="container-fluid">

    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <div class="row justify-content-center">

                <div class="col-xl-9 col-lg-10">

                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>
                            <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-pen-to-square text-warning"></i>
                                Edit Data Penempatan Siswa
                            </h2>

                            <p class="text-muted mb-0">
                                Ubah informasi penempatan siswa yang sudah tersimpan.
                            </p>
                        </div>

                        <a href="penempatan_siswa.php"
                           class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                           style="border-radius: 10px;">

                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Kembali

                        </a>

                    </div>

                    <!-- FORM EDIT -->
                    <div class="card card-custom p-4 p-md-5">

                        <form action="proses_edit_penempatan.php" method="POST">

                            <input type="hidden" name="id"
                                   value="<?= e($data['id']); ?>">

                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION['csrf_token']); ?>">

                            <div class="row g-4">

                                <!-- SISWA -->
                                <div class="col-md-6">

                                    <label for="siswa_id" class="form-label">
                                        Nama Siswa <span class="text-danger">*</span>
                                    </label>

                                    <select class="form-select"
                                            id="siswa_id"
                                            name="siswa_id"
                                            required>

                                        <option value="">-- Pilih Siswa --</option>

                                        <?php while ($row = mysqli_fetch_assoc($siswa)): ?>

                                            <option
                                                value="<?= (int) $row['id']; ?>"
                                                <?= (int) $row['id'] === (int) $data['siswa_id'] ? 'selected' : ''; ?>>

                                                <?= e($row['nis']); ?> -
                                                <?= e($row['nama']); ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <!-- TAHUN AJARAN -->
                                <div class="col-md-6">

                                    <label for="tahun_ajaran_id" class="form-label">
                                        Tahun Ajaran <span class="text-danger">*</span>
                                    </label>

                                    <select class="form-select"
                                            id="tahun_ajaran_id"
                                            name="tahun_ajaran_id"
                                            required>

                                        <option value="">-- Pilih Tahun Ajaran --</option>

                                        <?php while ($row = mysqli_fetch_assoc($tahun)): ?>

                                            <option
                                                value="<?= (int) $row['id']; ?>"
                                                <?= (int) $row['id'] === (int) $data['tahun_ajaran_id'] ? 'selected' : ''; ?>>

                                                <?= e($row['nama']); ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <!-- KELAS -->
                                <div class="col-md-6">

                                    <label for="kelas_id" class="form-label">
                                        Kelas <span class="text-danger">*</span>
                                    </label>

                                    <select class="form-select"
                                            id="kelas_id"
                                            name="kelas_id"
                                            required>

                                        <option value="">-- Pilih Kelas --</option>

                                        <?php while ($row = mysqli_fetch_assoc($kelas)): ?>

                                            <option
                                                value="<?= (int) $row['id']; ?>"
                                                <?= (int) $row['id'] === (int) $data['kelas_id'] ? 'selected' : ''; ?>>

                                                <?= e($row['nama']); ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <!-- STATUS -->
                                <div class="col-md-6">

                                    <label for="status_aktif" class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>

                                    <select class="form-select"
                                            id="status_aktif"
                                            name="status_aktif"
                                            required>

                                        <option value="1"
                                            <?= $status_aktif === 1 ? 'selected' : ''; ?>>
                                            Aktif
                                        </option>

                                        <option value="0"
                                            <?= $status_aktif === 0 ? 'selected' : ''; ?>>
                                            Tidak Aktif
                                        </option>

                                    </select>

                                </div>

                                <!-- TANGGAL MULAI -->
                                <div class="col-md-6">

                                    <label for="tanggal_mulai" class="form-label">
                                        Tanggal Mulai <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="date"
                                        class="form-control"
                                        id="tanggal_mulai"
                                        name="tanggal_mulai"
                                        value="<?= e($tanggal_mulai); ?>"
                                        required
                                    >

                                </div>

                                <!-- TANGGAL SELESAI -->
                                <div class="col-md-6">

                                    <label for="tanggal_selesai" class="form-label">
                                        Tanggal Selesai
                                    </label>

                                    <input
                                        type="date"
                                        class="form-control"
                                        id="tanggal_selesai"
                                        name="tanggal_selesai"
                                        value="<?= e($tanggal_selesai); ?>"
                                    >

                                    <small class="text-muted">
                                        Kosongkan jika penempatan masih berlangsung.
                                    </small>

                                </div>

                            </div>

                            <!-- TOMBOL -->
                            <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">

                                <a href="penempatan_siswa.php"
                                   class="btn btn-outline-secondary px-4 fw-medium"
                                   style="border-radius: 8px;">

                                    Batal

                                </a>

                                <button
                                    type="submit"
                                    name="update"
                                    class="btn btn-warning px-4 fw-medium shadow-sm text-dark"
                                    style="border-radius: 8px;"
                                >

                                    <i class="fa-solid fa-floppy-disk me-1"></i>
                                    Update Data

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
