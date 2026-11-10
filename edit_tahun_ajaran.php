<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$query = mysqli_query($koneksi, "SELECT * FROM t_tahun_ajaran WHERE id = '$id'");

if (!$query || mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data Tahun Ajaran tidak ditemukan!'); window.location='kelola_tahun_ajaran.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);
$id_ta_val           = $data['id'];
$tahun_val           = $data['tahun_ajaran'] ?? $data['tahun'] ?? '';
$status_val          = $data['status'] ?? 'Aktif';
$tanggal_mulai_val   = $data['tanggal_mulai'] ?? $data['tgl_mulai'] ?? '';
$tanggal_selesai_val = $data['tanggal_selesai'] ?? $data['tgl_selesai'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tahun Ajaran - Si Pelang</title>
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
                                <i class="fa-solid fa-pen-to-square text-warning"></i> Edit Data Tahun Ajaran
                            </h2>
                            <p class="text-muted mb-0">Ubah formulir di bawah ini untuk memperbarui periode tahun ajaran.</p>
                        </div>
                        <a href="kelola_tahun_ajaran.php" class="btn btn-white text-secondary border fw-medium px-3 shadow-sm" style="border-radius: 10px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    <div class="card card-custom p-4 p-md-5">
                        <form action="proses_edit_tahun_ajaran.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $id_ta_val; ?>">

                            <div class="row g-4">
                                <!-- Tahun Ajaran -->
                                <div class="col-md-6">
                                    <label for="tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo htmlspecialchars($tahun_val); ?>" required>
                                </div>

                                <!-- Status -->
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="Aktif" <?php echo ($status_val == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="Tidak Aktif" <?php echo ($status_val == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    </select>
                                </div>

                                <!-- Tanggal Mulai -->
                                <div class="col-md-6">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggal_mulai_val); ?>" required>
                                </div>

                                <!-- Tanggal Selesai -->
                                <div class="col-md-6">
                                    <label for="tanggal_selesai" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo htmlspecialchars($tanggal_selesai_val); ?>" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">
                                <a href="kelola_tahun_ajaran.php" class="btn btn-outline-secondary px-4 fw-medium" style="border-radius: 8px;">Batal</a>
                                <button type="submit" name="update" class="btn btn-warning px-4 fw-medium shadow-sm text-dark" style="border-radius: 8px;">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Update Data
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