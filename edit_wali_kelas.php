
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

// Memeriksa ID
if (!isset($_GET['id'])) {
    header("Location: kelola_wali_kelas.php");
    exit;
}

$id = (int) $_GET['id'];

// Mengambil data wali kelas
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_wali_kelas WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data wali kelas tidak ditemukan.");
}

// Mengambil data guru
$guru = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_guru
    ORDER BY nama
");

// Mengambil data kelas
$kelas = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    ORDER BY nama
");

// Mengambil data tahun ajaran
$tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    ORDER BY nama DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Wali Kelas - SI PELANG</title>

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
                    <h3 class="fw-bold mb-1">Edit Wali Kelas</h3>
                    <p class="text-secondary mb-0">
                        Ubah data penugasan wali kelas sekolah.
                    </p>
                </div>

                <a href="kelola_walikelas.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Card Form -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i>
                        Form Edit Wali Kelas
                    </h5>
                </div>

                <div class="card-body">

                    <form action="proses_edit_wali_kelas.php"
                          method="POST"
                          id="formWaliKelas">

                        <input type="hidden" name="id"
                               value="<?= (int)$data['id']; ?>">

                        <!-- Guru -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Guru</label>

                            <select name="guru_id" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>

                                <?php while ($g = mysqli_fetch_assoc($guru)): ?>
                                    <option value="<?= (int)$g['id']; ?>"
                                        <?= $g['id'] == $data['guru_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($g['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Kelas -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kelas</label>

                            <select name="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>

                                <?php while ($k = mysqli_fetch_assoc($kelas)): ?>
                                    <option value="<?= (int)$k['id']; ?>"
                                        <?= $k['id'] == $data['kelas_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars(
                                            $k['nama'] . ' - ' .
                                            $k['tingkat'] . ' ' .
                                            $k['jurusan']
                                        ); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Tahun Ajaran -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tahun Ajaran</label>

                            <select name="tahun_ajaran_id" class="form-select" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>

                                <?php while ($ta = mysqli_fetch_assoc($tahun)): ?>
                                    <option value="<?= (int)$ta['id']; ?>"
                                        <?= $ta['id'] == $data['tahun_ajaran_id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($ta['nama']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Tanggal Mulai -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Mulai</label>

                            <input type="date"
                                   name="tanggal_mulai"
                                   id="tanggal_mulai"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['tanggal_mulai']); ?>"
                                   required>
                        </div>

                        <!-- Tanggal Selesai -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Selesai</label>

                            <input type="date"
                                   name="tanggal_selesai"
                                   id="tanggal_selesai"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['tanggal_selesai']); ?>"
                                   required>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status</label>

                            <select name="status_aktif" class="form-select" required>
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

                            <a href="kelola_wali_kelas.php" class="btn btn-secondary">
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

    const form = document.getElementById('formWaliKelas');

    form.addEventListener('submit', function (event) {

        const guru = document.querySelector('[name="guru_id"]').value;
        const kelas = document.querySelector('[name="kelas_id"]').value;
        const tahun = document.querySelector('[name="tahun_ajaran_id"]').value;

        const mulai = document.getElementById('tanggal_mulai').value;
        const selesai = document.getElementById('tanggal_selesai').value;

        // Memeriksa pilihan
        if (guru === '' || kelas === '' || tahun === '') {
            event.preventDefault();
            alert('Silakan lengkapi pilihan guru, kelas, dan tahun ajaran!');
            return;
        }

        // Memeriksa tanggal
        if (selesai < mulai) {
            event.preventDefault();
            alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai!');
            return;
        }

        // Konfirmasi penyimpanan
        if (!confirm('Apakah kamu yakin ingin menyimpan perubahan data wali kelas?')) {
            event.preventDefault();
        }

    });

});
</script>

</body>
</html>
