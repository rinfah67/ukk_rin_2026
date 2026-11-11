
<?php
// edit_catatan.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// =====================================
// CEK HAK AKSES
// =====================================

if (!in_array($_SESSION['role'] ?? '', ['admin', 'guru'])) {
    header("Location: dashboard.php");
    exit;
}

// =====================================
// MENGAMBIL ID CATATAN
// =====================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: kelola_catatan_pelanggaran.php");
    exit;
}

// =====================================
// MENGAMBIL DATA CATATAN
// =====================================

$stmt = mysqli_prepare($koneksi, "
    SELECT id, tanggal, keterangan, tindakan, status
    FROM t_pelanggaran_siswa
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Query gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    die("Catatan pelanggaran tidak ditemukan.");
}

// =====================================
// MENENTUKAN HALAMAN SIDEBAR
// =====================================

$current_page = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Catatan Pelanggaran - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        main {
            min-width: 0;
        }

        .page-title {
            font-size: 25px;
            font-weight: 800;
            color: #1e293b;
        }

        .page-subtitle {
            font-size: 14px;
            color: #64748b;
        }

        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 11px 13px;
            font-size: 13px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-simpan {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 10px 18px;
            font-weight: 600;
        }

        .btn-simpan:hover {
            background: #1d4ed8;
            color: white;
        }

        .btn-kembali {
            border-radius: 9px;
            padding: 10px 18px;
            font-weight: 600;
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row flex-nowrap">

        <!-- SIDEBAR -->

        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->

        <main class="col px-md-4 py-4">

            <!-- JUDUL HALAMAN -->

            <div class="mb-4">

                <h4 class="page-title mb-1">
                    <i class="bi bi-pencil-square text-primary me-2"></i>
                    Edit Catatan Pelanggaran
                </h4>

                <p class="page-subtitle mb-0">
                    Perbarui informasi catatan pelanggaran siswa.
                </p>

            </div>

            <!-- FORM EDIT -->

            <div class="card card-custom">

                <div class="card-body p-4">

                    <form action="proses_edit_catatan.php" method="POST">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $data['id']; ?>"
                        >

                        <!-- TANGGAL -->

                        <div class="mb-3">

                            <label for="tanggal" class="form-label">
                                Tanggal Pelanggaran
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="tanggal"
                                id="tanggal"
                                class="form-control"
                                value="<?= htmlspecialchars($data['tanggal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >

                        </div>

                        <!-- KETERANGAN -->

                        <div class="mb-3">

                            <label for="keterangan" class="form-label">
                                Keterangan
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="keterangan"
                                id="keterangan"
                                class="form-control"
                                rows="4"
                                placeholder="Masukkan keterangan pelanggaran..."
                                required
                            ><?= htmlspecialchars($data['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                        </div>

                        <!-- TINDAKAN -->

                        <div class="mb-3">

                            <label for="tindakan" class="form-label">
                                Tindakan
                            </label>

                            <textarea
                                name="tindakan"
                                id="tindakan"
                                class="form-control"
                                rows="4"
                                placeholder="Masukkan tindakan yang diberikan..."
                            ><?= htmlspecialchars($data['tindakan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                        </div>

                        <!-- STATUS -->

                        <div class="mb-4">

                            <label for="status" class="form-label">
                                Status
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-select"
                                required
                            >

                                <?php
                                $statuses = [
                                    'Belum Ditindaklanjuti',
                                    'Diproses',
                                    'Selesai'
                                ];

                                foreach ($statuses as $status):
                                ?>

                                    <option
                                        value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?= ($data['status'] ?? '') === $status ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <!-- TOMBOL -->

                        <div class="d-flex justify-content-end gap-2">

                            <a
                                href="kelola_catatan_pelanggaran.php"
                                class="btn btn-outline-secondary btn-kembali"
                            >
                                <i class="bi bi-arrow-left me-1"></i>
                                Kembali
                            </a>

                            <button type="submit" class="btn btn-simpan">
                                <i class="bi bi-save me-1"></i>
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </main>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
