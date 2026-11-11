
<?php
// kelola_guru.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Mengambil data guru
$query_guru = mysqli_query($koneksi, "
    SELECT *
    FROM t_guru
    ORDER BY id DESC
");

if (!$query_guru) {
    die("Query gagal: " . mysqli_error($koneksi));
}

// Menghitung jumlah guru aktif
$query_guru_aktif = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_guru
    WHERE status_aktif = 1
");

if (!$query_guru_aktif) {
    die("Query guru aktif gagal: " . mysqli_error($koneksi));
}

$data_guru_aktif = mysqli_fetch_assoc($query_guru_aktif);
$guru_aktif = (int)$data_guru_aktif['total'];

// Menghitung jumlah guru yang menjadi wali kelas aktif
$query_wali_kelas = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT guru_id) AS total
    FROM t_wali_kelas
    WHERE status_aktif = 1
");

if (!$query_wali_kelas) {
    die("Query wali kelas gagal: " . mysqli_error($koneksi));
}

$data_wali_kelas = mysqli_fetch_assoc($query_wali_kelas);
$guru_wali_kelas = (int)$data_wali_kelas['total'];

// Menghitung jumlah guru tidak aktif
$query_guru_tidak_aktif = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_guru
    WHERE status_aktif = 0
");

if (!$query_guru_tidak_aktif) {
    die("Query guru tidak aktif gagal: " . mysqli_error($koneksi));
}

$data_guru_tidak_aktif = mysqli_fetch_assoc($query_guru_tidak_aktif);
$guru_tidak_aktif = (int)$data_guru_tidak_aktif['total'];

// Fungsi mengamankan output
function e($nilai) {
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

    <title>Kelola Guru - SI PELANG</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        .container-fluid > .row {
            align-items: stretch;
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

        /* Judul halaman */
        .page-title {
            font-weight: 800;
            color: #1e293b;
        }

        /* Card Statistik */
        .stat-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
            height: 100%;
            transition: 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            font-size: 22px;
            flex-shrink: 0;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 3px;
        }

        .stat-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
        }

        /* Card Tabel */
        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
        }

        /* Header Tabel */
        .table-custom-header th {
            background-color: #eff6ff !important;
            color: #1e40af !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #dbeafe !important;
            padding: 14px 12px;
            white-space: nowrap;
        }

        #tabelGuru td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Tombol Edit */
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

        /* Tombol Hapus */
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

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .page-header {
                align-items: flex-start !important;
                flex-direction: column;
                gap: 15px;
            }

            .stat-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        include 'layout/sidebar.php';
        ?>

        <main class="col px-md-4 py-4">

            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="fa-solid fa-chalkboard-user text-primary me-2"></i>
                        Kelola Data Guru
                    </h3>

                    <p class="text-secondary mb-0">
                        Kelola data tenaga pengajar dan staf sekolah.
                    </p>
                </div>

                <a href="tambah_guru.php"
                   class="btn btn-primary fw-medium px-4 shadow-sm">
                    <i class="fa-solid fa-plus me-1"></i>
                    Tambah Guru
                </a>

            </div>

            <!-- Card Statistik Guru -->
            <div class="row g-3 mb-4">

                <!-- Guru Aktif -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex align-items-center justify-content-between">

                            <div>
                                <div class="stat-label mb-2">
                                    Guru Aktif
                                </div>

                                <div class="stat-number">
                                    <?= $guru_aktif; ?>
                                </div>

                                <small class="text-success">
                                    <i class="fa-solid fa-circle-check me-1"></i>
                                    Guru berstatus aktif
                                </small>
                            </div>

                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="fa-solid fa-user-check"></i>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Guru Wali Kelas -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex align-items-center justify-content-between">

                            <div>
                                <div class="stat-label mb-2">
                                    Guru Menjadi Wali Kelas
                                </div>

                                <div class="stat-number">
                                    <?= $guru_wali_kelas; ?>
                                </div>

                                <small class="text-primary">
                                    <i class="fa-solid fa-user-tie me-1"></i>
                                    Memiliki penugasan wali kelas aktif
                                </small>
                            </div>

                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Guru Tidak Aktif -->
                <div class="col-xl-4 col-md-6">
                    <div class="stat-card">
                        <div class="d-flex align-items-center justify-content-between">

                            <div>
                                <div class="stat-label mb-2">
                                    Guru Tidak Aktif
                                </div>

                                <div class="stat-number">
                                    <?= $guru_tidak_aktif; ?>
                                </div>

                                <small class="text-danger">
                                    <i class="fa-solid fa-circle-xmark me-1"></i>
                                    Guru berstatus tidak aktif
                                </small>
                            </div>

                            <div class="stat-icon bg-danger-subtle text-danger">
                                <i class="fa-solid fa-user-xmark"></i>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- Card Data Guru -->
            <div class="card card-custom p-4">

                <div class="border-bottom pb-3 mb-4">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fa-solid fa-table text-primary me-2"></i>
                        Daftar Data Guru
                    </h5>

                    <p class="text-secondary small mb-0">
                        Cari, urutkan, dan kelola data guru terdaftar.
                    </p>
                </div>

                <div class="table-responsive">

                    <table id="tabelGuru" class="table table-hover align-middle w-100">

                        <thead class="table-custom-header">
                            <tr>
                                <th class="text-center" style="width: 40px;">No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Gender</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            if (mysqli_num_rows($query_guru) > 0) :
                                while ($data = mysqli_fetch_assoc($query_guru)) :

                                    $id_val = $data['id'] ?? ($data['id_guru'] ?? 0);
                                    $jk = $data['jenis_kelamin'] ?? '';
                                    $status = (string)($data['status_aktif'] ?? '');
                            ?>

                            <tr>
                                <td class="text-center text-muted fw-medium">
                                    <?= $no++; ?>
                                </td>

                                <td class="fw-medium text-dark">
                                    <?= e($data['nip'] ?? '-'); ?>
                                </td>

                                <td class="fw-semibold text-dark">
                                    <?= e($data['nama'] ?? ($data['nama_guru'] ?? '-')); ?>
                                </td>

                                <td>
                                    <?php if ($jk == 'Laki-laki') : ?>

                                        <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1">
                                            <i class="fa-solid fa-mars me-1"></i>
                                            Laki-laki
                                        </span>

                                    <?php elseif ($jk == 'Perempuan') : ?>

                                        <span class="badge bg-light text-danger border border-danger-subtle px-2 py-1">
                                            <i class="fa-solid fa-venus me-1"></i>
                                            Perempuan
                                        </span>

                                    <?php else : ?>

                                        <span class="badge bg-secondary">-</span>

                                    <?php endif; ?>
                                </td>

                                <td class="text-muted">
                                    <?= e($data['email'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php if ($status === '1') : ?>

                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            <i class="fa-solid fa-circle-check me-1"></i>
                                            Aktif
                                        </span>

                                    <?php elseif ($status === '0') : ?>

                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                                            <i class="fa-solid fa-circle-xmark me-1"></i>
                                            Tidak Aktif
                                        </span>

                                    <?php else : ?>

                                        <span class="badge bg-secondary">
                                            Tidak diketahui
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="edit_guru.php?id=<?= (int)$id_val; ?>"
                                           class="btn-action-edit text-decoration-none btn-sm"
                                           title="Edit Data">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <a href="hapus_guru.php?id=<?= (int)$id_val; ?>"
                                           class="btn-action-delete text-decoration-none btn-sm"
                                           title="Hapus Data"
                                           onclick="return confirm('Yakin ingin menghapus data guru ini?')">
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

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {

        // Mengaktifkan DataTables
        $('#tabelGuru').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],

            language: {
                lengthMenu: "Tampilkan _MENU_ data",
                zeroRecords: "Data guru tidak ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "›",
                    previous: "‹"
                }
            }
        });

    });
</script>

</body>
</html>
