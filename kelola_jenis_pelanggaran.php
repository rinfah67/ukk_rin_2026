
<?php
// kelola_jenis_pelanggaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// =====================================
// CEK HAK AKSES ADMIN
// =====================================
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// =====================================
// HALAMAN AKTIF
// =====================================
$current_page = basename($_SERVER['PHP_SELF']);

// =====================================
// AMBIL DATA JENIS PELANGGARAN
// =====================================
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

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

// =====================================
// FUNGSI ESCAPE HTML
// =====================================
function e($nilai)
{
    return htmlspecialchars(
        (string)($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Jenis Pelanggaran - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px !important;
            min-width: 250px;
            flex: 0 0 250px !important;
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar .brand-title {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            white-space: nowrap;
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: 0.2s;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .sidebar .nav-link i {
            width: 20px;
            min-width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #2563eb;
            color: #ffffff;
        }

        .sidebar .nav-link.text-danger:hover {
            background-color: #dc2626;
            color: #ffffff !important;
        }

        .sidebar .menu-title {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 20px 12px 10px;
            letter-spacing: 1px;
        }

        /* KONTEN */
        main {
            min-width: 0;
        }

        .page-title {
            color: #1e293b;
            font-weight: 800;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
        }

        /* CARD TABEL */
        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        /* HEADER TABEL */
        .table-custom-header th {
            background-color: #eff6ff !important;
            color: #1e40af !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dbeafe !important;
            padding: 14px 12px;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
            font-size: 13px;
            color: #475569;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* TOMBOL TAMBAH */
        .btn-tambah {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 9px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
            white-space: nowrap;
        }

        .btn-tambah:hover {
            background-color: #1d4ed8;
            color: #ffffff;
        }

        /* TOMBOL EDIT */
        .btn-action-edit {
            background-color: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
            border-radius: 7px;
            padding: 6px 10px;
            transition: 0.2s;
        }

        .btn-action-edit:hover {
            background-color: #d97706;
            color: #ffffff;
        }

        /* TOMBOL HAPUS */
        .btn-action-delete {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            border-radius: 7px;
            padding: 6px 10px;
            transition: 0.2s;
        }

        .btn-action-delete:hover {
            background-color: #dc2626;
            color: #ffffff;
        }

        /* DATATABLES */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 7px 12px;
            margin-left: 8px;
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 5px 10px;
            outline: none;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 13px;
            margin-top: 15px;
        }

        .dataTables_wrapper .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .dataTables_wrapper .page-link {
            color: #2563eb;
        }

        .table-responsive {
            width: 100%;
        }

        /* RESPONSIVE */
        @media (max-width: 767.98px) {
            main {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <?php
        // Menampilkan sidebar
        $current_page = basename($_SERVER['PHP_SELF']);
        include 'layout/sidebar.php';
        ?>

        <main class="col px-md-4 py-4">

            <!-- HEADER HALAMAN -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="fa-solid fa-triangle-exclamation text-primary me-2"></i>
                        Kelola Jenis Pelanggaran
                    </h3>

                    <p class="page-subtitle mb-0">
                        Kelola data jenis pelanggaran siswa.
                    </p>
                </div>

                <a href="tambah_jenis_pelanggaran.php" class="btn-tambah">
                    <i class="fa-solid fa-plus me-1"></i>
                    Tambah Jenis Pelanggaran
                </a>

            </div>

            <!-- CARD TABEL -->
            <div class="card-custom p-4">

                <!-- JUDUL CARD -->
                <div class="border-bottom pb-3 mb-4">

                    <h5 class="fw-bold text-dark mb-1">
                        Daftar Jenis Pelanggaran
                    </h5>

                    <p class="text-muted small mb-0">
                        Cari, urutkan, dan kelola data jenis pelanggaran yang terdaftar.
                    </p>

                </div>

                <!-- TABEL -->
                <div class="table-responsive">

                    <table id="tabelPelanggaran"
                           class="table table-hover align-middle w-100">

                        <thead class="table-custom-header">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Kode</th>
                                <th>Jenis Pelanggaran</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Status</th>
                                <th>Dibuat</th>
                                <th>Diubah</th>
                                <th class="text-center" style="width: 110px;">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            if (mysqli_num_rows($query) > 0) :
                                while ($data = mysqli_fetch_assoc($query)) :

                                    $id = (int) $data['id'];
                            ?>

                            <tr>

                                <!-- NOMOR -->
                                <td class="text-center text-muted fw-semibold">
                                    <?= $no++; ?>
                                </td>

                                <!-- KODE -->
                                <td class="fw-semibold text-dark">
                                    <?= e($data['kode']); ?>
                                </td>

                                <!-- NAMA -->
                                <td class="fw-semibold text-dark">
                                    <?= e($data['nama']); ?>
                                </td>

                                <!-- DESKRIPSI -->
                                <td>
                                    <?= e($data['deskripsi'] ?: '-'); ?>
                                </td>

                                <!-- STATUS -->
                                <td class="text-center">

                                    <?php if ($data['status_aktif'] == 'aktif' || $data['status_aktif'] == '1') : ?>

                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            Aktif
                                        </span>

                                    <?php else : ?>

                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                                            Tidak Aktif
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- DIBUAT -->
                                <td>
                                    <?= !empty($data['created_at'])
                                        ? date('d-m-Y H:i', strtotime($data['created_at']))
                                        : '-'; ?>
                                </td>

                                <!-- DIUBAH -->
                                <td>
                                    <?= !empty($data['updated_at'])
                                        ? date('d-m-Y H:i', strtotime($data['updated_at']))
                                        : '-'; ?>
                                </td>

                                <!-- AKSI -->
                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="edit_jenis_pelanggaran.php?id=<?= $id; ?>"
                                           class="btn-action-edit text-decoration-none"
                                           title="Edit Data">

                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <a href="hapus_jenis_pelanggaran.php?id=<?= $id; ?>"
                                           class="btn-action-delete text-decoration-none"
                                           title="Hapus Data"
                                           onclick="return confirm('Yakin ingin menghapus jenis pelanggaran ini?')">

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

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DATATABLES JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- INISIALISASI DATATABLES -->
<script>
    $(document).ready(function() {

        $('#tabelPelanggaran').DataTable({

            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            scrollX: true,

            language: {
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Jenis pelanggaran tidak ditemukan",
                emptyTable: "Belum ada data jenis pelanggaran",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                search: "Cari:",
                searchPlaceholder: "Cari jenis pelanggaran...",

                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }

        });

    });
</script>

</body>
</html>
