
<?php
// kelola_kelas_siswa.php

session_start();
include 'config/koneksi.php';

// =====================================
// CEK LOGIN
// =====================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

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
// AMBIL DATA KELAS SISWA
// =====================================
$sql = "SELECT *
        FROM t_kelas_siswa
        ORDER BY id DESC";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data kelas siswa: " . mysqli_error($koneksi));
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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Kelas Siswa - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* SIDEBAR - DISAMAKAN DENGAN DASHBOARD */
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

        /* CARD */
        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        /* HEADER TABEL */
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

        /* TOMBOL EDIT */
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

        /* TOMBOL HAPUS */
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

        /* DATATABLES */
        .dt-search input,
        .dt-length select {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            padding: 7px 12px !important;
        }

        .table-responsive {
            width: 100%;
        }

        .table td {
            vertical-align: middle;
            white-space: nowrap;
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

                <!-- HEADER HALAMAN -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

                    <div>
                        <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                            <i class="fa-solid fa-users text-primary"></i>
                            Kelola Kelas Siswa
                        </h2>

                        <p class="text-muted mb-0">
                            Kelola data penempatan siswa ke dalam kelas.
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">

                        <a href="dashboard.php"
                           class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                           style="border-radius: 10px;">

                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Dashboard
                        </a>

                        <a href="tambah_kelas.php"
                           class="btn btn-primary fw-medium px-4 shadow-sm"
                           style="border-radius: 10px; background-color: #2563eb;">

                            <i class="fa-solid fa-plus me-1"></i>
                            Tambah Kelas
                        </a>

                    </div>

                </div>

                <!-- CARD TABEL -->
                <div class="card card-custom p-4">

                    <div class="border-bottom pb-3 mb-4">

                        <h5 class="fw-bold text-dark mb-1">
                            Daftar Kelas Siswa
                        </h5>

                        <p class="text-muted small mb-0">
                            Cari, urutkan, dan kelola data penempatan siswa.
                        </p>

                    </div>

                    <!-- TABEL -->
                    <div class="table-responsive">

                        <table id="tabelKelas"
                               class="table table-hover align-middle w-100">

                            <thead class="table-custom-header">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Siswa ID</th>
                                    <th>Tahun Ajaran ID</th>
                                    <th>Kelas ID</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th class="text-center">Status</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php
                                $no = 1;

                                while ($kelas = mysqli_fetch_assoc($hasil)) {
                                    $id = (int)$kelas['id'];
                                ?>

                                    <tr>

                                        <!-- NOMOR -->
                                        <td class="text-center text-muted fw-medium">
                                            <?= $no++; ?>
                                        </td>

                                        <!-- SISWA ID -->
                                        <td>
                                            <?= e($kelas['siswa_id']); ?>
                                        </td>

                                        <!-- TAHUN AJARAN ID -->
                                        <td>
                                            <?= e($kelas['tahun_ajaran_id']); ?>
                                        </td>

                                        <!-- KELAS ID -->
                                        <td>
                                            <?= e($kelas['kelas_id']); ?>
                                        </td>

                                        <!-- TANGGAL MULAI -->
                                        <td>
                                            <?= e($kelas['tanggal_mulai'] ?? '-'); ?>
                                        </td>

                                        <!-- TANGGAL SELESAI -->
                                        <td>
                                            <?= e($kelas['tanggal_selesai'] ?? '-'); ?>
                                        </td>

                                        <!-- STATUS -->
                                        <td class="text-center">

                                            <?php if ($kelas['status_aktif'] == 1 || $kelas['status_aktif'] == 'aktif') { ?>

                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                                    Aktif
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">
                                                    Tidak Aktif
                                                </span>

                                            <?php } ?>

                                        </td>

                                        <!-- CREATED AT -->
                                        <td>
                                            <?= e($kelas['created_at'] ?? '-'); ?>
                                        </td>

                                        <!-- UPDATED AT -->
                                        <td>
                                            <?= e($kelas['updated_at'] ?? '-'); ?>
                                        </td>

                                        <!-- AKSI -->
                                        <td class="text-center">

                                            <div class="d-flex justify-content-center gap-2">

                                                <!-- EDIT -->
                                                <a href="edit_kelas.php?id=<?= $id; ?>"
                                                   class="btn-action-edit text-decoration-none btn-sm"
                                                   title="Edit Data">

                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <!-- HAPUS -->
                                                <a href="hapus_kelas.php?id=<?= $id; ?>"
                                                   class="btn-action-delete text-decoration-none btn-sm"
                                                   title="Hapus Data"
                                                   onclick="return confirm('Yakin ingin menghapus data kelas siswa ini?')">

                                                    <i class="fa-solid fa-trash"></i>
                                                </a>

                                            </div>

                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </main>

        </div>
    </div>

    <!-- JQUERY -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DATATABLES JS -->
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- INISIALISASI DATATABLES -->
    <script>
        $(document).ready(function() {

            $('#tabelKelas').DataTable({

                pageLength: 10,

                lengthMenu: [5, 10, 25, 50, 100],

                order: [],

                scrollX: true,

                language: {
                    search: "Cari:",
                    searchPlaceholder: "Cari data kelas siswa...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    zeroRecords: "Data tidak ditemukan",
                    emptyTable: "Belum ada data kelas siswa",

                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }

            });

        });
    </script>

</body>
</html>
