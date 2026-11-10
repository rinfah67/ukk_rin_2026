
<?php
// edit_penempatan_siswa.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Memastikan hanya admin yang bisa mengakses
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Membuat token keamanan
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fungsi mengamankan output
function e($nilai)
{
    return htmlspecialchars(
        (string) ($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Mengambil ID penempatan
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['pesan_error'] = "ID penempatan tidak valid.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Mengambil data penempatan
$stmt = mysqli_prepare($koneksi, "
    SELECT *
    FROM t_kelas_siswa
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['pesan_error'] = "Data penempatan tidak ditemukan.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Mengambil siswa aktif dan siswa yang sedang diedit
$stmt_siswa = mysqli_prepare($koneksi, "
    SELECT s.id, s.nis, s.nama
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
    ORDER BY s.nama ASC
");

mysqli_stmt_bind_param(
    $stmt_siswa,
    "iii",
    $data['siswa_id'],
    $data['tahun_ajaran_id'],
    $id
);

mysqli_stmt_execute($stmt_siswa);
$siswa = mysqli_stmt_get_result($stmt_siswa);

// Mengambil tahun ajaran aktif dan tahun ajaran yang sedang diedit
$stmt_tahun = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    OR id = ?
    ORDER BY id DESC
");

mysqli_stmt_bind_param(
    $stmt_tahun,
    "i",
    $data['tahun_ajaran_id']
);

mysqli_stmt_execute($stmt_tahun);
$tahun = mysqli_stmt_get_result($stmt_tahun);

// Mengambil kelas aktif dan kelas yang sedang digunakan
$stmt_kelas = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_kelas
    WHERE status_aktif = 1
    OR id = ?
    ORDER BY nama ASC
");

mysqli_stmt_bind_param(
    $stmt_kelas,
    "i",
    $data['kelas_id']
);

mysqli_stmt_execute($stmt_kelas);
$kelas = mysqli_stmt_get_result($stmt_kelas);

// Menyesuaikan tanggal untuk input date
$tanggal_mulai = $data['tanggal_mulai'] ?? '';

$tanggal_selesai = $data['tanggal_selesai'] ?? '';

if (
    $tanggal_selesai === '0000-00-00' ||
    $tanggal_selesai === '9999-12-31'
) {
    $tanggal_selesai = '';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Penempatan Siswa | SI PELANG</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Google Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            color: #334155;
        }

        .page-title {
            color: #1e293b;
            font-weight: 700;
        }

        .page-description {
            color: #64748b;
            font-size: 13px;
        }

        .page-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background-color: #dbeafe;
            color: #2563eb;
            font-size: 22px;
        }

        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .card-header-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 24px;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-body-custom {
            padding: 24px;
        }

        .form-label {
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #334155;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-text {
            color: #94a3b8;
            font-size: 11px;
        }

        .btn {
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        .btn-outline-secondary {
            border-color: #cbd5e1;
            color: #475569;
        }

        .btn-outline-secondary:hover {
            background-color: #e2e8f0;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .form-footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            main {
                padding: 20px !important;
            }

            .card-body-custom {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- Header halaman -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="page-icon">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>

                    <div>
                        <h3 class="page-title mb-1">
                            Edit Penempatan Siswa
                        </h3>

                        <p class="page-description mb-0">
                            Ubah informasi penempatan siswa.
                        </p>
                    </div>

                </div>

                <a href="penempatan_siswa.php"
                   class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- Card form -->
            <div class="card card-custom">

                <div class="card-header-custom">

                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-file-pen text-primary me-2"></i>
                        Form Edit Penempatan
                    </h5>

                    <p class="page-description mb-0">
                        Silakan ubah data penempatan siswa sesuai kebutuhan.
                    </p>

                </div>

                <div class="card-body-custom">

                    <form action="proses_edit_penempatan.php"
                          method="POST">

                        <input type="hidden"
                               name="id"
                               value="<?= (int) $data['id']; ?>">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= e($_SESSION['csrf_token']); ?>">

                        <div class="row">

                            <!-- Siswa -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Siswa <span class="text-danger">*</span>
                                </label>

                                <select name="siswa_id"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Pilih Siswa --
                                    </option>

                                    <?php while ($row = mysqli_fetch_assoc($siswa)): ?>

                                        <option
                                            value="<?= (int) $row['id']; ?>"
                                            <?= (int) $row['id'] === (int) $data['siswa_id'] ? 'selected' : ''; ?>>

                                            <?= e($row['nis']); ?> -
                                            <?= e($row['nama']); ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                                <div class="form-text">
                                    Pilih siswa yang akan ditempatkan.
                                </div>

                            </div>

                            <!-- Tahun Ajaran -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Tahun Ajaran <span class="text-danger">*</span>
                                </label>

                                <select name="tahun_ajaran_id"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Pilih Tahun Ajaran --
                                    </option>

                                    <?php while ($row = mysqli_fetch_assoc($tahun)): ?>

                                        <option
                                            value="<?= (int) $row['id']; ?>"
                                            <?= (int) $row['id'] === (int) $data['tahun_ajaran_id'] ? 'selected' : ''; ?>>

                                            <?= e($row['nama']); ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                                <div class="form-text">
                                    Pilih tahun ajaran penempatan siswa.
                                </div>

                            </div>

                            <!-- Kelas -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Kelas <span class="text-danger">*</span>
                                </label>

                                <select name="kelas_id"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        -- Pilih Kelas --
                                    </option>

                                    <?php while ($row = mysqli_fetch_assoc($kelas)): ?>

                                        <option
                                            value="<?= (int) $row['id']; ?>"
                                            <?= (int) $row['id'] === (int) $data['kelas_id'] ? 'selected' : ''; ?>>

                                            <?= e($row['nama']); ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                                <div class="form-text">
                                    Pilih kelas untuk siswa.
                                </div>

                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="date"
                                    name="tanggal_mulai"
                                    class="form-control"
                                    value="<?= e($tanggal_mulai); ?>"
                                    required
                                >

                                <div class="form-text">
                                    Tanggal mulai penempatan siswa.
                                </div>

                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Tanggal Selesai
                                </label>

                                <input
                                    type="date"
                                    name="tanggal_selesai"
                                    class="form-control"
                                    value="<?= e($tanggal_selesai); ?>"
                                >

                                <div class="form-text">
                                    Kosongkan jika penempatan masih berlangsung.
                                </div>

                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Status <span class="text-danger">*</span>
                                </label>

                                <select name="status_aktif"
                                        class="form-select"
                                        required>

                                    <option value="1"
                                        <?= (int) $data['status_aktif'] === 1 ? 'selected' : ''; ?>>
                                        Aktif
                                    </option>

                                    <option value="0"
                                        <?= (int) $data['status_aktif'] === 0 ? 'selected' : ''; ?>>
                                        Tidak Aktif
                                    </option>

                                </select>

                                <div class="form-text">
                                    Tentukan status penempatan siswa.
                                </div>

                            </div>

                        </div>

                        <!-- Tombol -->
                        <div class="form-footer d-flex flex-wrap gap-2">

                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i>
                                Simpan Perubahan
                            </button>

                            <a href="penempatan_siswa.php"
                               class="btn btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i>
                                Batal
                            </a>

                        </div>

                    </form>

                </div>

            </div>

            <div class="text-secondary small mt-4">
                SI PELANG &copy; <?= date('Y'); ?>
            </div>

        </main>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
