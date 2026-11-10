
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin yang dapat mengakses
if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Menentukan halaman aktif untuk sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Memeriksa ID kategori
if (!isset($_GET['id'])) {
    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}

$id = (int) $_GET['id'];

// Mengambil data kategori
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_pelanggaran_kategori WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// Memeriksa data kategori
if (!$data) {
    die("Data kategori tidak ditemukan.");
}

// Mengamankan output
function aman_edit_kategori($teks) {
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Kategori Pelanggaran - SI PELANG</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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

        /* Card utama */
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Input form */
        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-label {
            color: #334155;
            font-size: 0.9rem;
        }

        /* Tombol */
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
            border-radius: 8px;
            padding: 10px 18px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        .btn-secondary {
            border-radius: 8px;
            padding: 10px 18px;
            font-weight: 600;
        }

        /* Judul halaman */
        .page-title {
            color: #1e293b;
            font-weight: 700;
        }

        .page-description {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Header card */
        .card-header-custom {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 18px 22px;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-body-custom {
            padding: 25px;
        }

        /* Tombol kembali */
        .btn-kembali {
            background-color: #64748b;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .btn-kembali:hover {
            background-color: #475569;
            color: #ffffff;
        }
        /* Warna Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        /* Link Sidebar */
        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: 0.2s;
        }

        /* Hover dan Menu Aktif */
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #2563eb;
            color: #ffffff;
        }

        /* Tombol Logout */
        .sidebar .nav-link.text-danger:hover {
            background-color: #dc2626;
            color: #ffffff !important;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten Utama -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- Judul Halaman -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                <div>
                    <h3 class="page-title mb-1">
                        Edit Kategori Pelanggaran
                    </h3>

                    <p class="page-description mb-0">
                        Perbarui informasi kategori pelanggaran siswa.
                    </p>
                </div>

                <a href="kelola_pelanggaran_kategori.php" class="btn-kembali">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- Card Form -->
            <div class="card-custom">

                <div class="card-header-custom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Form Edit Kategori
                    </h5>
                </div>

                <div class="card-body-custom">

                    <form action="proses_edit_pelanggaran_kategori.php"
                          method="POST"
                          id="formEditKategori">

                        <!-- ID Kategori -->
                        <input type="hidden"
                               name="id"
                               value="<?= (int) $data['id']; ?>">

                        <!-- Nama Kategori -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Kategori
                            </label>

                            <input type="text"
                                   name="nama"
                                   class="form-control"
                                   maxlength="100"
                                   value="<?= aman_edit_kategori($data['nama']); ?>"
                                   placeholder="Masukkan nama kategori"
                                   required>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Deskripsi
                            </label>

                            <textarea name="deskripsi"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Masukkan deskripsi kategori"
                                      required><?= aman_edit_kategori($data['deskripsi']); ?></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Status
                            </label>

                            <select name="status_aktif"
                                    class="form-select"
                                    required>

                                <option value="1"
                                    <?= $data['status_aktif'] == 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= $data['status_aktif'] == 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>

                            </select>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex gap-2 flex-wrap">

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Simpan Perubahan
                            </button>

                            <a href="kelola_pelanggaran_kategori.php"
                               class="btn btn-secondary">
                                <i class="fas fa-times-circle me-1"></i>
                                Batal
                            </a>

                        </div>

                    </form>

                </div>
            </div>

        </main>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript Validasi -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formEditKategori');

    form.addEventListener('submit', function (event) {

        const nama = document.querySelector('[name="nama"]').value;
        const deskripsi = document.querySelector('[name="deskripsi"]').value;

        // Memeriksa nama kategori
        if (nama.trim() === '') {
            event.preventDefault();
            alert('Nama kategori wajib diisi!');
            return;
        }

        // Memeriksa deskripsi
        if (deskripsi.trim() === '') {
            event.preventDefault();
            alert('Deskripsi kategori wajib diisi!');
            return;
        }

        // Konfirmasi penyimpanan
        if (!confirm('Apakah kamu yakin ingin menyimpan perubahan kategori pelanggaran?')) {
            event.preventDefault();
        }

    });

});
</script>

</body>
</html>
