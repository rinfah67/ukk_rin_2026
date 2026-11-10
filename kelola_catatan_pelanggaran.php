
<?php
// kelola_catatan_pelanggaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Mengambil data catatan pelanggaran
$query_catatan = mysqli_query($koneksi, "
    SELECT *
    FROM t_pelanggaran_siswa
    ORDER BY tanggal DESC, id DESC
");

if (!$query_catatan) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Pelanggaran - SI PELANG</title>

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

        #tabelCatatan td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Tombol Aksi */
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
                        <i class="fas fa-book-open text-primary me-2"></i>
                        Catatan Pelanggaran
                    </h3>

                    <p class="text-secondary mb-0">
                        Kelola seluruh catatan pelanggaran siswa di SI PELANG.
                    </p>
                </div>

                <a href="tambah_catatan.php" class="btn btn-primary px-3 fw-semibold">
                    <i class="fas fa-plus me-1"></i>
                    Tambah Catatan
                </a>

            </div>

            <!-- Notifikasi Error -->
            <?php if (isset($_SESSION['pesan_error'])) : ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_error']); ?>

            <?php endif; ?>

            <!-- Notifikasi Sukses -->
            <?php if (isset($_SESSION['pesan_sukses'])) : ?>

                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_sukses']); ?>

            <?php endif; ?>

            <!-- Notifikasi URL -->
            <?php if (isset($_GET['pesan'])) : ?>

                <?php if ($_GET['pesan'] == 'tambah') : ?>

                    <div class="alert alert-success alert-dismissible fade show">
                        Catatan pelanggaran berhasil ditambahkan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php elseif ($_GET['pesan'] == 'edit') : ?>

                    <div class="alert alert-success alert-dismissible fade show">
                        Catatan pelanggaran berhasil diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php elseif ($_GET['pesan'] == 'hapus') : ?>

                    <div class="alert alert-success alert-dismissible fade show">
                        Catatan pelanggaran berhasil dihapus.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                <?php endif; ?>

            <?php endif; ?>

            <!-- Card Data Catatan -->
            <div class="card card-custom p-4">

                <div class="mb-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-table text-primary me-2"></i>
                        Data Catatan Pelanggaran Siswa
                    </h5>

                    <p class="text-secondary small mb-0">
                        Cari, urutkan, edit, dan hapus catatan pelanggaran siswa.
                    </p>
                </div>

                <div class="table-responsive">

                    <table id="tabelCatatan" class="table table-bordered table-hover align-middle w-100">

                        <thead class="table-custom-header text-center">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Pelanggaran</th>
                                <th>Poin</th>
                                <th>Guru</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            if (mysqli_num_rows($query_catatan) > 0) :
                                while ($data = mysqli_fetch_assoc($query_catatan)) :

                                    $id_val = $data['id'] ?? 0;
                                    $status = $data['status'] ?? '';
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
                                    <span class="badge bg-primary">
                                        <?= htmlspecialchars($data['poin'] ?? '0'); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_guru'] ?? '-'); ?>
                                </td>

                                <td class="text-center">
                                    <?php if ($status == 'Belum diproses') : ?>

                                        <span class="badge bg-warning text-dark">
                                            Belum diproses
                                        </span>

                                    <?php elseif ($status == 'Diproses') : ?>

                                        <span class="badge bg-info text-dark">
                                            Diproses
                                        </span>

                                    <?php elseif ($status == 'Selesai') : ?>

                                        <span class="badge bg-success">
                                            Selesai
                                        </span>

                                    <?php else : ?>

                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars($status ?: '-'); ?>
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td class="text-center text-nowrap">

                                    <a href="edit_catatan.php?id=<?= (int) $id_val; ?>"
                                       class="btn btn-action-edit">
                                        <i class="fas fa-pen-to-square"></i>
                                        Edit
                                    </a>

                                    <a href="hapus_catatan.php?id=<?= (int) $id_val; ?>"
                                       class="btn btn-action-delete"
                                       onclick="return confirm('Yakin ingin menghapus catatan pelanggaran ini?')">
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
        $('#tabelCatatan').DataTable({
            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            language: {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data catatan pelanggaran tidak ditemukan",
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
