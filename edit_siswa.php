
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Memeriksa ID siswa
if (!isset($_GET['id'])) {
    header("Location: kelola_siswa.php");
    exit;
}

$id = (int) $_GET['id'];

// Mengambil data siswa
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_siswa WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

$siswa = mysqli_fetch_assoc($result);

if (!$siswa) {
    die("Data siswa tidak ditemukan!");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Siswa - SI PELANG</title>

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
                    <h3 class="fw-bold mb-1">Edit Siswa</h3>
                    <p class="text-secondary mb-0">
                        Ubah data siswa sekolah.
                    </p>
                </div>

                <a href="kelola_siswa.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Card Form -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i>
                        Form Edit Siswa
                    </h5>
                </div>

                <div class="card-body">

                    <form action="proses_edit_siswa.php"
                          method="POST"
                          id="formEditSiswa">

                        <input type="hidden" name="id"
                               value="<?= (int)$siswa['id']; ?>">

                        <!-- NIS -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIS</label>

                            <input type="text"
                                   name="nis"
                                   class="form-control"
                                   maxlength="10"
                                   inputmode="numeric"
                                   value="<?= htmlspecialchars($siswa['nis'] ?? ''); ?>"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                   required>
                        </div>

                        <!-- NISN -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NISN</label>

                            <input type="text"
                                   id="nisn"
                                   name="nisn"
                                   class="form-control"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   inputmode="numeric"
                                   title="NISN harus terdiri dari 10 digit angka"
                                   value="<?= htmlspecialchars($siswa['nisn'] ?? ''); ?>"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                   required>

                            <div class="form-text">
                                NISN harus terdiri dari 10 digit angka.
                            </div>
                        </div>

                        <!-- Nama -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Siswa</label>

                            <input type="text"
                                   name="nama"
                                   class="form-control"
                                   maxlength="150"
                                   value="<?= htmlspecialchars($siswa['nama'] ?? ''); ?>"
                                   required>
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jenis Kelamin</label>

                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>

                                <option value="L"
                                    <?= $siswa['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>
                                    Laki-laki
                                </option>

                                <option value="P"
                                    <?= $siswa['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>
                                    Perempuan
                                </option>
                            </select>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Lahir</label>

                            <input type="date"
                                   name="tanggal_lahir"
                                   class="form-control"
                                   value="<?= htmlspecialchars($siswa['tanggal_lahir']); ?>"
                                   required>
                        </div>

                        <!-- Alamat -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat</label>

                            <textarea name="alamat"
                                      class="form-control"
                                      rows="4"
                                      required><?= htmlspecialchars($siswa['alamat'] ?? ''); ?></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status</label>

                            <select name="status_aktif" class="form-select" required>
                                <option value="1"
                                    <?= $siswa['status_aktif'] == 1 ? 'selected' : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= $siswa['status_aktif'] == 0 ? 'selected' : ''; ?>>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <!-- Informasi waktu -->
                        <div class="alert alert-light border">
                            <div class="mb-2">
                                <i class="bi bi-clock-history me-2"></i>
                                <strong>Created At:</strong>
                                <?= htmlspecialchars($siswa['created_at'] ?? '-'); ?>
                            </div>

                            <div>
                                <i class="bi bi-arrow-repeat me-2"></i>
                                <strong>Updated At:</strong>
                                <?= htmlspecialchars($siswa['updated_at'] ?? '-'); ?>
                            </div>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex gap-2">
                            <button type="submit" name="edit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan Perubahan
                            </button>

                            <a href="kelola_siswa.php" class="btn btn-secondary">
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

    const form = document.getElementById('formEditSiswa');

    form.addEventListener('submit', function (event) {

        const nis = document.querySelector('[name="nis"]').value;
        const nisn = document.getElementById('nisn').value;
        const nama = document.querySelector('[name="nama"]').value;

        // Memeriksa NIS
        if (!/^[0-9]{1,10}$/.test(nis)) {
            event.preventDefault();
            alert('NIS harus berupa angka dengan maksimal 10 digit!');
            return;
        }

        // Memeriksa NISN
        if (!/^[0-9]{10}$/.test(nisn)) {
            event.preventDefault();
            alert('NISN harus terdiri dari 10 digit angka!');
            return;
        }

        // Memeriksa nama
        if (nama.trim() === '') {
            event.preventDefault();
            alert('Nama siswa wajib diisi!');
            return;
        }

        // Konfirmasi penyimpanan
        if (!confirm('Apakah kamu yakin ingin menyimpan perubahan data siswa?')) {
            event.preventDefault();
        }

    });

});
</script>

</body>
</html>
