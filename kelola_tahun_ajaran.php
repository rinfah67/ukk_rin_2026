
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// =====================================
// AMBIL DATA TAHUN AJARAN
// =====================================
$query_ta = mysqli_query(
    $koneksi,
    "SELECT id, nama, tanggal_mulai, tanggal_selesai, status_aktif
     FROM t_tahun_ajaran
     ORDER BY id DESC"
);

if (!$query_ta) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}

// =====================================
// FUNGSI FORMAT TANGGAL
// =====================================
function formatTanggal($tanggal)
{
    if (
        empty($tanggal) ||
        $tanggal == '0000-00-00'
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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Tahun Ajaran - Si Pelang</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #2563eb;
        }

        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .table-custom-header th {
            background-color: #eff6ff !important;
            color: #1e40af !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.78rem;
            border-bottom: 2px solid #dbeafe !important;
            padding: 14px 12px;
        }

        .btn-action-edit {
            background-color: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
        }

        .btn-action-edit:hover {
            background-color: #d97706;
            color: #ffffff;
        }

        .btn-action-delete {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
        }

        .btn-action-delete:hover {
            background-color: #dc2626;
            color: #ffffff;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 7px 12px;
            margin-left: 8px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 5px 10px;
        }

        .table-responsive {
            width: 100%;
        }
    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- HEADER HALAMAN -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-calendar-days text-primary"></i>
                        Kelola Tahun Ajaran
                    </h2>

                    <p class="text-muted mb-0">
                        Kelola data periode tahun ajaran aktif.
                    </p>
                </div>

                <div class="d-flex gap-2">

                    <a href="dashboard.php"
                       class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                       style="border-radius: 10px;">

                        <i class="fa-solid fa-arrow-left me-1"></i>
                        Dashboard

                    </a>

                    <a href="tambah_tahun_ajaran.php"
                       class="btn btn-primary fw-medium px-4 shadow-sm"
                       style="border-radius: 10px; background-color: #2563eb;">

                        <i class="fa-solid fa-plus me-1"></i>
                        Tambah Tahun Ajaran

                    </a>

                </div>

            </div>

            <!-- TABEL DATA -->
            <div class="card card-custom p-4">

                <div class="table-responsive">

                    <table id="tabelTA"
                           class="table table-hover align-middle w-100">

                        <thead class="table-custom-header">

                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    No
                                </th>

                                <th>
                                    Tahun Ajaran
                                </th>

                                <th>
                                    Tanggal Mulai
                                </th>

                                <th>
                                    Tanggal Selesai
                                </th>

                                <th class="text-center">
                                    Status
                                </th>

                                <th class="text-center" style="width: 140px;">
                                    Aksi
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            while ($data = mysqli_fetch_assoc($query_ta)) :

                                $id = (int) $data['id'];

                                $nama = $data['nama'];

                                $tanggal_mulai = formatTanggal(
                                    $data['tanggal_mulai']
                                );

                                $tanggal_selesai = formatTanggal(
                                    $data['tanggal_selesai']
                                );

                                $status = (int) $data['status_aktif'];
                            ?>

                            <tr>

                                <!-- NOMOR -->
                                <td class="text-center text-muted fw-medium">
                                    <?= $no++; ?>
                                </td>

                                <!-- TAHUN AJARAN -->
                                <td class="fw-semibold text-dark">
                                    <?= e($nama); ?>
                                </td>

                                <!-- TANGGAL MULAI -->
                                <td>
                                    <?= e($tanggal_mulai); ?>
                                </td>

                                <!-- TANGGAL SELESAI -->
                                <td>
                                    <?= e($tanggal_selesai); ?>
                                </td>

                                <!-- STATUS -->
                                <td class="text-center">

                                    <?php if ($status === 1) : ?>

                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                            Aktif
                                        </span>

                                    <?php else : ?>

                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">
                                            Tidak Aktif
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- AKSI -->
                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="edit_tahun_ajaran.php?id=<?= $id; ?>"
                                           class="btn-action-edit text-decoration-none btn-sm"
                                           title="Edit Data">

                                            <i class="fa-solid fa-pen-to-square"></i>

                                        </a>

                                        <a href="hapus_tahun_ajaran.php?id=<?= $id; ?>"
                                           class="btn-action-delete text-decoration-none btn-sm"
                                           title="Hapus Data"
                                           onclick="return confirm('Yakin ingin menghapus data tahun ajaran ini?')">

                                            <i class="fa-solid fa-trash"></i>

                                        </a>

                                    </div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>

$(document).ready(function() {

    $('#tabelTA').DataTable({

        language: {

            emptyTable: "Belum ada data Tahun Ajaran di database.",

            zeroRecords: "Data tahun ajaran tidak ditemukan.",

            search: "Cari:",

            lengthMenu: "Tampilkan _MENU_ data",

            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",

            infoEmpty: "Tidak ada data yang ditampilkan",

            infoFiltered: "(difilter dari _MAX_ total data)",

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
