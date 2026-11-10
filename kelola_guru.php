<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// Ambil data guru dari database
$query_guru = mysqli_query($koneksi, "SELECT * FROM t_guru ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru - Pelanggaran Siswa</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
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

        /* Badge R dalam Lingkaran */
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

        /* Card Modern */
        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        /* Custom Header Tabel */
        .table-custom-header th {
            background-color: #eff6ff !important;
            color: #1e40af !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #dbeafe !important;
            padding: 14px 12px;
        }

        /* Tombol Aksi Custom (Outline Style) */
        .btn-action-edit {
            background-color: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-action-edit:hover {
            background-color: #d97706;
            color: #ffffff;
        }
        .btn-action-delete {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-action-delete:hover {
            background-color: #dc2626;
            color: #ffffff;
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
            <!-- Header Judul Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-chalkboard-user text-primary"></i> Kelola Data Guru
                    </h2>
                    <p class="text-muted mb-0">Kelola data tenaga pengajar dan staf sekolah.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="dashboard.php" class="btn btn-white text-secondary border fw-medium px-3 shadow-sm" style="border-radius: 10px;">
                        <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
                    </a>
                    <a href="tambah_guru.php" class="btn btn-primary fw-medium px-4 shadow-sm" style="border-radius: 10px; background-color: #2563eb;">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Guru
                    </a>
                </div>
            </div>

            <!-- Card Konten Tabel Data -->
            <div class="card card-custom p-4">
                <div class="border-bottom pb-3 mb-4">
                    <h5 class="fw-bold text-dark mb-1">Daftar Data Guru</h5>
                    <p class="text-muted small mb-0">Cari, urutkan, dan kelola data guru terdaftar.</p>
                </div>

                <div class="table-responsive">
                    <table id="tabelGuru" class="table table-hover align-middle w-100">
                        <thead class="table-custom-header">
                            <tr>
                                <th class="text-center" style="width: 40px;">No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Gender</th>
                                <th>No. Telepon</th>
                                <th>Email</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($query_guru && mysqli_num_rows($query_guru) > 0) :
                                while ($data = mysqli_fetch_assoc($query_guru)) :$id_val = isset($data['id']) ?$data['id'] : (isset($data['id_guru']) ?$data['id_guru'] : 0);
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-medium"><?= $no++; ?></td>
                                <td class="fw-medium text-dark"><?= htmlspecialchars(isset($data['nip']) ?$data['nip'] : '-'); ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars(isset($data['nama']) ?$data['nama'] : (isset($data['nama_guru']) ?$data['nama_guru'] : '-')); ?></td>
                                <td>
                                    <?php 
                                    $jk = isset($data['jenis_kelamin']) ?$data['jenis_kelamin'] : '';
                                    if ($jk == 'Laki-laki') : ?>
                                        <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1"><i class="fa-solid fa-mars me-1"></i>Laki-laki</span>
                                    <?php else : ?>
                                        <span class="badge bg-light text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-venus me-1"></i>Perempuan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars(isset($data['no_telp']) ?$data['no_telp'] : '-'); ?></td>
                                <td class="text-muted"><?= htmlspecialchars(isset($data['email']) ?$data['email'] : '-'); ?></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit_guru.php?id=<?= $id_val; ?>" class="btn-action-edit text-decoration-none btn-sm" title="Edit Data">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="hapus_guru.php?id=<?= $id_val; ?>" class="btn-action-delete text-decoration-none btn-sm" title="Hapus Data" onclick="return confirm('Yakin ingin menghapus data guru ini?')">
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

<!-- JQuery & Bootstrap 5 JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {$('#tabelGuru').DataTable({
            "pageLength": 10,
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Data guru tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 data",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "›",
                    "previous": "‹"
                }
            }
        });
    });
</script>
</body>
</html>