
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Mengambil data tahun ajaran
$query = mysqli_query($koneksi, "
    SELECT * FROM t_tahun_ajaran
    ORDER BY id DESC
");

if (!$query) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Tahun Ajaran - SI PELANG</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <!-- Judul halaman dan tombol tambah -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Data Tahun Ajaran</h3>
                    <p class="text-secondary mb-0">Kelola data tahun ajaran sekolah.</p>
                </div>

                <a href="tambah_tahun_ajaran.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Tahun Ajaran
                </a>
            </div>

            <!-- Notifikasi sukses -->
            <?php if (!empty($_SESSION['pesan_sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>

            <!-- Notifikasi error -->
            <?php if (!empty($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>

            <!-- Tabel tahun ajaran -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-calendar3 me-2"></i>Daftar Tahun Ajaran
                    </h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabelTahunAjaran" class="table table-striped table-hover align-middle w-100">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $no = 1; ?>

                                <?php while ($data = mysqli_fetch_assoc($query)): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>

                                        <td>
                                            <?= htmlspecialchars($data['nama'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= !empty($data['tanggal_mulai'])
                                                ? date('d-m-Y', strtotime($data['tanggal_mulai']))
                                                : '-' ?>
                                        </td>

                                        <td>
                                            <?= !empty($data['tanggal_selesai'])
                                                ? date('d-m-Y', strtotime($data['tanggal_selesai']))
                                                : '-' ?>
                                        </td>

                                        <td>
                                            <?php if ((int)$data['status_aktif'] === 1): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit_tahun_ajaran.php?id=<?= (int)$data['id'] ?>"
                                                   class="btn btn-warning btn-sm"
                                                   title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <a href="hapus_tahun_ajaran.php?id=<?= (int)$data['id'] ?>"
                                                   class="btn btn-danger btn-sm"
                                                   title="Hapus"
                                                   onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>

<!-- Konfigurasi DataTables -->
<script>
$(document).ready(function () {
    new DataTable('#tabelTahunAjaran', {
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        scrollX: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.3.4/i18n/id.json'
        }
    });
});
</script>

</body>
</html>
