
<?php
// edit_jenis_pelanggaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Menentukan halaman aktif untuk sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// Mengambil ID jenis pelanggaran
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['pesan_error'] = 'ID jenis pelanggaran tidak valid.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

// Mengambil data jenis pelanggaran
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_pelanggaran WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

// Memeriksa data
if (!$data) {
    $_SESSION['pesan_error'] = 'Data jenis pelanggaran tidak ditemukan.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

// Mengambil kategori aktif dan kategori yang sedang digunakan
$sql_kategori = "SELECT id, nama, status_aktif
                 FROM t_pelanggaran_kategori
                 WHERE status_aktif = 1
                    OR id = " . (int) $data['pelanggaran_kategori_id'] . "
                 ORDER BY nama ASC";

$kategori = mysqli_query($koneksi, $sql_kategori);

if (!$kategori) {
    die("Gagal mengambil kategori: " .
        htmlspecialchars(mysqli_error($koneksi)));
}

// Fungsi mengamankan output
function aman_jenis_pelanggaran($teks) {
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Jenis Pelanggaran - SI PELANG</title>

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

        /* Sidebar */
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

        /* Card */
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .card-header-custom {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 18px 22px;
            border-radius: 16px 16px 0 0;
        }

        .card-body-custom {
            padding: 25px;
        }

        /* Judul */
        .page-title {
            color: #1e293b;
            font-weight: 700;
        }

        .page-description {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Form */
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
                        Edit Jenis Pelanggaran
                    </h3>

                    <p class="page-description mb-0">
                        Perbarui informasi jenis pelanggaran siswa.
                    </p>
                </div>

                <a href="kelola_jenis_pelanggaran.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- Pesan Error -->
            <?php if (isset($_SESSION['pesan_error'])) { ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= aman_jenis_pelanggaran($_SESSION['pesan_error']); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_error']); ?>

            <?php } ?>

            <!-- Card Form -->
            <div class="card-custom">

                <div class="card-header-custom">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Form Edit Jenis Pelanggaran
                    </h5>
                </div>

                <div class="card-body-custom">

                    <form action="proses_edit_jenis_pelanggaran.php"
                          method="POST"
                          id="formEditJenis">

                        <!-- ID -->
                        <input type="hidden"
                               name="id"
                               value="<?= (int) $data['id']; ?>">

                        <!-- Kategori -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Kategori Pelanggaran
                            </label>

                            <select name="pelanggaran_kategori_id"
                                    class="form-select"
                                    required>

                                <?php while ($kat = mysqli_fetch_assoc($kategori)) { ?>

                                    <option
                                        value="<?= (int) $kat['id']; ?>"
                                        <?= (int) $kat['id'] === (int) $data['pelanggaran_kategori_id']
                                            ? 'selected'
                                            : ''; ?>>

                                        <?= aman_jenis_pelanggaran($kat['nama']); ?>

                                        <?= (int) $kat['status_aktif'] === 0
                                            ? ' (Tidak Aktif)'
                                            : ''; ?>

                                    </option>

                                <?php } ?>

                            </select>
                        </div>

                        <!-- Kode -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Kode Pelanggaran
                            </label>

                            <input type="text"
                                   name="kode"
                                   class="form-control"
                                   maxlength="30"
                                   value="<?= aman_jenis_pelanggaran($data['kode'] ?? ''); ?>"
                                   placeholder="Masukkan kode pelanggaran"
                                   required>
                        </div>

                        <!-- Nama -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Pelanggaran
                            </label>

                            <input type="text"
                                   name="nama"
                                   class="form-control"
                                   maxlength="150"
                                   value="<?= aman_jenis_pelanggaran($data['nama'] ?? ''); ?>"
                                   placeholder="Masukkan nama pelanggaran"
                                   required>
                        </div>

                        <!-- Poin -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Poin
                            </label>

                            <input type="number"
                                   name="poin"
                                   class="form-control"
                                   min="0"
                                   value="<?= (int) $data['poin']; ?>"
                                   placeholder="Masukkan poin pelanggaran"
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
                                      placeholder="Masukkan deskripsi pelanggaran"><?= aman_jenis_pelanggaran($data['deskripsi'] ?? ''); ?></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Status Aktif
                            </label>

                            <select name="status_aktif"
                                    class="form-select"
                                    required>

                                <option value="1"
                                    <?= (int) $data['status_aktif'] === 1
                                        ? 'selected'
                                        : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= (int) $data['status_aktif'] === 0
                                        ? 'selected'
                                        : ''; ?>>
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

                            <a href="kelola_jenis_pelanggaran.php"
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

<!-- Validasi Form -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formEditJenis');

    form.addEventListener('submit', function (event) {

        const kode = document.querySelector('[name="kode"]').value;
        const nama = document.querySelector('[name="nama"]').value;
        const poin = document.querySelector('[name="poin"]').value;

        if (kode.trim() === '') {
            event.preventDefault();
            alert('Kode pelanggaran wajib diisi!');
            return;
        }

        if (nama.trim() === '') {
            event.preventDefault();
            alert('Nama pelanggaran wajib diisi!');
            return;
        }

        if (poin === '' || Number(poin) < 0) {
            event.preventDefault();
            alert('Poin pelanggaran tidak valid!');
            return;
        }

        if (!confirm('Apakah kamu yakin ingin menyimpan perubahan jenis pelanggaran?')) {
            event.preventDefault();
        }

    });

});
</script>

</body>
</html>
