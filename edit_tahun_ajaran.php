
<?php
// edit_tahun_ajaran.php

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
// FUNGSI ESCAPE
// =====================================
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

// =====================================
// AMBIL ID
// =====================================
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

// =====================================
// AMBIL DATA TAHUN AJARAN
// =====================================
$stmt = mysqli_prepare($koneksi, "
    SELECT *
    FROM t_tahun_ajaran
    WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Tahun Ajaran - SI PELANG</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f1f5f9;
        }

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

        .card {
            border: none;
            border-radius: 12px;
        }

        .form-label {
            font-weight: 600;
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

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Edit Tahun Ajaran</h3>
                    <p class="text-muted mb-0">
                        Ubah informasi tahun ajaran.
                    </p>
                </div>

                <a href="kelola_tahun_ajaran.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- FORM EDIT -->
            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <form action="proses_edit_tahun_ajaran.php" method="POST">

                        <input type="hidden" name="id" value="<?= e($data['id']); ?>">

                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">

                        <!-- NAMA TAHUN AJARAN -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                Nama Tahun Ajaran
                            </label>

                            <input
                                type="text"
                                name="nama"
                                id="nama"
                                class="form-control"
                                value="<?= e($data['nama']); ?>"
                                placeholder="Contoh: 2026/2027"
                                maxlength="20"
                                required
                            >
                        </div>

                        <!-- TANGGAL MULAI -->
                        <div class="mb-3">
                            <label for="tanggal_mulai" class="form-label">
                                Tanggal Mulai
                            </label>

                            <input
                                type="date"
                                name="tanggal_mulai"
                                id="tanggal_mulai"
                                class="form-control"
                                value="<?= e($data['tanggal_mulai']); ?>"
                                required
                            >
                        </div>

                        <!-- TANGGAL SELESAI -->
                        <div class="mb-3">
                            <label for="tanggal_selesai" class="form-label">
                                Tanggal Selesai
                            </label>

                            <input
                                type="date"
                                name="tanggal_selesai"
                                id="tanggal_selesai"
                                class="form-control"
                                value="<?= e($data['tanggal_selesai']); ?>"
                                required
                            >
                        </div>

                        <!-- STATUS -->
                        <div class="mb-4">
                            <label for="status_aktif" class="form-label">
                                Status Tahun Ajaran
                            </label>

                            <select
                                name="status_aktif"
                                id="status_aktif"
                                class="form-select"
                                required
                            >
                                <option value="1"
                                    <?= $data['status_aktif'] == 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= $data['status_aktif'] == 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>
                            </select>

                            <small class="text-muted">
                                Hanya boleh ada satu tahun ajaran yang berstatus aktif.
                            </small>
                        </div>

                        <!-- TOMBOL -->
                        <div class="d-flex justify-content-end gap-2">

                            <a href="kelola_tahun_ajaran.php" class="btn btn-secondary">
                                Batal
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
