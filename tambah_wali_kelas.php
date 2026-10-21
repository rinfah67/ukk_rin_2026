
<?php
session_start();

include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin yang boleh menambah wali kelas
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Mengambil data guru aktif
$query_guru = mysqli_query(
    $koneksi,
    "SELECT id, nip, nama
     FROM t_guru
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

// Mengambil kelas aktif
$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

// Mengambil tahun ajaran aktif
$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY tanggal_mulai DESC"
);

// Pesan error
$pesan_error = $_SESSION['pesan_error'] ?? '';
unset($_SESSION['pesan_error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Wali Kelas | SI PELANG</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fb;
        }

        .form-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        .page-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background-color: #cfe2ff;
            color: #0d6efd;
            font-size: 24px;
        }
    </style>
</head>

<body>

<?php include 'layout/sidebar.php'; ?>

<main class="col-12 col-lg-10 offset-lg-2 p-3 p-md-4">

    <!-- Judul halaman -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="page-icon">
            <i class="bi bi-person-plus"></i>
        </div>

        <div>
            <h3 class="fw-bold mb-1">Tambah Wali Kelas</h3>
            <p class="text-secondary mb-0">
                Tambahkan penugasan guru sebagai wali kelas.
            </p>
        </div>
    </div>

    <!-- Pesan error -->
    <?php if ($pesan_error !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($pesan_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card form-card">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0">Form Penugasan Wali Kelas</h5>
        </div>

        <div class="card-body p-4">

            <form action="proses_tambah_wali_kelas.php" method="POST">

                <!-- Pilih guru -->
                <div class="mb-3">
                    <label for="guru_id" class="form-label fw-semibold">
                        Guru <span class="text-danger">*</span>
                    </label>

                    <select name="guru_id" id="guru_id"
                            class="form-select" required>
                        <option value="">-- Pilih Guru --</option>

                        <?php if ($query_guru): ?>
                            <?php while ($guru = mysqli_fetch_assoc($query_guru)): ?>
                                <option value="<?= (int) $guru['id']; ?>">
                                    <?= htmlspecialchars($guru['nip'] . ' - ' . $guru['nama']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Pilih kelas -->
                <div class="mb-3">
                    <label for="kelas_id" class="form-label fw-semibold">
                        Kelas <span class="text-danger">*</span>
                    </label>

                    <select name="kelas_id" id="kelas_id"
                            class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>

                        <?php if ($query_kelas): ?>
                            <?php while ($kelas = mysqli_fetch_assoc($query_kelas)): ?>
                                <option value="<?= (int) $kelas['id']; ?>">
                                    <?= htmlspecialchars(
                                        $kelas['nama'] . ' - ' .
                                        $kelas['tingkat'] . ' ' .
                                        $kelas['jurusan']
                                    ); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Pilih tahun ajaran -->
                <div class="mb-3">
                    <label for="tahun_ajaran_id" class="form-label fw-semibold">
                        Tahun Ajaran <span class="text-danger">*</span>
                    </label>

                    <select name="tahun_ajaran_id" id="tahun_ajaran_id"
                            class="form-select" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>

                        <?php if ($query_tahun): ?>
                            <?php while ($tahun = mysqli_fetch_assoc($query_tahun)): ?>
                                <option value="<?= (int) $tahun['id']; ?>">
                                    <?= htmlspecialchars($tahun['nama']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="row">
                    <!-- Tanggal mulai -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label fw-semibold">
                            Tanggal Mulai <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="tanggal_mulai"
                               id="tanggal_mulai"
                               class="form-control"
                               required>
                    </div>

                    <!-- Tanggal selesai -->
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_selesai" class="form-label fw-semibold">
                            Tanggal Selesai
                        </label>

                        <input type="date"
                               name="tanggal_selesai"
                               id="tanggal_selesai"
                               class="form-control">
                        <div class="form-text">
                            Boleh dikosongkan jika belum ditentukan.
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label for="status_aktif" class="form-label fw-semibold">
                        Status Penugasan
                    </label>

                    <select name="status_aktif" id="status_aktif"
                            class="form-select" required>
                        <option value="1" selected>Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>

                <!-- Tombol -->
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Simpan Penugasan
                    </button>

                    <a href="kelola_wali_kelas.php"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali
                    </a>
                </div>

            </form>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
