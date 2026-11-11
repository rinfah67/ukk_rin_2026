
<?php
// rekap_poin.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// ============================================
// CEK HAK AKSES
// ============================================

if (($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

// ============================================
// INPUT PENCARIAN DAN FILTER
// ============================================

$cari = trim($_GET['cari'] ?? '');
$kelas_filter = (int) ($_GET['kelas'] ?? 0);

$current_page = basename($_SERVER['PHP_SELF']);

// ============================================
// MENGAMBIL DATA KELAS
// ============================================

$data_kelas = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_kelas
    ORDER BY nama ASC
");

if (!$data_kelas) {
    die("Query kelas gagal: " . mysqli_error($koneksi));
}

// ============================================
// RINGKASAN JUMLAH SISWA
// ============================================

$hasil = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah
    FROM t_siswa
");

$jumlah_siswa = mysqli_fetch_assoc($hasil)['jumlah'] ?? 0;

// ============================================
// RINGKASAN TOTAL POIN
// ============================================

$hasil = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(poin), 0) AS total
    FROM t_pelanggaran_siswa
");

$total_poin = mysqli_fetch_assoc($hasil)['total'] ?? 0;

// ============================================
// RINGKASAN JUMLAH CATATAN
// ============================================

$hasil = mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah
    FROM t_pelanggaran_siswa
");

$jumlah_catatan = mysqli_fetch_assoc($hasil)['jumlah'] ?? 0;

// ============================================
// QUERY REKAP POIN
// ============================================

$sql = "SELECT
            s.id,
            s.nis,
            s.nama,
            k.nama AS nama_kelas,
            COUNT(p.id) AS jumlah_pelanggaran,
            COALESCE(SUM(p.poin), 0) AS total_poin
        FROM t_siswa s

        LEFT JOIN t_kelas_siswa ks
            ON ks.siswa_id = s.id
            AND ks.status_aktif = 1

        LEFT JOIN t_kelas k
            ON k.id = ks.kelas_id

        LEFT JOIN t_pelanggaran_siswa p
            ON p.siswa_id = s.id

        WHERE (s.nama LIKE ? OR s.nis LIKE ?)
        AND (? = 0 OR k.id = ?)

        GROUP BY s.id, s.nis, s.nama, k.nama

        ORDER BY total_poin DESC, s.nama ASC";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query rekap gagal: " . mysqli_error($koneksi));
}

$kata_cari = "%$cari%";

mysqli_stmt_bind_param(
    $stmt,
    "ssii",
    $kata_cari,
    $kata_cari,
    $kelas_filter,
    $kelas_filter
);

mysqli_stmt_execute($stmt);

$rekap = mysqli_stmt_get_result($stmt);

if (!$rekap) {
    die("Pengambilan data rekap gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rekap Poin Pelanggaran - SI PELANG</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #334155;
        }

        .main-content {
            min-width: 0;
        }

        .page-title {
            color: #1e293b;
            font-size: 25px;
            font-weight: 800;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
        }

        .content-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            padding: 22px;
        }

        .summary-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            padding: 20px;
            height: 100%;
        }

        .summary-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 21px;
        }

        .summary-label {
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
        }

        .summary-value {
            color: #1e293b;
            font-size: 25px;
            font-weight: 800;
            margin-bottom: 0;
        }

        .section-title {
            color: #1e293b;
            font-size: 16px;
            font-weight: 700;
        }

        .form-label {
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            padding: 10px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .table thead th {
            background: #eff6ff;
            color: #1e40af;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 14px 12px;
            white-space: nowrap;
        }

        .table tbody td {
            color: #475569;
            font-size: 13px;
            padding: 12px;
            vertical-align: middle;
        }

        .btn {
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
        }

        .dataTables_wrapper {
            font-size: 13px;
        }

        .dataTables_wrapper .dt-search input,
        .dataTables_wrapper .dt-length select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 10px;
        }

        @media print {
            body {
                background: #ffffff !important;
            }

            .sidebar,
            .no-print,
            .dataTables_filter,
            .dataTables_length,
            .dt-search,
            .dt-length,
            .dt-paging,
            .dt-info {
                display: none !important;
            }

            main {
                width: 100% !important;
                padding: 0 !important;
            }

            .content-card,
            .summary-card {
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
        <main class="col px-md-4 py-4 main-content">

            <!-- JUDUL HALAMAN -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                <div>
                    <h4 class="page-title mb-1">
                        Rekap Poin Pelanggaran
                    </h4>

                    <p class="page-subtitle mb-0">
                        Ringkasan jumlah pelanggaran dan poin setiap siswa.
                    </p>
                </div>

                <button onclick="window.print()" class="btn btn-success no-print">
                    <i class="bi bi-printer me-1"></i>
                    Cetak Rekap
                </button>

            </div>

            <!-- RINGKASAN -->
            <div class="row g-3 mb-4">

                <!-- JUMLAH SISWA -->
                <div class="col-12 col-md-4">

                    <div class="summary-card">
                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <p class="summary-label mb-2">
                                    Jumlah Siswa
                                </p>

                                <h3 class="summary-value">
                                    <?= number_format((int) $jumlah_siswa); ?>
                                </h3>
                            </div>

                            <div class="summary-icon bg-primary-subtle text-primary">
                                <i class="bi bi-people-fill"></i>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- TOTAL POIN -->
                <div class="col-12 col-md-4">

                    <div class="summary-card">
                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <p class="summary-label mb-2">
                                    Total Poin
                                </p>

                                <h3 class="summary-value">
                                    <?= number_format((int) $total_poin); ?>
                                </h3>
                            </div>

                            <div class="summary-icon bg-warning-subtle text-warning">
                                <i class="bi bi-star-fill"></i>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- JUMLAH CATATAN -->
                <div class="col-12 col-md-4">

                    <div class="summary-card">
                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <p class="summary-label mb-2">
                                    Jumlah Catatan Pelanggaran
                                </p>

                                <h3 class="summary-value">
                                    <?= number_format((int) $jumlah_catatan); ?>
                                </h3>
                            </div>

                            <div class="summary-icon bg-danger-subtle text-danger">
                                <i class="bi bi-clipboard-data-fill"></i>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <!-- FILTER DATA -->
            <div class="content-card mb-4 no-print">

                <div class="mb-3">
                    <h6 class="section-title mb-1">
                        <i class="bi bi-funnel me-1"></i>
                        Pencarian dan Filter
                    </h6>

                    <p class="text-secondary small mb-0">
                        Cari siswa berdasarkan nama, NIS, atau kelas.
                    </p>
                </div>

                <form method="GET" action="rekap_poin.php">

                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-lg-5">

                            <label for="cari" class="form-label">
                                Cari Siswa
                            </label>

                            <input
                                type="text"
                                name="cari"
                                id="cari"
                                class="form-control"
                                placeholder="Masukkan nama atau NIS siswa"
                                value="<?= htmlspecialchars($cari); ?>"
                            >

                        </div>

                        <div class="col-12 col-lg-4">

                            <label for="kelas" class="form-label">
                                Filter Kelas
                            </label>

                            <select name="kelas" id="kelas" class="form-select">

                                <option value="0">Semua Kelas</option>

                                <?php while ($kelas = mysqli_fetch_assoc($data_kelas)): ?>

                                    <option
                                        value="<?= (int) $kelas['id']; ?>"
                                        <?= $kelas_filter == $kelas['id'] ? 'selected' : ''; ?>
                                    >
                                        <?= htmlspecialchars($kelas['nama']); ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="col-12 col-lg-3">

                            <div class="d-flex gap-2">

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    Cari
                                </button>

                                <a href="rekap_poin.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Reset
                                </a>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

            <!-- TABEL REKAP -->
            <div class="content-card">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                    <div>
                        <h6 class="section-title mb-1">
                            <i class="bi bi-table me-1"></i>
                            Data Rekap Poin Siswa
                        </h6>

                        <p class="text-secondary small mb-0">
                            Daftar jumlah pelanggaran dan total poin siswa.
                        </p>
                    </div>

                </div>

                <div class="table-responsive">

                    <table id="tabelRekap" class="table table-bordered table-hover align-middle w-100">

                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th class="text-center">Jumlah Pelanggaran</th>
                                <th class="text-center">Total Poin</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            while ($row = mysqli_fetch_assoc($rekap)):
                            ?>

                                <tr>

                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= htmlspecialchars($row['nis'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                    </td>

                                    <td class="text-center">
                                        <?= (int) $row['jumlah_pelanggaran']; ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">
                                            <?= number_format((int) $row['total_poin']); ?>
                                            poin
                                        </span>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelRekap').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        order: [],

        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data rekap",

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
