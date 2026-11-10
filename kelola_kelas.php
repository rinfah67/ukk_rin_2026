<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

$query_kelas = mysqli_query($koneksi, "SELECT * FROM t_kelas ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kelas - Si Pelang</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); }
        .sidebar .nav-link { color: #94a3b8; padding: 12px 18px; border-radius: 8px; margin-bottom: 4px; font-weight: 500; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #ffffff; background-color: #2563eb; }
        .card-custom { background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); }
        .table-custom-header th { background-color: #eff6ff !important; color: #1e40af !important; font-weight: 600; text-transform: uppercase; font-size: 0.78rem; border-bottom: 2px solid #dbeafe !important; padding: 14px 12px; }
        .btn-action-edit { background-color: #fef3c7; color: #d97706; border: 1px solid #fde68a; border-radius: 6px; padding: 6px 12px; font-weight: 500; }
        .btn-action-edit:hover { background-color: #d97706; color: #ffffff; }
        .btn-action-delete { background-color: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 6px; padding: 6px 12px; font-weight: 500; }
        .btn-action-delete:hover { background-color: #dc2626; color: #ffffff; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-school text-primary"></i> Kelola Data Kelas
                    </h2>
                    <p class="text-muted mb-0">Kelola daftar kelas yang aktif di sekolah.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="dashboard.php" class="btn btn-white text-secondary border fw-medium px-3 shadow-sm" style="border-radius: 10px;">
                        <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                    </a>
                    <a href="tambah_kelas.php" class="btn btn-primary fw-medium px-4 shadow-sm" style="border-radius: 10px; background-color: #2563eb;">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Kelas
                    </a>
                </div>
            </div>

            <div class="card card-custom p-4">
                <div class="table-responsive">
                    <table id="tabelKelas" class="table table-hover align-middle w-100">
                        <thead class="table-custom-header">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Jurusan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($query_kelas && mysqli_num_rows($query_kelas) > 0) :
                                while ($data = mysqli_fetch_assoc($query_kelas)) : 
                                    $id_val = $data['id'] ?? $data['id_kelas'] ?? 0;
                                    
                                    // Pengecekan otomatis berbagai variasi nama kolom nama kelas
                                    $nama_kelas = $data['nama_kelas'] ?? $data['nama'] ?? $data['kelas'] ?? '-';
                                    $tingkat    = $data['tingkat'] ?? '-';
                                    $jurusan    = $data['jurusan'] ?? $data['nama_jurusan'] ?? '-';
                                    $stt        = $data['status'] ?? 'Aktif';
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-medium"><?= $no++; ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($nama_kelas); ?></td>
                                <td><span class="badge bg-light text-primary border border-primary-subtle px-2 py-1"><?= htmlspecialchars($tingkat); ?></span></td>
                                <td class="text-muted"><?= htmlspecialchars($jurusan); ?></td>
                                <td class="text-center">
                                    <?php if ($stt == 'Aktif') : ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">Aktif</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit_kelas.php?id=<?= $id_val; ?>" class="btn-action-edit text-decoration-none btn-sm" title="Edit Data">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="hapus_kelas.php?id=<?= $id_val; ?>" class="btn-action-delete text-decoration-none btn-sm" title="Hapus Data" onclick="return confirm('Yakin ingin menghapus kelas ini?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            endif; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelKelas').DataTable();
    });
</script>
</body>
</html>