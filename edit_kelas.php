
<?php

session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Memastikan hanya admin yang dapat mengakses halaman
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak!");
}

// Memeriksa apakah ID tersedia
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID kelas tidak ditemukan!");
}

// Mengambil ID kelas
$id = (int) $_GET['id'];

// Mengambil data kelas berdasarkan ID
$query = mysqli_query($koneksi, "
    SELECT *
    FROM t_kelas
    WHERE id = $id
");

// Memeriksa apakah data ditemukan
if (!$query || mysqli_num_rows($query) == 0) {
    die("Data kelas tidak ditemukan!");
}

// Menyimpan data kelas
$data = mysqli_fetch_assoc($query);

// Mengambil pesan error dari session
$pesan_error = $_SESSION['pesan_error'] ?? '';
unset($_SESSION['pesan_error']);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Kelas | SI PELANG</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- Memanggil sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten utama -->
        <main class="col-md-9 col-lg-10 p-4">

            <!-- Judul halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h3 class="fw-bold mb-1">Edit Data Kelas</h3>

                    <p class="text-secondary mb-0">
                        Perbarui informasi data kelas SI PELANG.
                    </p>
                </div>

                <!-- Tombol kembali -->
                <a href="kelola_kelas.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- Menampilkan pesan error -->
            <?php if ($pesan_error != ''): ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i>

                    <?= htmlspecialchars($pesan_error); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>

            <?php endif; ?>

            <!-- Card form edit kelas -->
            <div class="card border-0 shadow-sm rounded-3">

                <!-- Header card -->
                <div class="card-header bg-white py-3">

                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-pencil-square text-primary me-2"></i>
                        Form Edit Kelas
                    </h5>

                </div>

                <!-- Isi form -->
                <div class="card-body p-4">

                    <form action="proses_edit_kelas.php"
                          method="POST"
                          onsubmit="return confirm('Yakin ingin menyimpan perubahan data kelas?');">

                        <!-- ID kelas -->
                        <input type="hidden"
                               name="id"
                               value="<?= (int) $data['id']; ?>">

                        <!-- Nama kelas -->
                        <div class="mb-3">

                            <label for="nama" class="form-label fw-semibold">
                                Nama Kelas
                            </label>

                            <input type="text"
                                   class="form-control"
                                   id="nama"
                                   name="nama"
                                   value="<?= htmlspecialchars($data['nama']); ?>"
                                   placeholder="Contoh: XII RPL 1"
                                   required>

                        </div>

                        <!-- Tingkat kelas -->
                        <div class="mb-3">

                            <label for="tingkat" class="form-label fw-semibold">
                                Tingkat
                            </label>

                            <select class="form-select"
                                    id="tingkat"
                                    name="tingkat"
                                    required>

                                <option value="">-- Pilih Tingkat --</option>

                                <option value="X"
                                    <?= $data['tingkat'] == 'X' ? 'selected' : ''; ?>>
                                    X
                                </option>

                                <option value="XI"
                                    <?= $data['tingkat'] == 'XI' ? 'selected' : ''; ?>>
                                    XI
                                </option>

                                <option value="XII"
                                    <?= $data['tingkat'] == 'XII' ? 'selected' : ''; ?>>
                                    XII
                                </option>

                            </select>

                        </div>

                        <!-- Jurusan -->
                        <div class="mb-3">

                            <label for="jurusan" class="form-label fw-semibold">
                                Jurusan
                            </label>

                            <input type="text"
                                   class="form-control"
                                   id="jurusan"
                                   name="jurusan"
                                   value="<?= htmlspecialchars($data['jurusan']); ?>"
                                   placeholder="Contoh: RPL"
                                   required>

                        </div>

                        <!-- Status kelas -->
                        <div class="mb-4">

                            <label for="status_aktif" class="form-label fw-semibold">
                                Status Kelas
                            </label>

                            <select class="form-select"
                                    id="status_aktif"
                                    name="status_aktif"
                                    required>

                                <option value="1"
                                    <?= (int) $data['status_aktif'] == 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= (int) $data['status_aktif'] == 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>

                            </select>

                        </div>

                        <!-- Tombol simpan dan batal -->
                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan Perubahan
                            </button>

                            <a href="kelola_kelas.php" class="btn btn-secondary">
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

</body>
</html>
