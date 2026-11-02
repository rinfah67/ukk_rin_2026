
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Cek hak akses admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die("Akses ditolak!");
}

// Ambil data siswa
$sql = "SELECT * FROM t_siswa ORDER BY id DESC";
$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Siswa - Si Pelang</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">
</head>

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">


            <!-- Header halaman -->
            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold text-primary mb-1">
                        <i class="bi bi-people-fill me-2"></i>
                        Kelola Siswa
                    </h2>

                    <p class="text-secondary mb-0">
                        Kelola data siswa yang terdaftar di Si Pelang.
                    </p>
                </div>

                <a href="tambah_siswa.php" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i>
                    Tambah Siswa
                </a>
            </div>

            <!-- Tombol kembali -->
            <div class="mb-3">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali ke Dashboard
                </a>
            </div>

            <!-- Notifikasi jika tersedia di session -->
            <?php if (isset($_SESSION['pesan_sukses'])) { ?>
                <div class="alert alert-success alert-dismissible fade show"
                     role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['pesan_sukses']) ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php } ?>

            <?php if (isset($_SESSION['pesan_error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show"
                     role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php } ?>

            <!-- Card tabel siswa -->
            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-table text-primary me-2"></i>
                        Data Siswa
                    </h5>

                    <p class="text-secondary small mb-0">
                        Cari, urutkan, dan kelola data siswa.
                    </p>
                </div>

                <div class="card-body pt-0">

                    <div class="table-responsive">
                        <table id="tabelSiswa"
                               class="table table-striped table-bordered
                                      table-hover align-middle w-100">

                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;

                                while ($siswa = mysqli_fetch_assoc($hasil)) {
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>

                                        <td>
                                            <?= htmlspecialchars($siswa['nis'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($siswa['nisn'] ?? '-') ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($siswa['nama'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?php
                                            if (($siswa['jenis_kelamin'] ?? '') === 'L') {
                                                echo 'Laki-laki';
                                            } elseif (($siswa['jenis_kelamin'] ?? '') === 'P') {
                                                echo 'Perempuan';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($siswa['tanggal_lahir'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($siswa['alamat'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?php if (($siswa['status_aktif'] ?? 0) == 1) { ?>
                                                <span class="badge bg-success">
                                                    Aktif
                                                </span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">
                                                    Tidak Aktif
                                                </span>
                                            <?php } ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($siswa['created_at'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($siswa['updated_at'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit_siswa.php?id=<?= (int)$siswa['id'] ?>"
                                                   class="btn btn-warning btn-sm"
                                                   title="Edit siswa">
                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit
                                                </a>

                                                <a href="hapus_siswa.php?id=<?= (int)$siswa['id'] ?>"
                                                   class="btn btn-danger btn-sm"
                                                   title="Hapus siswa"
                                                   onclick="return confirm('Yakin ingin menghapus siswa ini?')">
                                                    <i class="bi bi-trash"></i>
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

            <!-- Footer -->
            <div class="text-center text-secondary small mt-4">
                Si Pelang &copy; <?= date('Y') ?>
            </div>

        </div>
    </main>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelSiswa').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        scrollX: true,
        language: {
            search: "Cari:",
            searchPlaceholder: "Cari nama atau NIS siswa...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data siswa tidak ditemukan",
            emptyTable: "Belum ada data siswa",
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
