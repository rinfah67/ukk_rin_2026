
<?php
session_start();

include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Memastikan hanya admin yang bisa mengakses halaman
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Mengambil semua kategori pelanggaran
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_pelanggaran_kategori ORDER BY id DESC"
);

// Memeriksa query
$error_query = '';
if (!$query) {
    $error_query = mysqli_error($koneksi);
}

// Mengambil pesan notifikasi
$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

// Fungsi mengamankan output
function e($nilai)
{
    return htmlspecialchars(
        (string) ($nilai ?? '-'),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Kategori Pelanggaran | SI PELANG</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Google Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
          rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            color: #334155;
        }

        .content-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .page-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: #dbeafe;
            color: #2563eb;
            font-size: 22px;
        }

        .table thead th {
            background-color: #eff6ff;
            color: #1e40af;
            border-bottom: 1px solid #dbeafe;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            padding: 14px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 13px 14px;
            font-size: 13px;
        }

        .table {
            margin-bottom: 0;
        }

        .btn-edit {
            background: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .btn-edit:hover {
            background: #fde68a;
            color: #92400e;
        }

        .btn-delete {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .btn-delete:hover {
            background: #fecaca;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 6px;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 10px;
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 13px;
            margin-top: 15px;
        }

        .page-title {
            font-weight: 700;
            color: #1e293b;
        }

        .description {
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            main {
                padding: 20px !important;
            }
        }

        .sidebar {
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    min-height: 100vh;
}

.sidebar .nav-link {
    color: #94a3b8;
    padding: 12px 18px;
    border-radius: 8px;
    margin-bottom: 5px;
    transition: 0.2s;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background-color: #2563eb;
    color: #ffffff;
}
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- Judul halaman -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <div>
                        <h3 class="page-title mb-1">
                            Kategori Pelanggaran
                        </h3>

                        <p class="description mb-0">
                            Kelola kategori pelanggaran siswa.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-house me-1"></i>
                        Dashboard
                    </a>

                    <a href="tambah_pelanggaran_kategori.php"
                       class="btn btn-primary">
                        <i class="fa-solid fa-plus me-1"></i>
                        Tambah Kategori
                    </a>
                </div>

            </div>

            <!-- Notifikasi berhasil -->
            <?php if ($pesan_sukses != ''): ?>
                <div class="alert alert-success alert-dismissible fade show"
                     role="alert">

                    <i class="fa-solid fa-circle-check me-2"></i>
                    <?= e($pesan_sukses); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>
                </div>
            <?php endif; ?>

            <!-- Notifikasi gagal -->
            <?php if ($pesan_error != ''): ?>
                <div class="alert alert-danger alert-dismissible fade show"
                     role="alert">

                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <?= e($pesan_error); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>
                </div>
            <?php endif; ?>

            <!-- Error database -->
            <?php if ($error_query != ''): ?>
                <div class="alert alert-danger">
                    <strong>Query database gagal:</strong>
                    <?= e($error_query); ?>
                </div>
            <?php endif; ?>

            <!-- Card tabel -->
            <div class="card content-card">

                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-table text-primary me-2"></i>
                        Daftar Kategori Pelanggaran
                    </h5>

                    <p class="description mb-0">
                        Data kategori pelanggaran yang tersimpan dalam database.
                    </p>
                </div>

                <div class="card-body p-4">

                    <div class="table-responsive">

                        <table id="tabelKategori"
                               class="table table-hover align-middle w-100">

                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if ($query && mysqli_num_rows($query) > 0): ?>

                                    <?php $no = 1; ?>

                                    <?php while ($data = mysqli_fetch_assoc($query)): ?>

                                        <tr>
                                            <td><?= $no++; ?></td>

                                            <td class="fw-semibold">
                                                <?= e($data['nama'] ?? '-'); ?>
                                            </td>

                                            <td>
                                                <?= e($data['deskripsi'] ?? '-'); ?>
                                            </td>

                                            <td>
                                                <?php if ((int) ($data['status_aktif'] ?? 0) === 1): ?>
                                                    <span class="badge bg-success">
                                                        Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        Tidak Aktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <div class="action-buttons">

                                                    <a href="edit_pelanggaran_kategori.php?id=<?= (int) $data['id']; ?>"
                                                       class="btn btn-sm btn-edit"
                                                       title="Edit kategori">

                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>

                                                    <a href="hapus_pelanggaran_kategori.php?id=<?= (int) $data['id']; ?>"
                                                       class="btn btn-sm btn-delete"
                                                       title="Hapus kategori"
                                                       onclick="return confirm('Yakin ingin menghapus kategori ini?');">

                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>

                                                </div>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                        <?php if ($query && mysqli_num_rows($query) === 0): ?>
                            <p class="text-center text-secondary mt-3 mb-0">
                                Belum ada data kategori pelanggaran.
                            </p>
                        <?php endif; ?>

                    </div>

                </div>

                <div class="card-footer bg-white border-0 text-secondary small px-4 pb-3">
                    SI PELANG &copy; <?= date('Y'); ?>
                </div>

            </div>

        </main>

    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tabelKategori').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [],

            language: {
                search: 'Cari:',
                searchPlaceholder: 'Cari kategori...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data untuk ditampilkan',
                infoFiltered: '(disaring dari _MAX_ total data)',
                zeroRecords: 'Kategori tidak ditemukan',
                emptyTable: 'Belum ada data kategori',
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
