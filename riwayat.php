
<?php
// riwayat.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// =====================================
// CEK HAK AKSES
// =====================================

if (!in_array($_SESSION['role'] ?? '', ['admin', 'guru'])) {
    header("Location: dashboard.php");
    exit;
}

// =====================================
// MENGAMBIL INPUT PENCARIAN DAN STATUS
// =====================================

$cari = trim($_GET['cari'] ?? '');
$status = trim($_GET['status'] ?? '');

$current_page = basename($_SERVER['PHP_SELF']);

// =====================================
// FILTER BERDASARKAN GURU LOGIN
// =====================================

$filter_guru = '';
$guru_id = 0;

if (($_SESSION['role'] ?? '') === 'guru') {

    $id_user = (int) ($_SESSION['id_user'] ?? 0);

    // Mengambil ID guru berdasarkan akun yang login
    $stmt_guru = mysqli_prepare($koneksi, "
        SELECT id
        FROM t_guru
        WHERE user_id = ?
        LIMIT 1
    ");

    if (!$stmt_guru) {
        die("Query guru gagal: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt_guru, "i", $id_user);
    mysqli_stmt_execute($stmt_guru);

    $hasil_guru = mysqli_stmt_get_result($stmt_guru);
    $data_guru = mysqli_fetch_assoc($hasil_guru);

    mysqli_stmt_close($stmt_guru);

    if ($data_guru) {
        $guru_id = (int) $data_guru['id'];
        $filter_guru = " AND guru_id = $guru_id";
    } else {
        // Jika akun tidak terhubung dengan data guru
        $filter_guru = " AND 1 = 0";
    }
}

// =====================================
// QUERY DATA RIWAYAT
// =====================================

$sql = "
    SELECT *
    FROM t_pelanggaran_siswa
    WHERE 1=1
    $filter_guru
";

// Pencarian berdasarkan siswa, pelanggaran, kelas, dan guru
if ($cari !== '') {

    $kata_cari = mysqli_real_escape_string($koneksi, $cari);

    $sql .= "
        AND (
            nama_siswa LIKE '%$kata_cari%'
            OR nama_pelanggaran LIKE '%$kata_cari%'
            OR nama_kelas LIKE '%$kata_cari%'
            OR nama_guru LIKE '%$kata_cari%'
        )
    ";
}

// Filter berdasarkan status
if ($status !== '') {

    $status_aman = mysqli_real_escape_string($koneksi, $status);

    $sql .= " AND status = '$status_aman'";
}

// Urutkan dari tanggal terbaru
$sql .= " ORDER BY tanggal DESC, id DESC";

// Jalankan query
$query = mysqli_query($koneksi, $sql);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

// Menghitung jumlah data
$jumlah = mysqli_num_rows($query);

// =====================================
// MENENTUKAN NAMA GURU LOGIN
// =====================================

$nama_guru_login = '';

if (($_SESSION['role'] ?? '') === 'guru' && $guru_id > 0) {

    $hasil_nama = mysqli_query($koneksi, "
        SELECT nama
        FROM t_guru
        WHERE id = $guru_id
        LIMIT 1
    ");

    if ($hasil_nama && mysqli_num_rows($hasil_nama) > 0) {
        $data_nama = mysqli_fetch_assoc($hasil_nama);
        $nama_guru_login = $data_nama['nama'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Pelanggaran - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Konten Utama */

        main {
            min-width: 0;
        }

        /* Judul Halaman */

        .page-title {
            color: #1e293b;
            font-size: 25px;
            font-weight: 800;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
        }

        /* Card */

        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
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
            border-radius: 9px;
            font-size: 13px;
            padding: 10px 12px;
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
            color: #ffffff !important;
            border: 1px solid #2563eb !important;
        }

        /* Tombol */

        .btn-primary-custom {
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 9px;
            padding: 10px 18px;
            font-weight: 600;
        }

        .btn-primary-custom:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-reset-custom {
            border-radius: 9px;
            padding: 10px 16px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .page-header {
                align-items: flex-start !important;
                gap: 12px;
                flex-direction: column;
            }
        }

        @media print {
            body {
                background: #ffffff !important;
            }

            .sidebar,
            .no-print,
            .dataTables_filter,
            .dataTables_length,
            .dataTables_paginate,
            .dataTables_info {
                display: none !important;
            }

            main {
                width: 100% !important;
                padding: 0 !important;
            }

            .card-custom {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row flex-nowrap">

        <!-- SIDEBAR -->

        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->

        <main class="col px-md-4 py-4">

            <!-- HEADER HALAMAN -->

            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <div>

                    <h4 class="page-title mb-1">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        Riwayat Pelanggaran
                    </h4>

                    <p class="page-subtitle mb-0">
                        Daftar riwayat catatan pelanggaran siswa.
                    </p>

                </div>

                <button onclick="window.print()" class="btn btn-success no-print">
                    <i class="bi bi-printer me-1"></i>
                    Cetak Riwayat
                </button>

            </div>

            <!-- INFORMASI GURU LOGIN -->

            <?php if (($_SESSION['role'] ?? '') === 'guru'): ?>

                <div class="alert alert-primary border-0 rounded-3 mb-4">

                    <i class="bi bi-person-circle me-2"></i>

                    Menampilkan riwayat pelanggaran yang dicatat oleh:

                    <strong>
                        <?= htmlspecialchars($nama_guru_login ?: 'Guru'); ?>
                    </strong>

                </div>

            <?php endif; ?>

            <!-- RINGKASAN JUMLAH DATA -->

            <div class="card card-custom p-4 mb-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-clipboard-data text-primary fs-2"></i>
                    </div>

                    <div>

                        <p class="text-secondary small mb-1">
                            Jumlah Riwayat Ditampilkan
                        </p>

                        <div class="summary-number">

                            <?= number_format($jumlah); ?>

                            <span class="fs-6 fw-normal text-secondary">
                                data
                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- PENCARIAN DAN FILTER -->

            <div class="card card-custom p-4 mb-4 no-print">

                <div class="mb-3">

                    <h6 class="fw-bold mb-1">
                        <i class="bi bi-funnel text-primary me-2"></i>
                        Pencarian dan Filter
                    </h6>

                    <p class="text-secondary small mb-0">
                        Cari riwayat berdasarkan siswa, pelanggaran, kelas, atau guru.
                    </p>

                </div>

                <form method="GET" action="riwayat.php">

                    <div class="row g-3">

                        <!-- PENCARIAN -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Cari Riwayat
                            </label>

                            <input
                                type="text"
                                name="cari"
                                class="form-control"
                                placeholder="Nama siswa, pelanggaran, kelas, atau guru..."
                                value="<?= htmlspecialchars($cari); ?>"
                            >

                        </div>

                        <!-- FILTER STATUS -->

                        <div class="col-md-3">

                            <label class="form-label fw-semibold">
                                Filter Status
                            </label>

                            <select name="status" class="form-select">

                                <option value="">
                                    Semua Status
                                </option>

                                <option value="Belum diproses"
                                    <?= $status === 'Belum diproses' ? 'selected' : ''; ?>>
                                    Belum diproses
                                </option>

                                <option value="Ditindaklanjuti"
                                    <?= $status === 'Ditindaklanjuti' ? 'selected' : ''; ?>>
                                    Ditindaklanjuti
                                </option>

                                <option value="Selesai"
                                    <?= $status === 'Selesai' ? 'selected' : ''; ?>>
                                    Selesai
                                </option>

                            </select>

                        </div>

                        <!-- TOMBOL -->

                        <div class="col-md-3 d-flex align-items-end gap-2">

                            <button type="submit" class="btn btn-primary-custom">
                                <i class="bi bi-search me-1"></i>
                                Cari
                            </button>

                            <a href="riwayat.php" class="btn btn-outline-secondary btn-reset-custom">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Reset
                            </a>

                        </div>

                    </div>

                </form>

            </div>

            <!-- TABEL RIWAYAT -->

            <div class="card card-custom p-4">

                <div class="mb-3">

                    <h6 class="fw-bold mb-1">
                        <i class="bi bi-table text-primary me-2"></i>
                        Data Riwayat Pelanggaran
                    </h6>

                    <p class="text-secondary small mb-0">
                        Daftar lengkap riwayat pelanggaran sesuai hak akses akun.
                    </p>

                </div>

                <div class="table-responsive">

                    <table
                        id="tabelRiwayat"
                        class="table table-bordered table-hover align-middle w-100">

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

                            while ($data = mysqli_fetch_assoc($query)):

                                $status_data = strtolower(
                                    trim($data['status'] ?? '')
                                );
                            ?>

                                <tr>

                                    <td class="text-center">
                                        <?= $no++; ?>
                                    </td>

                                    <td>
                                        <?= !empty($data['tanggal'])
                                            ? date('d-m-Y', strtotime($data['tanggal']))
                                            : '-'; ?>
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
                                            <?= (int) ($data['poin'] ?? 0); ?> poin
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

                                        <?php if ($status_data === 'selesai'): ?>

                                            <span class="badge bg-success">
                                                Selesai
                                            </span>

                                        <?php elseif ($status_data === 'ditindaklanjuti'): ?>

                                            <span class="badge bg-info text-dark">
                                                Ditindaklanjuti
                                            </span>

                                        <?php elseif ($status_data === 'belum diproses'): ?>

                                            <span class="badge bg-danger">
                                                Belum diproses
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($data['status'] ?? '-'); ?>
                                            </span>

                                        <?php endif; ?>

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

<!-- JAVASCRIPT -->

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>

$(document).ready(function () {

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
