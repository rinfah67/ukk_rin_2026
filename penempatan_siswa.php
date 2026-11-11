
<?php
// penempatan_siswa.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Cek hak akses
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Token keamanan
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mengambil data penempatan siswa
$query_penempatan = mysqli_query($koneksi, "
    SELECT
        ks.*,
        s.nis,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        ta.nama AS nama_tahun_ajaran
    FROM t_kelas_siswa ks
    INNER JOIN t_siswa s ON ks.siswa_id = s.id
    INNER JOIN t_kelas k ON ks.kelas_id = k.id
    INNER JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    ORDER BY ks.id DESC
");

if (!$query_penempatan) {
    die("Query gagal: " . mysqli_error($koneksi));
}

// Mengambil tahun ajaran aktif
$query_tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY tanggal_mulai DESC
");

if (!$query_tahun) {
    die("Query tahun ajaran gagal: " . mysqli_error($koneksi));
}

// Mengambil siswa yang belum pernah ditempatkan
$query_siswa = mysqli_query($koneksi, "
    SELECT s.id, s.nis, s.nama
    FROM t_siswa s
    WHERE s.status_aktif = 1
    AND NOT EXISTS (
        SELECT 1
        FROM t_kelas_siswa ks
        WHERE ks.siswa_id = s.id
    )
    ORDER BY s.nama ASC
");

if (!$query_siswa) {
    die("Query siswa gagal: " . mysqli_error($koneksi));
}

// Mengambil kelas aktif
$query_kelas = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

if (!$query_kelas) {
    die("Query kelas gagal: " . mysqli_error($koneksi));
}

// Menghitung jumlah data
$total_penempatan = mysqli_num_rows($query_penempatan);
$total_siswa_belum_ditempatkan = mysqli_num_rows($query_siswa);
$total_tahun_aktif = mysqli_num_rows($query_tahun);

// Notifikasi
$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

// Fungsi escape HTML
function e($data)
{
    return htmlspecialchars((string) $data, ENT_QUOTES, 'UTF-8');
}

// Fungsi format tanggal
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

// Halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Penempatan Siswa - SI PELANG</title>

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

        /* Judul Halaman */
        .page-title {
            font-weight: 800;
            color: #1e293b;
        }

        .page-description {
            color: #64748b;
            font-size: 14px;
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

        #tabelPenempatan td {
            padding: 12px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Form */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            width: 100%;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Tombol */
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

        /* Statistik */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
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

        /* Responsif */
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

            .card-custom {
                padding: 15px !important;
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

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            #tabelPenempatan {
                min-width: 850px;
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

        <?php
        // Menampilkan sidebar
        $current_page = basename($_SERVER['PHP_SELF']);
        include 'layout/sidebar.php';
        ?>

        <!-- Konten Utama -->
        <main class="col px-md-4 py-4">

            <!-- Header Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 page-header">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="fas fa-user-graduate text-primary me-2"></i>
                        Penempatan Siswa
                    </h3>

                    <p class="page-description mb-0">
                        Kelola penempatan siswa ke dalam kelas dan tahun ajaran.
                    </p>
                </div>

            </div>

            <!-- Notifikasi -->
            <?php if (!empty($pesan_sukses)) : ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= e($pesan_sukses); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)) : ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= e($pesan_error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistik -->
            <div class="row g-3 mb-4">

                <div class="col-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-users"></i>
                            </div>

                            <div>
                                <div class="stat-number">
                                    <?= $total_penempatan; ?>
                                </div>
                                <div class="stat-label">Total Penempatan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-success bg-opacity-10 text-success">
                                <i class="fas fa-user-plus"></i>
                            </div>

                            <div>
                                <div class="stat-number">
                                    <?= $total_siswa_belum_ditempatkan; ?>
                                </div>
                                <div class="stat-label">Siswa Belum Ditempatkan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-calendar-check"></i>
                            </div>

                            <div>
                                <div class="stat-number">
                                    <?= $total_tahun_aktif; ?>
                                </div>
                                <div class="stat-label">Tahun Ajaran Aktif</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Form Tambah Penempatan -->
            <div class="card card-custom p-4 mb-4">

                <div class="mb-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-plus-circle text-primary me-2"></i>
                        Form Penempatan Siswa
                    </h5>

                    <p class="text-secondary small mb-0">
                        Masukkan data siswa untuk ditempatkan ke dalam kelas.
                    </p>
                </div>

                <?php if ($total_tahun_aktif == 0) : ?>

                    <div class="alert alert-warning mb-0">
                        Belum ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.
                    </div>

                <?php elseif ($total_siswa_belum_ditempatkan == 0) : ?>

                    <div class="alert alert-info mb-0">
                        Semua siswa aktif sudah pernah ditempatkan.
                    </div>

                <?php else : ?>

                    <form action="proses_simpan_penempatan.php" method="POST">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?= e($_SESSION['csrf_token']); ?>">

                        <div class="row g-3">

                            <!-- Tahun Ajaran -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Tahun Ajaran <span class="text-danger">*</span>
                                </label>

                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>

                                    <?php while ($tahun = mysqli_fetch_assoc($query_tahun)) : ?>
                                        <option value="<?= e($tahun['id']); ?>">
                                            <?= e($tahun['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>
                            </div>

                            <!-- Siswa -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Siswa <span class="text-danger">*</span>
                                </label>

                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>

                                    <?php while ($siswa = mysqli_fetch_assoc($query_siswa)) : ?>
                                        <option value="<?= e($siswa['id']); ?>">
                                            <?= e($siswa['nis']); ?> - <?= e($siswa['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>
                            </div>

                            <!-- Kelas -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Kelas <span class="text-danger">*</span>
                                </label>

                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($kelas = mysqli_fetch_assoc($query_kelas)) : ?>
                                        <option value="<?= e($kelas['id']); ?>">
                                            <?= e($kelas['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>

                                <input type="date"
                                       name="tanggal_mulai"
                                       class="form-control"
                                       required>
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Tanggal Selesai
                                </label>

                                <input type="date"
                                       name="tanggal_selesai"
                                       class="form-control">
                            </div>

                            <!-- Status -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">
                                    Status
                                </label>

                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" selected>Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Simpan Penempatan
                            </button>
                        </div>

                    </form>

                <?php endif; ?>

            </div>

            <!-- Card Data Penempatan -->
            <div class="card card-custom p-4">

                <div class="mb-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fas fa-table text-primary me-2"></i>
                        Data Penempatan Siswa
                    </h5>

                    <p class="text-secondary small mb-0">
                        Cari, urutkan, edit, dan hapus data penempatan siswa.
                    </p>
                </div>

                <div class="table-responsive">

                    <table id="tabelPenempatan"
                           class="table table-bordered table-hover align-middle w-100">

                        <thead class="table-custom-header text-center">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
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

                            while ($data = mysqli_fetch_assoc($query_penempatan)) :

                                $id = (int) ($data['id'] ?? 0);
                                $status = $data['status_aktif'] ?? 0;
                                $status_aktif = ($status == 1 || strtolower((string) $status) === 'aktif');
                            ?>

                                <tr>
                                    <td class="text-center">
                                        <?= $no++; ?>
                                    </td>

                                    <td>
                                        <?= e($data['nis'] ?? '-'); ?>
                                    </td>

                                    <td class="fw-semibold">
                                        <?= e($data['nama_siswa'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= e($data['nama_kelas'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= e($data['nama_tahun_ajaran'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= e(formatTanggal($data['tanggal_mulai'] ?? '')); ?>
                                    </td>

                                    <td>
                                        <?= e(formatTanggal($data['tanggal_selesai'] ?? '')); ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($status_aktif) : ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center text-nowrap">

                                        <a href="edit_penempatan_siswa.php?id=<?= $id; ?>"
                                           class="btn btn-action-edit">
                                            <i class="fas fa-pen-to-square"></i>
                                            Edit
                                        </a>

                                        <a href="hapus_penempatan_siswa.php?id=<?= $id; ?>"
                                           class="btn btn-action-delete"
                                           onclick="return confirm('Yakin ingin menghapus data penempatan siswa ini?')">
                                            <i class="fas fa-trash"></i>
                                            Hapus
                                        </a>

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

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {

        // Mengaktifkan DataTables
        $('#tabelPenempatan').DataTable({
            pageLength: 10,

            lengthMenu: [5, 10, 25, 50, 100],

            language: {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data penempatan tidak ditemukan",
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
