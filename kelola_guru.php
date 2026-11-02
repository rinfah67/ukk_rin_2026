
<?php
session_start();

include 'config/koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Halaman ini hanya boleh dibuka admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Mengambil data guru dan menghubungkannya dengan akun pengguna
$query = mysqli_query($koneksi, "
    SELECT
        g.id,
        g.nip,
        g.nama,
        g.email,
        g.status_aktif,
        g.user_id,
        g.created_at,
        g.updated_at,
        u.name AS nama_akun,
        u.email AS email_akun,
        u.role AS role_akun
    FROM t_guru g
    LEFT JOIN t_users u ON g.user_id = u.id
    ORDER BY g.id DESC
");

if (!$query) {
    $pesan_query = mysqli_error($koneksi);
} else {
    $pesan_query = '';
}

// Mengambil notifikasi dari proses tambah, edit, atau hapus
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

    <title>Kelola Guru | SI PELANG</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
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

        .table thead th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
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
                <i class="bi bi-person-badge"></i>
            </div>

            <div>
                <h3 class="fw-bold mb-1">Kelola Guru</h3>
                <p class="text-secondary mb-0">
                    Kelola data guru dan akun pengguna SI PELANG.
                </p>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-house-door me-1"></i>
                Dashboard
            </a>

            <a href="tambah_guru.php" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>
                Tambah Guru
            </a>
        </div>
    </div>

    <!-- Notifikasi -->
    <?php if ($pesan_sukses != ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars($pesan_sukses); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($pesan_error != ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($pesan_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($pesan_query != ''): ?>
        <div class="alert alert-danger">
            <strong>Query database gagal:</strong>
            <?= htmlspecialchars($pesan_query); ?>
        </div>
    <?php endif; ?>

    <!-- Tabel data guru -->
    <div class="card content-card">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-1">
                <i class="bi bi-table text-primary me-2"></i>
                Daftar Data Guru
            </h5>
            <p class="text-secondary small mb-0">
                Gunakan pencarian untuk menemukan data guru.
            </p>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="tabelGuru"
                       class="table table-striped table-hover table-bordered align-middle w-100">

                    <thead class="table-primary">
                        <tr>
                            <th>No.</th>
                            <th>NIP</th>
                            <th>Nama Guru</th>
                            <th>Email Guru</th>
                            <th>Akun Pengguna</th>
                            <th>Role Akun</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($query && mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1; ?>

                        <?php while ($guru = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars($guru['nip'] ?? '-'); ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= htmlspecialchars($guru['nama'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guru['email'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php if (!empty($guru['user_id'])): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-link-45deg me-1"></i>
                                            Terhubung
                                        </span>
                                        <div class="small text-secondary mt-1">
                                            <?= htmlspecialchars($guru['nama_akun'] ?? '-'); ?>
                                        </div>
                                        <div class="small text-secondary">
                                            <?= htmlspecialchars($guru['email_akun'] ?? '-'); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">
                                            Belum terhubung
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guru['role_akun'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php if ((int) $guru['status_aktif'] === 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guru['created_at'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guru['updated_at'] ?? '-'); ?>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_guru.php?id=<?= (int) $guru['id']; ?>"
                                           class="btn btn-warning btn-sm"
                                           title="Edit guru">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a href="hapus_guru.php?id=<?= (int) $guru['id']; ?>"
                                           class="btn btn-danger btn-sm"
                                           title="Hapus guru"
                                           onclick="return confirm('Yakin ingin menghapus data guru ini?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php elseif ($query): ?>
                        <tr>
                            <td colspan="10" class="text-center text-secondary py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Belum ada data guru.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
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
        $('#tabelGuru').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [],
            scrollX: true,
            language: {
                search: 'Cari:',
                searchPlaceholder: 'Cari data guru...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data untuk ditampilkan',
                infoFiltered: '(disaring dari _MAX_ total data)',
                zeroRecords: 'Data yang dicari tidak ditemukan',
                emptyTable: 'Belum ada data guru',
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
