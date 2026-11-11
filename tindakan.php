
<?php
// tindakan.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// ============================================
// CEK HAK AKSES
// ============================================

if (!in_array($_SESSION['role'] ?? '', ['admin', 'guru'])) {
    header("Location: dashboard.php");
    exit;
}

// ============================================
// VARIABEL PESAN ERROR
// ============================================

$pesan_error = '';

// ============================================
// PROSES MENYIMPAN TINDAKAN
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int) ($_POST['id'] ?? 0);
    $tindakan = trim($_POST['tindakan'] ?? '');

    if ($id <= 0) {

        $pesan_error = 'ID pelanggaran tidak valid.';

    } elseif ($tindakan === '') {

        $pesan_error = 'Tindakan tidak boleh kosong.';

    } else {

        // Update hanya jika tindakan sebelumnya masih kosong
        $sql_update = "
            UPDATE t_pelanggaran_siswa
            SET tindakan = ?
            WHERE id = ?
            AND (tindakan IS NULL OR TRIM(tindakan) = '')
        ";

        $stmt = mysqli_prepare($koneksi, $sql_update);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "si", $tindakan, $id);

            if (mysqli_stmt_execute($stmt)) {

                if (mysqli_stmt_affected_rows($stmt) > 0) {

                    mysqli_stmt_close($stmt);

                    header('Location: tindakan.php?pesan=berhasil');
                    exit;

                } else {

                    $pesan_error = 'Data sudah memiliki tindakan atau tidak ditemukan.';
                    mysqli_stmt_close($stmt);

                }

            } else {

                $pesan_error = mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);

            }

        } else {

            $pesan_error = mysqli_error($koneksi);

        }
    }
}

// ============================================
// MENGAMBIL DATA YANG BELUM ADA TINDAKAN
// ============================================

$sql = "
    SELECT
        id,
        nama_siswa,
        nama_kelas,
        nama_pelanggaran,
        tanggal,
        keterangan,
        tindakan
    FROM t_pelanggaran_siswa
    WHERE tindakan IS NULL
       OR TRIM(tindakan) = ''
    ORDER BY tanggal DESC, id DESC
";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die('Gagal mengambil data: ' . htmlspecialchars(mysqli_error($koneksi)));
}

// ============================================
// MENENTUKAN HALAMAN AKTIF
// ============================================

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tindakan Pelanggaran - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Sidebar */
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
            background: #2563eb;
            color: #ffffff;
        }

        .sidebar .nav-link.text-danger:hover {
            background: #dc2626;
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

        main {
            min-width: 0;
        }

        /* Judul Halaman */
        .page-title {
            color: #1e293b;
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
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
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

        #tabelTindakan td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Tombol Simpan */
        .btn-action-save {
            background-color: #dbeafe;
            color: #1d4ed8;
            border: none;
            border-radius: 7px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            transition: 0.2s;
        }

        .btn-action-save:hover {
            background-color: #bfdbfe;
            color: #1e40af;
        }

        /* Textarea */
        .form-control {
            font-size: 13px;
            border-radius: 8px;
            border-color: #cbd5e1;
        }

        .form-control:focus {
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
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten Utama -->
        <main class="col px-md-4 py-4">

            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="bi bi-clipboard-check text-primary me-2"></i>
                        Tindakan Pelanggaran
                    </h3>

                    <p class="page-subtitle mb-0">
                        Kelola tindakan untuk setiap pelanggaran siswa.
                    </p>
                </div>

            </div>

            <!-- Notifikasi Berhasil -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil') : ?>

                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Tindakan berhasil disimpan. Data sudah dipindahkan dari daftar tindakan.

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php endif; ?>

            <!-- Notifikasi Error -->
            <?php if ($pesan_error !== '') : ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?= htmlspecialchars($pesan_error); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php endif; ?>

            <!-- Ringkasan Jumlah Data -->
            <div class="card card-custom p-4 mb-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-clipboard-data text-primary fs-2"></i>
                    </div>

                    <div>
                        <p class="text-secondary small mb-1">
                            Pelanggaran Belum Diberi Tindakan
                        </p>

                        <div class="summary-number">
                            <?= mysqli_num_rows($hasil); ?>

                            <span class="fs-6 fw-normal text-secondary">
                                data
                            </span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Card Data Tindakan -->
            <div class="card-custom p-4">

                <div class="mb-3">

                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-table text-primary me-2"></i>
                        Data Tindakan Pelanggaran Siswa
                    </h5>

                    <p class="text-secondary small mb-0">
                        Hanya menampilkan pelanggaran yang belum memiliki tindakan.
                    </p>

                </div>

                <!-- Tabel -->
                <div class="table-responsive">

                    <table
                        id="tabelTindakan"
                        class="table table-bordered table-hover align-middle w-100">

                        <thead class="table-custom-header text-center">

                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Pelanggaran</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Tindakan</th>
                                <th>Aksi</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            while ($row = mysqli_fetch_assoc($hasil)) :

                                $id_form = 'form-tindakan-' . (int) $row['id'];
                            ?>

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
                                        echo htmlspecialchars(
                                            date('d-m-Y', strtotime($row['tanggal']))
                                        );
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['keterangan'] ?? '-'); ?>
                                </td>

                                <!-- Input Tindakan -->
                                <td>

                                    <textarea
                                        form="<?= $id_form; ?>"
                                        name="tindakan"
                                        class="form-control"
                                        rows="2"
                                        placeholder="Masukkan tindakan..."
                                        required></textarea>

                                </td>

                                <!-- Tombol Aksi -->
                                <td class="text-center">

                                    <form
                                        id="<?= $id_form; ?>"
                                        method="POST"
                                        action="tindakan.php">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $row['id']; ?>">

                                        <button
                                            type="submit"
                                            class="btn-action-save">

                                            <i class="bi bi-floppy me-1"></i>
                                            Simpan

                                        </button>

                                    </form>

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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {

        // Mengaktifkan DataTables
        $('#tabelTindakan').DataTable({

            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            order: [],

            language: {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data tindakan tidak ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data tersedia",
                infoFiltered: "(difilter dari _MAX_ total data)",
                search: "Cari:",
                emptyTable: "Tidak ada pelanggaran yang perlu ditindak",
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
