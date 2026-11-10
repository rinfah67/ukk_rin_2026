<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// Ambil data siswa dari database
$query_siswa = mysqli_query($koneksi, "SELECT * FROM t_siswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa - Pelanggaran Siswa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }

        /* Styling Sidebar */
        .sidebar {
            min-height: 100vh;
            background-color: #1e2229;
        }
        .sidebar .nav-link {
            color: #a0a7b5;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 4px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #0d6efd;
        }
        .sidebar .nav-link.text-danger:hover {
            background-color: #dc3545;
            color: #fff !important;
        }

        /* Badge R dalam Lingkaran Garis */
        .circle-r {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border: 2px solid currentColor;
            border-radius: 50%;
            font-size: 11px;
            font-weight: bold;
            line-height: 1;
        }

        /* Card Konten */
        .card-custom {
            background-color: #ffffff;
            border: 2px solid #0d6efd;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="fw-bold text-primary d-flex align-items-center gap-2">
                        <i class="fa-solid fa-users"></i> Kelola Siswa
                    </h2>
                    <p class="text-muted mb-0">Kelola data siswa yang terdaftar di Si Pelang.</p>
                </div>
                <a href="tambah_siswa.php" class="btn btn-primary fw-semibold px-3">+ Tambah Siswa</a>
            </div>

            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mb-4">← Kembali ke Dashboard</a>

            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-1">Data Siswa</h5>
                <p class="text-muted small mb-3">Cari, urutkan, dan kelola data siswa.</p>

                <div class="table-responsive">
                    <!-- ID 'tabelSiswa' digunakan untuk inisialisasi DataTables JS -->
                    <table id="tabelSiswa" class="table table-bordered table-hover align-middle w-100">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Lahir</th>
                                <th>Alamat</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($query_siswa && mysqli_num_rows($query_siswa) > 0) :
                                while ($data = mysqli_fetch_assoc($query_siswa)) : 
                                    $id_val = $data['id'] ?? $data['id_siswa'] ?? 0;
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['nis'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($data['nisn'] ?? '-'); ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($data['nama'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($data['jenis_kelamin'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($data['tanggal_lahir'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($data['alamat'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-success">Aktif</span>
                                </td>
                                <td class="text-center">
                                    <a href="edit_siswa.php?id=<?= $id_val; ?>" class="btn btn-warning btn-sm fw-semibold">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    <a href="hapus_siswa.php?id=<?= $id_val; ?>" class="btn btn-danger btn-sm fw-semibold" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </a>
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

<!-- DataTables JS & Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Mengubah tabel biasa menjadi Interactive DataTable
        $('#tabelSiswa').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Data siswa tidak ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });
</script>
</body>
</html>