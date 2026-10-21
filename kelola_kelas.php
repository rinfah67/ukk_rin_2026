
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek role admin
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak!");
}

// Ambil data kelas siswa beserta nama siswa, kelas, dan tahun ajaran
$sql = "SELECT
            ks.*,
            s.nama AS nama_siswa,
            k.nama AS nama_kelas,
            k.tingkat,
            k.jurusan,
            ta.nama AS tahun_ajaran
        FROM t_kelas_siswa ks
        LEFT JOIN t_siswa s ON ks.siswa_id = s.id
        LEFT JOIN t_kelas k ON ks.kelas_id = k.id
        LEFT JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
        ORDER BY ks.id DESC";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Kelola Kelas Siswa</h1>

                <a href="tambah_kelas.php" class="btn btn-primary">
                    + Tambah Kelas Siswa
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

                                while ($data = mysqli_fetch_assoc($hasil)) {
                                    $namaKelas = ($data['nama_kelas'] ?? '-')
                                        . ' - '
                                        . ($data['tingkat'] ?? '-')
                                        . ' '
                                        . ($data['jurusan'] ?? '');
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_siswa'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($namaKelas); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tahun_ajaran'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tanggal_mulai'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tanggal_selesai'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?php if ($data['status_aktif'] == 1) { ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit_kelas.php?id=<?= (int) $data['id']; ?>"
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                <a href="hapus_kelas.php?id=<?= (int) $data['id']; ?>"
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
