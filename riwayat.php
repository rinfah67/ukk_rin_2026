<?php
// riwayat.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Mengambil kata pencarian
$cari = isset($_GET['cari'])
    ? mysqli_real_escape_string($koneksi, trim($_GET['cari']))
    : '';

// Mengambil filter status
$status = isset($_GET['status'])
    ? mysqli_real_escape_string($koneksi, trim($_GET['status']))
    : '';

// Query dasar
$sql = "SELECT *
        FROM t_pelanggaran_siswa
        WHERE 1=1";

// Pencarian riwayat
if ($cari != '') {
    $sql .= " AND (
        nama_siswa LIKE '%$cari%'
        OR nama_pelanggaran LIKE '%$cari%'
        OR nama_kelas LIKE '%$cari%'
        OR nama_guru LIKE '%$cari%'
    )";
}

// Filter status
if ($status != '') {
    $sql .= " AND status = '$status'";
}

// Urutkan dari tanggal terbaru
$sql .= " ORDER BY tanggal DESC, id DESC";

// Jalankan query
$query = mysqli_query($koneksi, $sql);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

// Hitung jumlah data
$jumlah = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Pelanggaran - SI PELANG</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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

        /* Ringkasan */
        .summary-number {
            font-size: 28px;
            font-weight: 800;
            color: #2563eb;
        }

        /* Header Tabel */
        .table-custom-header th {
            background-color: #eff6ff;
            color: #1e40af;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 14px 12px;
            white-space: nowrap;
            vertical-align: middle;
        }

        #tabelRiwayat td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Form */
        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 13px;
            padding: 9px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
    <div class="row">

        <!-- Sidebar -->
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        include 'layout/sidebar.php';
        ?>

        <!-- Konten Utama -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="fas fa-clock-rotate-left text-primary me-2"></i>
                        Riwayat Pelanggaran
                    </h3>

                    <p class="text-secondary mb-0">
                        Daftar seluruh catatan pelanggaran siswa yang telah dicatat.
                    </p>
                </div>

            </div>

            <!-- Ringkasan Jumlah Data -->
            <div class="card card-custom p-4 mb-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                        <i class="fas fa-clipboard-list text-primary fa-2x"></i>
                    </div>

                    <div>
                        <p class="text-secondary small mb-1">
                            Jumlah Riwayat Ditampilkan
                        </p>

                        <div class="summary-number">
                            <?= $jumlah; ?>
                            <span class="fs-6 fw-normal text-secondary">data</span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Pencarian dan Filter -->
            <div class="card card-custom p-4 mb-4">

                <div class="mb-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-filter text-primary me-2"></i>
                        Pencarian dan Filter
                    </h5>

                    <p class="text-secondary small mb-0">
                        Cari riwayat berdasarkan siswa, pelanggaran, kelas, atau guru.
                    </p>
                </div>

                <form method="GET" action="riwayat.php">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Cari Riwayat
                            </label>

                            <input
                                type="text"
                                name="cari"
                                class="form-control"
                                placeholder="Nama siswa, pelanggaran, kelas..."
                                value="<?= htmlspecialchars($cari); ?>"
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                Filter Status
                            </label>

                            <select name="status" class="form-select">

                                <option value="">Semua Status</option>

                                <option value="belum"
                                    <?= $status == 'belum' ? 'selected' : ''; ?>>
                                    Belum Ditindak
                                </option>

                                <option value="selesai"
                                    <?= $status == 'selesai' ? 'selected' : ''; ?>>
                                    Selesai
                                </option>

                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end gap-2">

                            <button type="submit" class="btn btn-primary px-3">
                                <i class="fas fa-search me-1"></i>
                                Cari
                            </button>

                            <a href="riwayat.php" class="btn btn-outline-secondary">
                                <i class="fas fa-rotate-left me-1"></i>
                                Reset
                            </a>

                        </div>

                    </div>

                </form>

            </div>

            <!-- Card Tabel Riwayat -->
            <div class="card card-custom p-4">

                <div class="mb-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-table text-primary me-2"></i>
                        Data Riwayat Pelanggaran
                    </h5>

                    <p class="text-secondary small mb-0">
                        Daftar lengkap riwayat pelanggaran siswa.
                    </p>
                </div>

                <div class="table-responsive">

                    <table id="tabelRiwayat" class="table table-bordered table-hover align-middle w-100">

                        <thead class="table-custom-header text-center">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jenis Pelanggaran</th>
                                <th>Poin</th>
                                <th>Guru Pencatat</th>
                                <th>Keterangan</th>
                                <th>Tindakan</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            if ($jumlah > 0) :
                                while ($data = mysqli_fetch_assoc($query)) :

                                    $status_data = strtolower(
                                        trim($data['status'] ?? '')
                                    );
                            ?>

                            <tr>
                                <td class="text-center">
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['tanggal'] ?? '-'); ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= htmlspecialchars($data['nama_siswa'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_pelanggaran'] ?? '-'); ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">
                                        <?= htmlspecialchars((string) ($data['poin'] ?? 0)); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_guru'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['keterangan'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['tindakan'] ?? '-'); ?>
                                </td>

                                <td class="text-center">

                                    <?php if ($status_data == 'selesai') : ?>

                                        <span class="badge bg-success">
                                            Selesai
                                        </span>

                                    <?php elseif (
                                        $status_data == 'belum' ||
                                        $status_data == 'belum ditindak'
                                    ) : ?>

                                        <span class="badge bg-danger">
                                            Belum Ditindak
                                        </span>

                                    <?php else : ?>

                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($data['status'] ?? '-'); ?>
                                        </span>

                                    <?php endif; ?>

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

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {

        // Mengaktifkan DataTables
        $('#tabelRiwayat').DataTable({

            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            order: [],

            language: {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data riwayat tidak ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data tersedia",
                infoFiltered: "(difilter dari _MAX_ total data)",
                search: "Cari:",
                emptyTable: "Belum ada riwayat pelanggaran",
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
