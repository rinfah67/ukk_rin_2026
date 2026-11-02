
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Memeriksa hak akses
$role = $_SESSION['role'] ?? '';

if ($role != 'admin' && $role != 'guru') {
    die("Akses ditolak.");
}

// Mengambil data catatan pelanggaran
$query = mysqli_query($koneksi, "
    SELECT *
    FROM t_pelanggaran_siswa
    ORDER BY tanggal DESC, id DESC
");

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catatan Pelanggaran - SI PELANG</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <!-- Judul halaman -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                <div>
                    <h3 class="fw-bold mb-1">
                        <i class="bi bi-journal-text me-2"></i>
                        Catatan Pelanggaran
                    </h3>

                    <p class="text-secondary mb-0">
                        Daftar seluruh catatan pelanggaran siswa.
                    </p>
                </div>

                <a href="tambah_catatan.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Catatan
                </a>

            </div>

            <!-- Notifikasi error -->
            <?php if (isset($_SESSION['pesan_error'])): ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_error']); ?>

            <?php endif; ?>

            <!-- Notifikasi sukses -->
            <?php if (isset($_SESSION['pesan_sukses'])): ?>

                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']); ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_sukses']); ?>

            <?php endif; ?>

            <!-- Notifikasi berdasarkan URL -->
            <?php if (isset($_GET['pesan'])): ?>

                <?php if ($_GET['pesan'] == 'tambah'): ?>

                    <div class="alert alert-success alert-dismissible fade show">
                        Catatan pelanggaran berhasil ditambahkan.

                        <button type="button" class="btn-close"
                                data-bs-dismiss="alert"></button>
                    </div>

                <?php elseif ($_GET['pesan'] == 'edit'): ?>

                    <div class="alert alert-success alert-dismissible fade show">
                        Catatan pelanggaran berhasil diperbarui.

                        <button type="button" class="btn-close"
                                data-bs-dismiss="alert"></button>
                    </div>

                <?php elseif ($_GET['pesan'] == 'hapus'): ?>

                    <div class="alert alert-success alert-dismissible fade show">
                        Catatan pelanggaran berhasil dihapus.

                        <button type="button" class="btn-close"
                                data-bs-dismiss="alert"></button>
                    </div>

                <?php endif; ?>

            <?php endif; ?>

            <!-- Tabel catatan pelanggaran -->
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-table me-2"></i>
                        Data Catatan Pelanggaran Siswa
                    </h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table id="tabelCatatan"
                               class="table table-bordered table-striped table-hover align-middle text-nowrap w-100">

                            <thead class="table-primary text-center">
                                <tr>
                                    <th>No.</th>
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

                                while ($data = mysqli_fetch_assoc($query)):
                                ?>

                                    <tr>
                                        <td class="text-center">
                                            <?= $no++; ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tanggal'] ?? '-'); ?>
                                        </td>

                                        <td>
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

                                            <?php
                                            $status = $data['status'] ?? '';

                                            if ($status == 'Belum diproses') {
                                                echo '<span class="badge bg-warning text-dark">Belum diproses</span>';
                                            } elseif ($status == 'Diproses') {
                                                echo '<span class="badge bg-info text-dark">Diproses</span>';
                                            } elseif ($status == 'Selesai') {
                                                echo '<span class="badge bg-success">Selesai</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">' .
                                                    htmlspecialchars($status ?: '-') .
                                                    '</span>';
                                            }
                                            ?>

                                        </td>

                                        <td class="text-center">

                                            <div class="d-flex justify-content-center gap-1">

                                                <a href="edit_catatan.php?id=<?= (int)$data['id']; ?>"
                                                   class="btn btn-warning btn-sm"
                                                   title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <a href="hapus_catatan.php?id=<?= (int)$data['id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   title="Hapus"
                                                   onclick="return confirm('Yakin ingin menghapus catatan ini?')">
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

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JavaScript -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Mengaktifkan DataTables -->
<script>
$(document).ready(function () {

    $('#tabelCatatan').DataTable({

        // Jumlah data per halaman
        pageLength: 10,

        // Pilihan jumlah data
        lengthMenu: [5, 10, 25, 50, 100],

        // Tidak mengurutkan otomatis
        order: [],

        // Mengaktifkan fitur DataTables
        searching: true,
        ordering: true,
        paging: true,
        info: true,

        // Bahasa Indonesia
        language: {
            search: "Cari:",
            searchPlaceholder: "Cari catatan pelanggaran...",

            lengthMenu: "Tampilkan _MENU_ data",

            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",

            infoEmpty: "Belum ada data",

            infoFiltered: "(disaring dari _MAX_ total data)",

            zeroRecords: "Data tidak ditemukan",

            emptyTable: "Belum ada catatan pelanggaran",

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
