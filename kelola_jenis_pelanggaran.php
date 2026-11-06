
<?php
// kelola_jenis_pelanggaran.php

session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Halaman hanya boleh diakses admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Mengambil data jenis pelanggaran dari database
$sql = "SELECT
            id,
            kode,
            nama,
            deskripsi,
            status_aktif,
            created_at,
            updated_at
        FROM t_pelanggaran
        ORDER BY id DESC";

$query = mysqli_query($koneksi, $sql);

// Mengecek kesalahan query
if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jenis Pelanggaran - SI PELANG</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fb;
        }

        .content-card {
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

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table thead th {
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 6px;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <!-- Judul halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>

                    <div>
                        <h4 class="mb-1 fw-bold">Jenis Pelanggaran</h4>
                        <p class="text-muted mb-0">
                            Kelola data jenis pelanggaran siswa
                        </p>
                    </div>
                </div>

                <!-- Tombol tambah -->
                <a href="tambah_jenis_pelanggaran.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Jenis Pelanggaran
                </a>

            </div>

            <!-- Tabel data -->
            <div class="card content-card">
                <div class="card-body">

                    <div class="table-responsive">

                        <table id="tabelPelanggaran"
                               class="table table-striped table-hover table-bordered align-middle w-100">

                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Kode</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Diubah</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php while ($data = mysqli_fetch_assoc($query)) : ?>

                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($data['id']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['kode']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['nama']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['deskripsi'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?php if ($data['status_aktif'] == 'aktif') : ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?= !empty($data['created_at'])
                                                ? date('d-m-Y H:i', strtotime($data['created_at']))
                                                : '-'; ?>
                                        </td>

                                        <td>
                                            <?= !empty($data['updated_at'])
                                                ? date('d-m-Y H:i', strtotime($data['updated_at']))
                                                : '-'; ?>
                                        </td>

                                        <td>
                                            <div class="action-buttons">

                                                <!-- Tombol edit -->
                                                <a href="edit_jenis_pelanggaran.php?id=<?= $data['id']; ?>"
                                                   class="btn btn-warning btn-sm"
                                                   title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <!-- Tombol hapus -->
                                                <a href="hapus_jenis_pelanggaran.php?id=<?= $data['id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   title="Hapus"
                                                   onclick="return confirm('Yakin ingin menghapus jenis pelanggaran ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Mengaktifkan DataTables -->
<script>
$(document).ready(function () {

    $('#tabelPelanggaran').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        order: [],

        scrollX: true,

        language: {
            search: 'Cari:',
            searchPlaceholder: 'Cari jenis pelanggaran...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data untuk ditampilkan',
            infoFiltered: '(disaring dari _MAX_ total data)',
            zeroRecords: 'Jenis pelanggaran tidak ditemukan',
            emptyTable: 'Belum ada data jenis pelanggaran',

            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        }

    });

});
</script>

</body>
</html>
