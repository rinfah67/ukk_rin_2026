
<?php
session_start();

include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Halaman ini hanya dapat diakses admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Mengambil data wali kelas beserta nama guru, kelas, dan tahun ajaran
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

// Menyiapkan pesan notifikasi
$error_query = $query ? '' : mysqli_error($koneksi);

$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Wali Kelas | SI PELANG</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fb;
        }

        .content-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        .page-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background-color: #cfe2ff;
            color: #0d6efd;
            font-size: 24px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table thead th {
            white-space: nowrap;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 6px;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">


    <!-- Judul halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">
                <i class="bi bi-person-workspace"></i>
            </div>

            <div>
                <h3 class="fw-bold mb-1">Data Wali Kelas</h3>
                <p class="text-secondary mb-0">
                    Kelola penugasan guru sebagai wali kelas.
                </p>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-house-door me-1"></i>
                Dashboard
            </a>

            <a href="tambah_wali_kelas.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i>
                Tambah Wali Kelas
            </a>
        </div>
    </div>

    <!-- Notifikasi sukses -->
    <?php if ($pesan_sukses !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars($pesan_sukses); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Notifikasi gagal -->
    <?php if ($pesan_error !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($pesan_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Error query -->
    <?php if ($error_query !== ''): ?>
        <div class="alert alert-danger">
            <strong>Query database gagal:</strong>
            <?= htmlspecialchars($error_query); ?>
        </div>
    <?php endif; ?>

    <!-- Tabel data wali kelas -->
    <div class="card content-card">

        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-1">
                <i class="bi bi-table text-primary me-2"></i>
                Daftar Wali Kelas
            </h5>

            <p class="text-secondary small mb-0">
                Informasi guru, kelas, tahun ajaran, dan masa penugasan.
            </p>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="tabelWaliKelas"
                       class="table table-bordered table-striped table-hover align-middle w-100">

                    <thead class="table-primary">
                        <tr>
                            <th>No.</th>
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
                    <?php if ($query && mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1; ?>

                        <?php while ($data = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($data['nip'] ?? '-'); ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= htmlspecialchars($data['nama_guru'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_kelas'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['tingkat'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['jurusan'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['tahun_ajaran'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php
                                    if (!empty($data['tanggal_mulai'])) {
                                        echo date(
                                            'd-m-Y',
                                            strtotime($data['tanggal_mulai'])
                                        );
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    if (!empty($data['tanggal_selesai'])) {
                                        echo date(
                                            'd-m-Y',
                                            strtotime($data['tanggal_selesai'])
                                        );
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?php if ((int) $data['status_aktif'] === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_wali_kelas.php?id=<?= (int) $data['id']; ?>"
                                           class="btn btn-warning btn-sm"
                                           title="Edit penugasan">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a href="hapus_wali_kelas.php?id=<?= (int) $data['id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           title="Hapus penugasan"
                                           onclick="return confirm('Yakin ingin menghapus penugasan wali kelas ini?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>

                </table>

                <?php if ($query && mysqli_num_rows($query) === 0): ?>
                    <p class="text-center text-secondary mt-3 mb-0">
                        Belum ada data wali kelas.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-footer bg-white border-0 text-secondary small px-4 pb-3">
            SI PELANG &copy; <?= date('Y'); ?>
        </div>

    </div>

</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tabelWaliKelas').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [],
            scrollX: true,

            language: {
                search: 'Cari:',
                searchPlaceholder: 'Cari wali kelas...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data untuk ditampilkan',
                infoFiltered: '(disaring dari _MAX_ total data)',
                zeroRecords: 'Data wali kelas tidak ditemukan',
                emptyTable: 'Belum ada data wali kelas',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            }
        });
    });
</script>

</body>
</html>
