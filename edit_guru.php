
<?php
session_start();

include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin yang boleh mengakses
if (($_SESSION['role'] ?? '') != 'admin') {
    echo "Akses ditolak!";
    exit;
}

// Memeriksa ID guru
if (!isset($_GET['id'])) {
    echo "ID guru tidak ditemukan!";
    exit;
}

$id = (int) $_GET['id'];

// Mengambil data guru
$stmt = mysqli_prepare($koneksi, "
    SELECT * FROM t_guru
    WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($hasil);

if (!$guru) {
    echo "Data guru tidak ditemukan!";
    exit;
}

// Fungsi untuk mengamankan output
function aman_edit_guru($teks) {
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Guru - SI PELANG</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten Utama -->
        <main class="col-md-9 col-lg-10 p-4">

            <!-- Judul Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h3 class="fw-bold mb-1">Edit Guru</h3>
                    <p class="text-muted mb-0">
                        Ubah informasi data guru.
                    </p>
                </div>

                <a href="kelola_guru.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- Card Form -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-person-gear me-2"></i>
                        Form Edit Guru
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form action="proses_edit_guru.php" method="POST" id="formEditGuru">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= aman_edit_guru($guru['id']); ?>"
                        >

                        <!-- NIP -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                NIP
                            </label>

                            <input
                                type="text"
                                name="nip"
                                class="form-control"
                                value="<?= aman_edit_guru($guru['nip']); ?>"
                                required
                            >
                        </div>

                        <!-- Nama Guru -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama Guru
                            </label>

                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                value="<?= aman_edit_guru($guru['nama']); ?>"
                                required
                            >
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?= aman_edit_guru($guru['email']); ?>"
                                required
                            >
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Status Guru
                            </label>

                            <select name="status_aktif" class="form-select" required>

                                <option
                                    value="1"
                                    <?= ($guru['status_aktif'] == 1) ? 'selected' : ''; ?>
                                >
                                    Aktif
                                </option>

                                <option
                                    value="0"
                                    <?= ($guru['status_aktif'] == 0) ? 'selected' : ''; ?>
                                >
                                    Tidak Aktif
                                </option>

                            </select>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex gap-2">

                            <button type="submit" name="edit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Simpan Perubahan
                            </button>

                            <a href="kelola_guru.php" class="btn btn-secondary">
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

<script>
document.getElementById('formEditGuru').addEventListener('submit', function(event) {

    if (!this.checkValidity()) {
        event.preventDefault();
        this.reportValidity();
        return;
    }

    if (!confirm('Apakah kamu yakin ingin menyimpan perubahan data guru?')) {
        event.preventDefault();
    }

});
</script>

</body>
</html>
