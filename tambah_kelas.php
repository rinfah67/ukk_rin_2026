<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// Ambil data jurusan dari database secara dinamis
$query_jurusan = false;
try {
    $query_jurusan = mysqli_query($koneksi, "SELECT * FROM t_jurusan ORDER BY nama_jurusan ASC");
} catch (mysqli_sql_exception $e) {
    try {
        $query_jurusan = mysqli_query($koneksi, "SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
    } catch (mysqli_sql_exception $e2) {$query_jurusan = false;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kelas - Si Pelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 18px; border-radius: 8px; margin-bottom: 4px; font-weight: 500; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #ffffff; background-color: #2563eb; }
        .card-custom { background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); }
        .form-label { font-weight: 600; color: #475569; font-size: 0.875rem; margin-bottom: 6px; }
        .form-control, .form-select { border-radius: 8px; padding: 10px 14px; border: 1px solid #cbd5e1; font-size: 0.95rem; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-plus text-primary"></i> Tambah Data Kelas
                            </h2>
                            <p class="text-muted mb-0">Isi formulir di bawah untuk menambahkan kelas baru.</p>
                        </div>
                        <a href="kelola_kelas.php" class="btn btn-white text-secondary border fw-medium px-3 shadow-sm" style="border-radius: 10px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    <div class="card card-custom p-4 p-md-5">
                        <form action="proses_tambah_kelas.php" method="POST">
                            <div class="row g-4">
                                <!-- 1. Nama Kelas -->
                                <div class="col-md-6">
                                    <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" placeholder="Contoh: X RPL 1" required>
                                </div>

                                <!-- 2. Tingkat -->
                                <div class="col-md-6">
                                    <label for="tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tingkat" name="tingkat" required>
                                        <option value="" selected disabled>-- Pilih Tingkat --</option>
                                        <option value="X">X (Sepuluh)</option>
                                        <option value="XI">XI (Sebelas)</option>
                                        <option value="XII">XII (Dua Belas)</option>
                                    </select>
                                </div>

                                <!-- 3. Jurusan (Mengambil Sesuai Data di Database) -->
                                <div class="col-md-6">
                                    <label for="jurusan" class="form-label">Jurusan / Kompetensi Keahlian <span class="text-danger">*</span></label>
                                    <?php if ($query_jurusan && mysqli_num_rows($query_jurusan) > 0) : ?>
                                        <select class="form-select" id="jurusan" name="jurusan" required>
                                            <option value="" selected disabled>-- Pilih Jurusan --</option>
                                            <?php while ($j = mysqli_fetch_assoc($query_jurusan)) : ?>
                                                <?php $val_jurusan =$j['nama_jurusan'] ?? $j['jurusan'] ?? $j['nama']; ?>
                                                <option value="<?= htmlspecialchars($val_jurusan); ?>">
                                                    <?= htmlspecialchars($val_jurusan); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    <?php else : ?>
                                        <input type="text" class="form-control" id="jurusan" name="jurusan" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                                    <?php endif; ?>
                                </div>

                                <!-- 4. Status Kelas -->
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status Kelas <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif" selected>Aktif</option>
                                        <option value="Tidak Aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">
                                <a href="kelola_kelas.php" class="btn btn-outline-secondary px-4 fw-medium" style="border-radius: 8px;">Batal</a>
                                <button type="submit" name="simpan" class="btn btn-primary px-4 fw-medium shadow-sm" style="border-radius: 8px; background-color: #2563eb;">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data Kelas
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>