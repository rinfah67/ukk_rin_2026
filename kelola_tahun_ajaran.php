
<?php
// kelola_tahun_ajaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Mengambil data tahun ajaran
$query_ta = mysqli_query($koneksi, "
    SELECT id, nama, tanggal_mulai, tanggal_selesai, status_aktif
    FROM t_tahun_ajaran
    ORDER BY id DESC
");

if (!$query_ta) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}

// Fungsi format tanggal
function formatTanggal($tanggal)
{
    if (empty($tanggal) || $tanggal == '0000-00-00') {
        return '-';
    }

    return date('d-m-Y', strtotime($tanggal));
}

// Fungsi escape HTML
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

    <title>Kelola Tahun Ajaran - SI PELANG</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
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

        .sidebar .nav-link.text-danger:hover {
            background-color: #dc2626;
            color: #ffffff !important;
        }

        /* Judul Halaman */
        .page-title {
            font-weight: 800;
            color: #1e293b;
        }

        /* Card */
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }

        /* Header Tabel */
        .table-custom-header th {
            background-color: #eff6ff !important;
            color: #1e40af !important;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 14px 12px;
            white-space: nowrap;
            vertical-align: middle;
        }

        #tabelTA td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Tombol Aksi */
        .btn-action-edit {
            background-color: #fef3c7;
            color: #92400e;
            border: none;
            border-radius: 7px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-action-edit:hover {
            background-color: #fcd34d;
            color: #78350f;
        }

        .btn-action-delete {
            background-color: #fee2e2;
            color: #b91c1c;
            border: none;
            border-radius: 7px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-action-delete:hover {
            background-color: #fecaca;
            color: #991b1b;
        }

        /* DataTables */
        .dataTables_wrapper {
            font-size: 13px;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            padding: 6px 10px;
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter input:focus,
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            color: white !important;
            border: 1px solid #2563eb !important;
        }

        @media (max-width: 768px) {
            .page-header {
                align-items: flex-start !important;
                gap: 15px;
                flex-direction: column;
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

        <!-- Konten Utama -->
        <main class="col px-md-4 py-4">

            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="fa-solid fa-calendar-days text-primary me-2"></i>
                        Kelola Tahun Ajaran
                    </h3>

                    <p class="text-secondary mb-0">
                        Kelola data periode tahun ajaran SI PELANG.
                    </p>
                </div>

                <a href="tambah_tahun_ajaran.php" class="btn btn-primary px-3 fw-semibold">
                    <i class="fas fa-plus me-1"></i>
                    Tambah Tahun Ajaran
                </a>

            </div>

            <!-- Card Data Tahun Ajaran -->
            <div class="card card-custom p-4">

                <div class="mb-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-table text-primary me-2"></i>
                        Data Tahun Ajaran
                    </h5>

                    <p class="text-secondary small mb-0">
                        Cari, urutkan, edit, dan hapus data tahun ajaran.
                    </p>
                </div>

                <div class="table-responsive">

                    <table id="tabelTA" class="table table-bordered table-hover align-middle w-100">

                        <thead class="table-custom-header text-center">
                            <tr>
                                <th>No</th>
                                <th>Tahun Ajaran</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            while ($data = mysqli_fetch_assoc($query_ta)) :

                                $id = (int) $data['id'];
                                $nama = $data['nama'];

                                $tanggal_mulai = formatTanggal($data['tanggal_mulai']);
                                $tanggal_selesai = formatTanggal($data['tanggal_selesai']);

                                $status = (int) $data['status_aktif'];
                            ?>

                            <tr>

                                <td class="text-center">
                                    <?= $no++; ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= e($nama); ?>
                                </td>

                                <td>
                                    <?= e($tanggal_mulai); ?>
                                </td>

                                <td>
                                    <?= e($tanggal_selesai); ?>
                                </td>

                                <td class="text-center">

                                    <?php if ($status === 1) : ?>

                                        <span class="badge bg-success">
                                            Aktif
                                        </span>

                                    <?php else : ?>

                                        <span class="badge bg-secondary">
                                            Tidak Aktif
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="text-center text-nowrap">

                                    <a href="edit_tahun_ajaran.php?id=<?= $id; ?>"
                                       class="btn btn-action-edit">
                                        <i class="fas fa-pen-to-square"></i>
                                        Edit
                                    </a>

                                    <a href="hapus_tahun_ajaran.php?id=<?= $id; ?>"
                                       class="btn btn-action-delete"
                                       onclick="return confirm('Yakin ingin menghapus data tahun ajaran ini?')">
                                        <i class="fas fa-trash"></i>
                                        Hapus
                                    </a>

                                </td>

                            </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>
            </div>

        </main>

    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {

        // Mengaktifkan DataTables
        $('#tabelTA').DataTable({
            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            language: {
                emptyTable: "Belum ada data Tahun Ajaran di database.",
                zeroRecords: "Data tahun ajaran tidak ditemukan.",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data yang ditampilkan",
                infoFiltered: "(difilter dari _MAX_ total data)",
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
