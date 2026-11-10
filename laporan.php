
<?php
// laporan.php

session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Mengambil role dan ID pengguna
$role = $_SESSION['role'] ?? '';
$id_user = (int) ($_SESSION['id_user'] ?? 0);

// Hanya admin dan guru yang boleh mengakses
if ($role !== 'admin' && $role !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

$is_walikelas = false;
$id_guru = 0;

// Memeriksa apakah guru adalah wali kelas aktif
if ($role === 'guru') {

    $stmt_guru = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_guru WHERE user_id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt_guru, "i", $id_user);
    mysqli_stmt_execute($stmt_guru);

    $hasil_guru = mysqli_stmt_get_result($stmt_guru);
    $data_guru = mysqli_fetch_assoc($hasil_guru);

    if ($data_guru) {

        $id_guru = (int) $data_guru['id'];

        $stmt_wali = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM t_wali_kelas
             WHERE guru_id = ?
             AND status_aktif = 1
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt_wali, "i", $id_guru);
        mysqli_stmt_execute($stmt_wali);

        $hasil_wali = mysqli_stmt_get_result($stmt_wali);

        if (mysqli_num_rows($hasil_wali) > 0) {
            $is_walikelas = true;
        }
    }

    // Guru yang bukan wali kelas tidak boleh membuka laporan
    if (!$is_walikelas) {
        echo "<script>
            alert('Halaman laporan hanya dapat diakses oleh wali kelas!');
            window.location.href = 'dashboard.php';
        </script>";
        exit;
    }
}

// Mengambil laporan berdasarkan hak akses
if ($role === 'admin') {

    // Admin melihat seluruh laporan
    $sql = "SELECT *
            FROM t_pelanggaran_siswa
            ORDER BY tanggal DESC, id DESC";

    $query = mysqli_query($koneksi, $sql);

} else {

    // Wali kelas hanya melihat laporan siswa di kelas tanggung jawabnya
    $sql = "SELECT DISTINCT p.*
            FROM t_pelanggaran_siswa p
            INNER JOIN t_kelas_siswa ks
                ON ks.siswa_id = p.siswa_id
            INNER JOIN t_wali_kelas wk
                ON wk.kelas_id = ks.kelas_id
                AND wk.tahun_ajaran_id = ks.tahun_ajaran_id
            WHERE wk.guru_id = ?
              AND wk.status_aktif = 1
              AND ks.status_aktif = 1
            ORDER BY p.tanggal DESC, p.id DESC";

    $stmt_laporan = mysqli_prepare($koneksi, $sql);

    mysqli_stmt_bind_param($stmt_laporan, "i", $id_guru);
    mysqli_stmt_execute($stmt_laporan);

    $query = mysqli_stmt_get_result($stmt_laporan);
}

// Memeriksa error query
$error_query = $query ? '' : mysqli_error($koneksi);

// Halaman aktif untuk sidebar
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laporan Pelanggaran - SI PELANG</title>

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

        #tabelLaporan td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Ikon Judul */
        .page-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background-color: #dbeafe;
            color: #2563eb;
            font-size: 23px;
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

        /* Cetak */
        @media print {
            body {
                background: white !important;
            }

            .sidebar,
            aside,
            .no-print,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_paginate,
            .dataTables_wrapper .dataTables_info {
                display: none !important;
            }

            main {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card-custom {
                box-shadow: none !important;
                border: none !important;
            }

            .table {
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 5px !important;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                align-items: flex-start !important;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten Utama -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <div class="d-flex align-items-center gap-3">

                    <div class="page-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>

                    <div>
                        <h3 class="page-title mb-1">
                            Laporan Pelanggaran
                        </h3>

                        <p class="text-secondary mb-0">
                            Rekap data pelanggaran siswa SI PELANG.
                        </p>
                    </div>

                </div>

                <div class="d-flex gap-2 no-print">

                    <button type="button"
                            class="btn btn-primary fw-semibold px-3"
                            onclick="window.print()">

                        <i class="fas fa-print me-1"></i>
                        Cetak Laporan

                    </button>

                </div>

            </div>

            <!-- Error Query -->
            <?php if ($error_query !== ''): ?>

                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>

                    <strong>Gagal mengambil data:</strong>
                    <?= htmlspecialchars($error_query); ?>
                </div>

            <?php endif; ?>

            <!-- Informasi Hak Akses -->
            <div class="alert alert-info no-print">

                <i class="fas fa-circle-info me-2"></i>

                <?php if ($role === 'admin'): ?>

                    Anda melihat seluruh laporan pelanggaran siswa.

                <?php else: ?>

                    Anda melihat laporan siswa pada kelas yang menjadi tanggung jawab Anda.

                <?php endif; ?>

            </div>

            <!-- Card Data Laporan -->
            <div class="card card-custom p-4">

                <div class="mb-3">

                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-table text-primary me-2"></i>
                        Data Laporan Pelanggaran
                    </h5>

                    <p class="text-secondary small mb-0">
                        Cari, urutkan, dan cetak data laporan pelanggaran.
                    </p>

                </div>

                <div class="table-responsive">

                    <table id="tabelLaporan"
                           class="table table-bordered table-hover align-middle w-100">

                        <thead class="table-custom-header text-center">

                            <tr>
                                <th>No.</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Pelanggaran</th>
                                <th>Tanggal</th>
                                <th>Poin</th>
                                <th>Tindakan</th>
                                <th>Status</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if ($query && mysqli_num_rows($query) > 0): ?>

                                <?php $no = 1; ?>

                                <?php while ($row = mysqli_fetch_assoc($query)): ?>

                                    <tr>

                                        <td class="text-center">
                                            <?= $no++; ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_pelanggaran'] ?? '-'); ?>
                                        </td>

                                        <td class="text-center">
                                            <?php
                                            if (!empty($row['tanggal'])) {
                                                echo date('d-m-Y', strtotime($row['tanggal']));
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-danger">
                                                <?= (int) ($row['poin'] ?? 0); ?> Poin
                                            </span>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tindakan'] ?? '-'); ?>
                                        </td>

                                        <td class="text-center">

                                            <?php
                                            $status = $row['status'] ?? '-';

                                            if ($status === 'Selesai') {
                                                echo '<span class="badge bg-success">Selesai</span>';
                                            } elseif ($status === 'Proses') {
                                                echo '<span class="badge bg-warning text-dark">Proses</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">'
                                                    . htmlspecialchars($status)
                                                    . '</span>';
                                            }
                                            ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                    <?php if ($query && mysqli_num_rows($query) === 0): ?>

                        <p class="text-center text-secondary mt-3 mb-0">
                            Belum ada data laporan pelanggaran.
                        </p>

                    <?php endif; ?>

                </div>

            </div>

            <!-- Footer -->
            <div class="text-center text-secondary small mt-4">
                SI PELANG &copy; <?= date('Y'); ?>
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
        $('#tabelLaporan').DataTable({

            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            order: [],

            language: {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data laporan tidak ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data tersedia",
                infoFiltered: "(difilter dari _MAX_ total data)",
                search: "Cari:",
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
