<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// Ambil data tahun ajaran dari database
$query_ta = mysqli_query($koneksi, "SELECT * FROM t_tahun_ajaran ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tahun Ajaran - Si Pelang</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS Bootstrap 5 -->
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
        <!-- Sidebar Navigation -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-calendar-days text-primary"></i> Kelola Tahun Ajaran
                    </h2>
                    <p class="text-muted mb-0">Kelola data periode tahun ajaran aktif.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="dashboard.php" class="btn btn-white text-secondary border fw-medium px-3 shadow-sm" style="border-radius: 10px;">
                        <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                    </a>
                    <a href="tambah_tahun_ajaran.php" class="btn btn-primary fw-medium px-4 shadow-sm" style="border-radius: 10px; background-color: #2563eb;">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Tahun Ajaran
                    </a>
                </div>
            </div>

            <!-- Card Tabel Data -->
            <div class="card card-custom p-4">
                <div class="table-responsive">
                    <table id="tabelTA" class="table table-hover align-middle w-100">
                        <thead class="table-custom-header">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Tahun Ajaran</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($query_ta && mysqli_num_rows($query_ta) > 0) :
                                while ($data = mysqli_fetch_assoc($query_ta)) : 
                                    // Ambil ID
                                    $id_val = 0;
                                    foreach (['id', 'id_tahun_ajaran', 'id_ta'] as $k_id) {
                                        if (isset($data[$k_id])) { $id_val = $data[$k_id]; break; }
                                    }

                                    // Ambil Nama Tahun Ajaran (Pencarian Fleksibel)
                                    $tahun_aj = '';
                                    foreach ($data as $key => $val) {
                                        if (strpos($key, 'tahun') !== false || strpos($key, 'nama') !== false || strpos($key, 'thn') !== false) {
                                            if (!empty($val) && $key != 'id' && $key != 'status') {
                                                $tahun_aj = $val;
                                                break;
                                            }
                                        }
                                    }
                                    if (empty($tahun_aj)) {
                                        // Fallback ke kolom urutan ke-2 jika nama kolom tidak sesuai kata kunci
                                        $vals = array_values($data);
                                        $tahun_aj = isset($vals[1]) ? $vals[1] : '-';
                                    }

                                    // Ambil Tanggal Mulai & Selesai
                                    $tgl_mulai   = $data['tanggal_mulai'] ?? $data['tgl_mulai'] ?? '-';
                                    $tgl_selesai = $data['tanggal_selesai'] ?? $data['tgl_selesai'] ?? '-';
                                    
                                    if ($tgl_mulai != '-') $tgl_mulai = date('d-m-Y', strtotime($tgl_mulai));
                                    if ($tgl_selesai != '-') $tgl_selesai = date('d-m-Y', strtotime($tgl_selesai));

                                    // Ambil Status
                                    $stt = $data['status'] ?? 'Aktif';
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-medium"><?= $no++; ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($tahun_aj); ?></td>
                                <td><?= htmlspecialchars($tgl_mulai); ?></td>
                                <td><?= htmlspecialchars($tgl_selesai); ?></td>
                                <td class="text-center">
                                    <?php if ($stt == 'Aktif') : ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">Aktif</span>
                                    <?php else : ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit_tahun_ajaran.php?id=<?= $id_val; ?>" class="btn-action-edit text-decoration-none btn-sm" title="Edit Data">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="hapus_tahun_ajaran.php?id=<?= $id_val; ?>" class="btn-action-delete text-decoration-none btn-sm" title="Hapus Data" onclick="return confirm('Yakin ingin menghapus data tahun ajaran ini?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else :
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 text-secondary"></i><br>
                                    Belum ada data Tahun Ajaran di database. Klik tombol <strong>Tambah Tahun Ajaran</strong> di atas.
                                </td>
                            </tr>
                            <?php endif; ?>
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
        $('#tabelTA').DataTable();
    });
</script>
</body>
</html>