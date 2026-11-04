
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Memastikan hanya guru yang dapat mengakses
if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

// Mengambil ID user yang sedang login
$user_id = (int)($_SESSION['id_user'] ?? 0);

// Mengambil ID guru berdasarkan user yang login
$stmt = mysqli_prepare($koneksi, "
    SELECT id FROM t_guru
    WHERE user_id = ?
");

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$guru) {
    die("Data guru tidak ditemukan.");
}

$guru_id = (int)$guru['id'];

// Mengambil ID catatan
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("ID catatan tidak valid.");
}

// Mengambil data catatan milik guru yang login
$stmt = mysqli_prepare($koneksi, "
    SELECT *
    FROM t_pelanggaran_siswa
    WHERE id = ? AND guru_id = ?
");

mysqli_stmt_bind_param($stmt, "ii", $id, $guru_id);
mysqli_stmt_execute($stmt);

$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    die("Catatan tidak ditemukan atau bukan milik akun kamu.");
}

// Mengamankan output
function aman_edit_pelanggaran($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Catatan Pelanggaran - SI PELANG</title>

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
                    <h3 class="fw-bold mb-1">Edit Catatan Pelanggaran</h3>
                    <p class="text-secondary mb-0">
                        Perbarui informasi catatan pelanggaran siswa.
                    </p>
                </div>

                <a href="catatan_pelanggaran.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- Card Form -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i>
                        Form Edit Catatan Pelanggaran
                    </h5>
                </div>

                <div class="card-body">

                    <form action="proses_edit_pelanggaran.php"
                          method="POST"
                          id="formEditPelanggaran">

                        <input type="hidden"
                               name="id"
                               value="<?= (int)$data['id']; ?>">

                        <!-- Nama Siswa -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Siswa</label>

                            <input type="text"
                                   class="form-control"
                                   value="<?= aman_edit_pelanggaran($data['nama_siswa']); ?>"
                                   readonly>
                        </div>

                        <!-- Kelas -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kelas</label>

                            <input type="text"
                                   class="form-control"
                                   value="<?= aman_edit_pelanggaran($data['nama_kelas']); ?>"
                                   readonly>
                        </div>

                        <!-- Jenis Pelanggaran -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jenis Pelanggaran</label>

                            <input type="text"
                                   class="form-control"
                                   value="<?= aman_edit_pelanggaran($data['nama_pelanggaran']); ?>"
                                   readonly>
                        </div>

                        <!-- Poin -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Poin Pelanggaran</label>

                            <input type="number"
                                   class="form-control"
                                   value="<?= (int)$data['poin']; ?>"
                                   readonly>
                        </div>

                        <!-- Tanggal -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Pelanggaran</label>

                            <input type="date"
                                   name="tanggal"
                                   class="form-control"
                                   value="<?= aman_edit_pelanggaran($data['tanggal']); ?>"
                                   required>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keterangan</label>

                            <textarea name="keterangan"
                                      class="form-control"
                                      rows="3"
                                      required><?= aman_edit_pelanggaran($data['keterangan']); ?></textarea>
                        </div>

                        <!-- Tindakan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tindakan</label>

                            <textarea name="tindakan"
                                      class="form-control"
                                      rows="3"
                                      required><?= aman_edit_pelanggaran($data['tindakan']); ?></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status</label>

                            <select name="status" class="form-select" required>

                                <option value="Belum diproses"
                                    <?= $data['status'] == 'Belum diproses' ? 'selected' : ''; ?>>
                                    Belum diproses
                                </option>

                                <option value="Diproses"
                                    <?= $data['status'] == 'Diproses' ? 'selected' : ''; ?>>
                                    Diproses
                                </option>

                                <option value="Selesai"
                                    <?= $data['status'] == 'Selesai' ? 'selected' : ''; ?>>
                                    Selesai
                                </option>

                            </select>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan Perubahan
                            </button>

                            <a href="catatan_pelanggaran.php" class="btn btn-secondary">
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

    const form = document.getElementById('formEditPelanggaran');

    form.addEventListener('submit', function (event) {

        const tanggal = document.querySelector('[name="tanggal"]').value;
        const keterangan = document.querySelector('[name="keterangan"]').value;
        const tindakan = document.querySelector('[name="tindakan"]').value;

        // Memeriksa tanggal
        if (tanggal === '') {
            event.preventDefault();
            alert('Tanggal pelanggaran wajib diisi!');
            return;
        }

        // Memeriksa keterangan
        if (keterangan.trim() === '') {
            event.preventDefault();
            alert('Keterangan wajib diisi!');
            return;
        }

        // Memeriksa tindakan
        if (tindakan.trim() === '') {
            event.preventDefault();
            alert('Tindakan wajib diisi!');
            return;
        }

        // Konfirmasi penyimpanan
        if (!confirm('Apakah kamu yakin ingin menyimpan perubahan catatan pelanggaran?')) {
            event.preventDefault();
        }

    });

});
</script>

</body>
</html>
