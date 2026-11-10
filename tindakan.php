<?php
// tindakan.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Variabel pesan error
$pesan_error = '';

// Proses menyimpan tindakan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil data dari form
    $id = (int) ($_POST['id'] ?? 0);
    $tindakan = trim($_POST['tindakan'] ?? '');

    // Memeriksa data
    if ($id <= 0) {
        $pesan_error = 'ID pelanggaran tidak valid.';
    } elseif ($tindakan === '') {
        $pesan_error = 'Tindakan tidak boleh kosong.';
    } else {

        // Memperbarui tindakan
        $sql_update = "UPDATE t_pelanggaran_siswa
                       SET tindakan = ?
                       WHERE id = ?";

        $stmt = mysqli_prepare($koneksi, $sql_update);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "si", $tindakan, $id);

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header('Location: tindakan.php?pesan=berhasil');
                exit;

            } else {

                $pesan_error = mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);

            }

        } else {

            $pesan_error = mysqli_error($koneksi);

        }
    }
}

// Mengambil data pelanggaran siswa
$sql = "SELECT
            id,
            nama_siswa,
            nama_kelas,
            nama_pelanggaran,
            tanggal,
            keterangan,
            tindakan
        FROM t_pelanggaran_siswa
        ORDER BY tanggal DESC, id DESC";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die('Gagal mengambil data: ' . htmlspecialchars(mysqli_error($koneksi)));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tindakan Pelanggaran - SI PELANG</title>

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
        }

        .btn-action-save:hover {
            background-color: #bfdbfe;
            color: #1e40af;
        }

        /* Textarea */
        .form-control {
            font-size: 13px;
            border-radius: 7px;
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
                        <i class="fas fa-clipboard-check text-primary me-2"></i>
                        Tindakan Pelanggaran
                    </h3>

                    <p class="text-secondary mb-0">
                        Kelola tindakan untuk setiap pelanggaran siswa.
                    </p>
                </div>

            </div>

            <!-- Notifikasi Berhasil -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil') : ?>

                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    Tindakan berhasil disimpan.

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php endif; ?>

            <!-- Notifikasi Error -->
            <?php if ($pesan_error !== '') : ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Gagal menyimpan tindakan:
                    <?= htmlspecialchars($pesan_error); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php endif; ?>

            <!-- Card Data Tindakan -->
            <div class="card card-custom p-4">

                <div class="mb-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-table text-primary me-2"></i>
                        Data Tindakan Pelanggaran Siswa
                    </h5>

                    <p class="text-secondary small mb-0">
                        Lihat catatan pelanggaran dan masukkan tindakan yang diberikan.
                    </p>
                </div>

                <div class="table-responsive">

                    <table id="tabelTindakan" class="table table-bordered table-hover align-middle w-100">

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

                            if (mysqli_num_rows($hasil) > 0) :
                                while ($row = mysqli_fetch_assoc($hasil)) :
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

                                <td>
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

                                <td>
                                    <form method="POST" action="tindakan.php">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $row['id']; ?>">

                                        <textarea
                                            name="tindakan"
                                            class="form-control"
                                            rows="2"
                                            placeholder="Masukkan tindakan..."
                                            required><?= htmlspecialchars($row['tindakan'] ?? ''); ?></textarea>
                                </td>

                                <td class="text-center">

                                        <button
                                            type="submit"
                                            class="btn btn-action-save">
                                            <i class="fas fa-save me-1"></i>
                                            Simpan
                                        </button>

                                    </form>

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
        $('#tabelTindakan').DataTable({
            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            language: {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data tindakan tidak ditemukan",
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
