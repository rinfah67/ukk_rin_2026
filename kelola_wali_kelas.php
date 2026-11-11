
<?php
// kelola_wali_kelas.php

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
// MENGAMBIL DATA WALI KELAS
// =====================================
$sql = "SELECT
            wk.id,
            wk.guru_id,
            wk.kelas_id,
            wk.tahun_ajaran_id,
            wk.tanggal_mulai,
            wk.tanggal_selesai,
            wk.status_aktif,
            g.nip,
            g.nama AS nama_guru,
            k.nama AS nama_kelas,
            k.tingkat,
            k.jurusan,
            ta.nama AS tahun_ajaran
        FROM t_wali_kelas wk
        LEFT JOIN t_guru g ON wk.guru_id = g.id
        LEFT JOIN t_kelas k ON wk.kelas_id = k.id
        LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
        ORDER BY wk.id DESC";

$query = mysqli_query($koneksi, $sql);

// =====================================
// ERROR QUERY
// =====================================
$error_query = $query ? '' : mysqli_error($koneksi);

// =====================================
// MENGHITUNG JUMLAH DATA
// =====================================
$total_wali_kelas = $query ? mysqli_num_rows($query) : 0;

$total_aktif = 0;
$total_tidak_aktif = 0;

// =====================================
// NOTIFIKASI
// =====================================
$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

// =====================================
// FUNGSI FORMAT TANGGAL
// =====================================
function formatTanggal($tanggal)
{
    if (
        empty($tanggal) ||
        $tanggal == '0000-00-00' ||
        $tanggal == '9999-12-31'
    ) {
        return '-';
    }

    return date('d-m-Y', strtotime($tanggal));
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

// =====================================
// MENGHITUNG STATUS
// =====================================
if ($query && $total_wali_kelas > 0) {
    while ($data_status = mysqli_fetch_assoc($query)) {
        if ((int)($data_status['status_aktif'] ?? 0) === 1) {
            $total_aktif++;
        } else {
            $total_tidak_aktif++;
        }
    }

    // Mengembalikan posisi hasil query ke awal
    mysqli_data_seek($query, 0);
}

// Halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Wali Kelas - SI PELANG</title>

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
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
            overflow-x: hidden;
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

        /* Konten utama */
        main {
            min-width: 0;
        }

        /* Judul halaman */
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

        /* Header tabel */
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

        #tabelWaliKelas td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
            white-space: nowrap;
        }

        /* Tombol tambah */
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        /* Tombol aksi */
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

        /* Statistik */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03);
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-number {
            font-size: 21px;
            font-weight: 800;
            color: #1e293b;
        }

        .stat-label {
            font-size: 12px;
            color: #64748b;
        }

        /* DataTables */
        .dataTables_wrapper {
            font-size: 13px;
            width: 100%;
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

        /* Responsif tablet dan HP */
        @media (max-width: 768px) {
            main {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .page-header {
                align-items: flex-start !important;
                gap: 15px;
                flex-direction: column;
            }

            .page-title {
                font-size: 21px;
            }

            .page-description {
                font-size: 12px;
            }

            .stat-card {
                padding: 13px;
            }

            .stat-number {
                font-size: 18px;
            }

            .stat-label {
                font-size: 11px;
            }

            .card-custom .card-body {
                padding: 15px !important;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            #tabelWaliKelas {
                min-width: 1050px;
            }

            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length {
                width: 100%;
                text-align: left;
                margin-bottom: 12px;
            }

            .dataTables_wrapper .dataTables_filter input {
                max-width: 150px;
            }

            .dataTables_wrapper .dataTables_paginate {
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        @media (max-width: 400px) {
            .stat-card {
                padding: 10px;
            }

            .stat-icon {
                width: 34px;
                height: 34px;
                font-size: 15px;
            }

            .stat-number {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <!-- SIDEBAR -->
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        include 'layout/sidebar.php';
        ?>

        <!-- KONTEN UTAMA -->
        <main class="col px-md-4 py-4">

            <!-- HEADER HALAMAN -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                        Kelola Wali Kelas
                    </h3>

                    <p class="text-secondary mb-0">
                        Kelola penugasan guru sebagai wali kelas berdasarkan kelas dan tahun ajaran.
                    </p>
                </div>

                <a href="tambah_wali_kelas.php" class="btn btn-primary px-3 py-2 fw-semibold">
                    <i class="fas fa-plus me-1"></i>
                    Tambah Wali Kelas
                </a>

            </div>

            <!-- NOTIFIKASI SUKSES -->
            <?php if ($pesan_sukses !== '') : ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">

                    <i class="fas fa-check-circle me-2"></i>

                    <?= e($pesan_sukses); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>

            <!-- NOTIFIKASI ERROR -->
            <?php if ($pesan_error !== '') : ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <i class="fas fa-exclamation-circle me-2"></i>

                    <?= e($pesan_error); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>

            <!-- ERROR DATABASE -->
            <?php if ($error_query !== '') : ?>

                <div class="alert alert-danger">
                    <strong>
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        Query database gagal:
                    </strong>

                    <?= e($error_query); ?>
                </div>

            <?php endif; ?>

            <!-- STATISTIK -->
            <div class="row g-3 mb-4">

                <div class="col-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">

                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-chalkboard-user"></i>
                            </div>

                            <div>
                                <div class="stat-number">
                                    <?= $total_wali_kelas; ?>
                                </div>

                                <div class="stat-label">
                                    Total Wali Kelas
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">

                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>

                            <div>
                                <div class="stat-number">
                                    <?= $total_aktif; ?>
                                </div>

                                <div class="stat-label">
                                    Wali Kelas Aktif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">

                            <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                                <i class="fas fa-user-slash"></i>
                            </div>

                            <div>
                                <div class="stat-number">
                                    <?= $total_tidak_aktif; ?>
                                </div>

                                <div class="stat-label">
                                    Tidak Aktif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- CARD DATA WALI KELAS -->
            <div class="card card-custom">

                <div class="card-body p-4">

                    <div class="mb-3">
                        <h5 class="fw-bold mb-1">
                            <i class="fas fa-table text-primary me-2"></i>
                            Data Wali Kelas
                        </h5>

                        <p class="text-secondary small mb-0">
                            Cari, urutkan, edit, dan hapus data penugasan wali kelas.
                        </p>
                    </div>

                    <!-- TABEL -->
                    <div class="table-responsive">

                        <table id="tabelWaliKelas"
                               class="table table-bordered table-hover align-middle w-100">

                            <thead class="table-custom-header text-center">

                                <tr>
                                    <th>No</th>
                                    <th>NIP</th>
                                    <th>Nama Guru</th>
                                    <th>Kelas</th>
                                    <th>Tingkat</th>
                                    <th>Jurusan</th>
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

                                if ($query && mysqli_num_rows($query) > 0) :

                                    while ($data = mysqli_fetch_assoc($query)) :

                                        $id_val = (int) $data['id'];
                                        $status = (int) ($data['status_aktif'] ?? 0);
                                ?>

                                    <tr>

                                        <td class="text-center">
                                            <?= $no++; ?>
                                        </td>

                                        <td>
                                            <?= e($data['nip'] ?? '-'); ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= e($data['nama_guru'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= e($data['nama_kelas'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= e($data['tingkat'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= e($data['jurusan'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= e($data['tahun_ajaran'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= e(formatTanggal($data['tanggal_mulai'] ?? '')); ?>
                                        </td>

                                        <td>
                                            <?= e(formatTanggal($data['tanggal_selesai'] ?? '')); ?>
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

                                            <a href="edit_wali_kelas.php?id=<?= $id_val; ?>"
                                               class="btn btn-action-edit">

                                                <i class="fas fa-pen-to-square"></i>
                                                Edit

                                            </a>

                                            <a href="hapus_wali_kelas.php?id=<?= $id_val; ?>"
                                               class="btn btn-action-delete"
                                               onclick="return confirm('Yakin ingin menghapus data wali kelas ini?')">

                                                <i class="fas fa-trash"></i>
                                                Hapus

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

            </div>

        </main>

    </div>
</div>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {

    // Mengaktifkan DataTables
    $('#tabelWaliKelas').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        scrollX: true,

        language: {

            lengthMenu: "Tampilkan _MENU_ data per halaman",

            zeroRecords: "Data wali kelas tidak ditemukan",

            emptyTable: "Belum ada data wali kelas",

            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",

            infoEmpty: "Tidak ada data tersedia",

            infoFiltered: "(difilter dari _MAX_ total data)",

            search: "Cari:",

            searchPlaceholder: "Cari wali kelas...",

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
