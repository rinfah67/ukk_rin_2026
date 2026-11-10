<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// Ambil ID siswa dari parameter URL
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Query mengambil data siswa berdasarkan kolom primary key 'id'
$query = mysqli_query($koneksi, "SELECT * FROM t_siswa WHERE id = '$id'");

if (!$query || mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location='kelola_siswa.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($query);
$id_siswa_val = $data['id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa - Pelanggaran Siswa</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Styling Sidebar */
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
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .sidebar .nav-link.text-danger:hover {
            background-color: #ef4444;
            color: #fff !important;
        }

        /* Badge R dalam Lingkaran Garis */
        .circle-r {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border: 2px solid currentColor;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
        }

        /* Card Form Container */
        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }

        .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.875rem;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">
                    
                    <!-- Header Judul -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-pen-to-square text-warning"></i> Edit Data Siswa
                            </h2>
                            <p class="text-muted mb-0">Ubah formulir di bawah ini untuk memperbarui data siswa.</p>
                        </div>
                        <a href="kelola_siswa.php" class="btn btn-white text-secondary border fw-medium px-3 shadow-sm" style="border-radius: 10px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>

                    <!-- Card Form Input Edit Lengkap -->
                    <div class="card card-custom p-4 p-md-5">
                        <form action="proses_edit_siswa.php" method="POST">
                            <!-- Hidden ID Siswa -->
                            <input type="hidden" name="id" value="<?php echo $id_siswa_val; ?>">

                            <div class="row g-4">
                                
                                <!-- 1. NIS (Maximal 15 Karakter & Hanya Angka) -->
                                <div class="col-md-6">
                                    <label for="nis" class="form-label">NIS <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nis" name="nis" maxlength="15" value="<?php echo htmlspecialchars(isset($data['nis']) ? $data['nis'] : ''); ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                                    <div class="form-text">Maksimal 15 digit angka.</div>
                                </div>

                                <!-- 2. NISN (Maximal 10 Karakter & Hanya Angka) -->
                                <div class="col-md-6">
                                    <label for="nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nisn" name="nisn" maxlength="10" value="<?php echo htmlspecialchars(isset($data['nisn']) ? $data['nisn'] : ''); ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                                    <div class="form-text">Maksimal 10 digit angka.</div>
                                </div>

                                <!-- 3. NAMA LENGKAP -->
                                <div class="col-12">
                                    <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama" name="nama" maxlength="100" value="<?php echo htmlspecialchars(isset($data['nama']) ? $data['nama'] : ''); ?>" required>
                                </div>

                                <!-- 4. JENIS KELAMIN -->
                                <div class="col-md-6">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                        <?php $jk = isset($data['jenis_kelamin']) ? $data['jenis_kelamin'] : ''; ?>
                                        <option value="Laki-laki" <?php echo ($jk == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="Perempuan" <?php echo ($jk == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                </div>

                                <!-- 5. TANGGAL LAHIR -->
                                <div class="col-md-6">
                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars(isset($data['tanggal_lahir']) ? $data['tanggal_lahir'] : ''); ?>" required>
                                </div>

                                <!-- 6. ALAMAT LENGKAP -->
                                <div class="col-12">
                                    <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars(isset($data['alamat']) ? $data['alamat'] : ''); ?></textarea>
                                </div>

                            </div>

                            <!-- Tombol Update & Batal -->
                            <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-3 border-top">
                                <a href="kelola_siswa.php" class="btn btn-outline-secondary px-4 fw-medium" style="border-radius: 8px;">Batal</a>
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