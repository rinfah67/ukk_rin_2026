
<?php
// Menampilkan error jika terjadi masalah
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// =====================================
// CEK LOGIN
// =====================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// =====================================
// CEK AKSES ADMIN
// =====================================
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

// =====================================
// CEK ID
// =====================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: kelola_wali_kelas.php");
    exit;
}

$id = (int) $_GET['id'];

// =====================================
// AMBIL DATA WALI KELAS
// =====================================
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_wali_kelas WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    die("Data wali kelas tidak ditemukan.");
}

// =====================================
// AMBIL DATA GURU
// Guru aktif dan guru yang sedang dipilih
// =====================================
$guru = mysqli_query($koneksi, "
    SELECT g.id, g.nama
    FROM t_guru g
    WHERE g.status_aktif = 1
       OR g.id = " . (int)$data['guru_id'] . "
    ORDER BY g.nama ASC
");

// =====================================
// AMBIL DATA KELAS
// Kelas aktif dan kelas yang sedang dipilih
// =====================================
$kelas = mysqli_query($koneksi, "
    SELECT k.id, k.nama, k.tingkat, k.jurusan
    FROM t_kelas k
    WHERE (
        k.status_aktif = 1
        OR k.id = " . (int)$data['kelas_id'] . "
    )
    ORDER BY k.nama ASC
");

// =====================================
// AMBIL DATA TAHUN AJARAN
// Tahun aktif dan tahun yang sedang dipilih
// =====================================
$tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
       OR id = " . (int)$data['tahun_ajaran_id'] . "
    ORDER BY nama DESC
");

// =====================================
// CEK QUERY
// =====================================
if (!$guru || !$kelas || !$tahun) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Wali Kelas - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

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
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">

                    <!-- JUDUL HALAMAN -->
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>
                            <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-user-tie text-primary"></i>
                                Edit Wali Kelas
                            </h2>

                            <p class="text-muted mb-0">
                                Ubah data penugasan wali kelas.
                            </p>
                        </div>

                        <a href="kelola_wali_kelas.php"
                           class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                           style="border-radius: 10px;">

                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Kembali

                        </a>

                    </div>

                    <!-- CARD FORM -->
                    <div class="card card-custom p-4 p-md-5">

                        <form action="proses_edit_wali_kelas.php"
                              method="POST"
                              id="formWaliKelas">

                            <input type="hidden"
                                   name="id"
                                   value="<?= (int)$data['id']; ?>">

                            <div class="row g-4">

                                <!-- GURU -->
                                <div class="col-md-6">

                                    <label for="guru_id" class="form-label">
                                        Guru <span class="text-danger">*</span>
                                    </label>

                                    <select name="guru_id"
                                            id="guru_id"
                                            class="form-select"
                                            required>

                                        <option value="" disabled>
                                            -- Pilih Guru --
                                        </option>

                                        <?php while ($g = mysqli_fetch_assoc($guru)) { ?>

                                            <option value="<?= (int)$g['id']; ?>"
                                                <?= $g['id'] == $data['guru_id'] ? 'selected' : ''; ?>>

                                                <?= htmlspecialchars($g['nama']); ?>

                                            </option>

                                        <?php } ?>

                                    </select>

                                </div>

                                <!-- KELAS -->
                                <div class="col-md-6">

                                    <label for="kelas_id" class="form-label">
                                        Kelas <span class="text-danger">*</span>
                                    </label>

                                    <select name="kelas_id"
                                            id="kelas_id"
                                            class="form-select"
                                            required>

                                        <option value="" disabled>
                                            -- Pilih Kelas --
                                        </option>

                                        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>

                                            <option value="<?= (int)$k['id']; ?>"
                                                <?= $k['id'] == $data['kelas_id'] ? 'selected' : ''; ?>>

                                                <?= htmlspecialchars(
                                                    $k['nama'] . ' - ' .
                                                    $k['tingkat'] . ' ' .
                                                    $k['jurusan']
                                                ); ?>

                                            </option>

                                        <?php } ?>

                                    </select>

                                </div>

                                <!-- TAHUN AJARAN -->
                                <div class="col-12">

                                    <label for="tahun_ajaran_id" class="form-label">
                                        Tahun Ajaran <span class="text-danger">*</span>
                                    </label>

                                    <select name="tahun_ajaran_id"
                                            id="tahun_ajaran_id"
                                            class="form-select"
                                            required>

                                        <option value="" disabled>
                                            -- Pilih Tahun Ajaran --
                                        </option>

                                        <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>

                                            <option value="<?= (int)$ta['id']; ?>"
                                                <?= $ta['id'] == $data['tahun_ajaran_id'] ? 'selected' : ''; ?>>

                                                <?= htmlspecialchars($ta['nama']); ?>

                                            </option>

                                        <?php } ?>

                                    </select>

                                </div>

                                <!-- TANGGAL MULAI -->
                                <div class="col-md-6">

                                    <label for="tanggal_mulai" class="form-label">
                                        Tanggal Mulai <span class="text-danger">*</span>
                                    </label>

                                    <input type="date"
                                           name="tanggal_mulai"
                                           id="tanggal_mulai"
                                           class="form-control"
                                           value="<?= htmlspecialchars($data['tanggal_mulai']); ?>"
                                           required>

                                </div>

                                <!-- TANGGAL SELESAI -->
                                <div class="col-md-6">

                                    <label for="tanggal_selesai" class="form-label">
                                        Tanggal Selesai <span class="text-danger">*</span>
                                    </label>

                                    <input type="date"
                                           name="tanggal_selesai"
                                           id="tanggal_selesai"
                                           class="form-control"
                                           value="<?= htmlspecialchars($data['tanggal_selesai']); ?>"
                                           required>

                                </div>

                                <!-- STATUS -->
                                <div class="col-12">

                                    <label for="status_aktif" class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>

                                    <select name="status_aktif"
                                            id="status_aktif"
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

                            </div>

                            <!-- TOMBOL -->
                            <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">

                                <a href="kelola_wali_kelas.php"
                                   class="btn btn-outline-secondary px-4 fw-medium"
                                   style="border-radius: 8px;">

                                    Batal

                                </a>

                                <button type="submit"
                                        name="simpan"
                                        class="btn btn-primary px-4 fw-medium shadow-sm"
                                        style="border-radius: 8px; background-color: #2563eb;">

                                    <i class="fa-solid fa-floppy-disk me-1"></i>
                                    Simpan Perubahan

                                </button>

                            </div>

                        </form>

                    </div>

                </div>
            </div>

        </main>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- VALIDASI FORM -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formWaliKelas');

    form.addEventListener('submit', function (event) {

        const guru = document.getElementById('guru_id').value;
        const kelas = document.getElementById('kelas_id').value;
        const tahun = document.getElementById('tahun_ajaran_id').value;

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
