
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Ambil data guru
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_guru WHERE id = ? LIMIT 1"
);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);

if (!$query || mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data guru tidak ditemukan!'); window.location='kelola_guru.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);
$id_guru_val = $data['id'];

function e($nilai) {
    return htmlspecialchars(
        (string)($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Guru - Pelanggaran Siswa</title>

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
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar Navigation -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">

                    <!-- Header Judul -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-pen-to-square text-warning"></i>
                                Edit Data Guru
                            </h2>
                            <p class="text-muted mb-0">
                                Ubah formulir di bawah ini untuk memperbarui data guru.
                            </p>
                        </div>

                        <a href="kelola_guru.php"
                           class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                           style="border-radius: 10px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    <!-- Card Form Edit -->
                    <div class="card card-custom p-4 p-md-5">

                        <form action="proses_edit_guru.php" method="POST">

                            <input type="hidden" name="id" value="<?php echo e($id_guru_val); ?>">

                            <div class="row g-4">

                                <!-- 1. NIP -->
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
                                        value="<?php echo e($data['nip'] ?? ''); ?>"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                        required
                                    >

                                    <div class="form-text">
                                        Maksimal 18 digit angka.
                                    </div>
                                </div>

                                <!-- 2. Jenis Kelamin -->
                                <div class="col-md-6">
                                    <label for="jenis_kelamin" class="form-label">
                                        Jenis Kelamin <span class="text-danger">*</span>
                                    </label>

                                    <?php $jk = $data['jenis_kelamin'] ?? ''; ?>

                                    <select
                                        class="form-select"
                                        id="jenis_kelamin"
                                        name="jenis_kelamin"
                                        required
                                    >
                                        <option value="Laki-laki" <?php echo ($jk == 'Laki-laki') ? 'selected' : ''; ?>>
                                            Laki-laki
                                        </option>

                                        <option value="Perempuan" <?php echo ($jk == 'Perempuan') ? 'selected' : ''; ?>>
                                            Perempuan
                                        </option>
                                    </select>
                                </div>

                                <!-- 3. Nama Guru -->
                                <div class="col-12">
                                    <label for="nama" class="form-label">
                                        Nama Lengkap & Gelar <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="nama"
                                        name="nama"
                                        maxlength="100"
                                        value="<?php
                                            echo e(
                                                $data['nama']
                                                ?? $data['nama_guru']
                                                ?? ''
                                            );
                                        ?>"
                                        required
                                    >
                                </div>

                                <!-- 4. Email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label">
                                        Email
                                    </label>

                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="<?php echo e($data['email'] ?? ''); ?>"
                                    >
                                </div>

                                <!-- 5. Status (Paling Akhir) -->
                                <div class="col-md-6">
                                    <label for="status_aktif" class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>

                                    <select
                                        class="form-select"
                                        id="status_aktif"
                                        name="status_aktif"
                                        required
                                    >
                                        <option value="1"
                                            <?php echo ((string)($data['status_aktif'] ?? '') === '1') ? 'selected' : ''; ?>>
                                            Aktif
                                        </option>

                                        <option value="0"
                                            <?php echo ((string)($data['status_aktif'] ?? '') === '0') ? 'selected' : ''; ?>>
                                            Tidak Aktif
                                        </option>
                                    </select>
                                </div>

                            </div>

                            <!-- Tombol Update & Batal -->
                            <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">

                                <a href="kelola_guru.php"
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
                                    <i class="fa-solid fa-pen-to-square me-1"></i>
                                    Update Data Guru
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
