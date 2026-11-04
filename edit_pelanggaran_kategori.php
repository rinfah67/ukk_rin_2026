
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
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Kategori Pelanggaran - SI PELANG</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <!-- Judul halaman -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                <div>
                    <h3 class="fw-bold mb-1">Edit Kategori Pelanggaran</h3>
                    <p class="text-secondary mb-0">
                        Perbarui informasi kategori pelanggaran siswa.
                    </p>
                </div>

                <a href="kelola_pelanggaran_kategori.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- Card Form -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i>
                        Form Edit Kategori
                    </h5>
                </div>

                <div class="card-body">

                    <form action="proses_edit_pelanggaran_kategori.php"
                          method="POST"
                          id="formEditKategori">

                        <!-- ID Kategori -->
                        <input type="hidden"
                               name="id"
                               value="<?= (int)$data['id']; ?>">

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
                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan Perubahan
                            </button>

                            <a href="kelola_pelanggaran_kategori.php"
                               class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

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
