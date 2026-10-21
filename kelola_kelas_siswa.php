
<?php
session_start();
include 'config/koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// CEK ROLE ADMIN
if (($_SESSION['role'] ?? '') != 'admin') {
    echo "Akses ditolak!";
    exit;
}

// AMBIL DATA KELAS SISWA
$sql = "SELECT *
        FROM t_kelas_siswa
        ORDER BY id DESC";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data kelas siswa: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Kelas Siswa</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <?php include "layout/sidebar.php"; ?>

    <main class="row">
        <div class="col-9 offset-3 col-lg-10 offset-lg-2 p-4">

        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Kelola Kelas Siswa</h1>

                <a href="tambah_kelas.php" class="btn btn-primary">
                    + Tambah Kelas
                </a>
            </div>

            <a href="dashboard.php" class="btn btn-secondary btn-sm mb-3">
                Kembali ke Dashboard
            </a>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Data Kelas Siswa</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">

                        <table id="tabelKelas"
                               class="table table-striped table-bordered table-hover align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Siswa ID</th>
                                    <th>Tahun Ajaran ID</th>
                                    <th>Kelas ID</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;

                                while ($kelas = mysqli_fetch_assoc($hasil)) {
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= htmlspecialchars((string) $kelas['siswa_id']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars((string) $kelas['tahun_ajaran_id']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars((string) $kelas['kelas_id']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($kelas['tanggal_mulai'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($kelas['tanggal_selesai'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?php if ($kelas['status_aktif'] == 1) { ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($kelas['created_at'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($kelas['updated_at'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit_kelas.php?id=<?= (int) $kelas['id']; ?>"
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                <a href="hapus_kelas.php?id=<?= (int) $kelas['id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus data kelas siswa ini?')">
                                                    Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
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

<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelKelas').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
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
