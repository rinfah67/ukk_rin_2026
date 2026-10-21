```php
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Memeriksa hak akses admin dan guru
$role = $_SESSION['role'] ?? '';

if ($role != 'admin' && $role != 'guru') {
    die("Akses ditolak.");
}

// Mengambil semua catatan pelanggaran
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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Catatan Pelanggaran</title>

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

<body class="bg-light">

<div class="container-fluid">
    <?php include "layout/sidebar.php"; ?>

    <main class="row">
        <div class="col-12 col-lg-10 offset-lg-2 p-3 p-md-4">

            <!-- Judul halaman -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1">
                        <i class="bi bi-journal-text"></i>
                        Catatan Pelanggaran Siswa
                    </h3>

                    <p class="text-muted mb-0">
                        Kelola data catatan pelanggaran siswa.
                    </p>
                </div>

                <a href="tambah_catatan.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Catatan
                </a>
            </div>

            <!-- Pesan error -->
            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>

            <!-- Pesan sukses -->
            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']) ?>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>

            <!-- Pesan edit dan hapus -->
            <?php if (isset($_GET['pesan'])): ?>

                <?php if ($_GET['pesan'] == 'tambah'): ?>
                    <div class="alert alert-success">
                        Catatan berhasil ditambahkan.
                    </div>

                <?php elseif ($_GET['pesan'] == 'edit'): ?>
                    <div class="alert alert-success">
                        Catatan berhasil diperbarui.
                    </div>

                <?php elseif ($_GET['pesan'] == 'hapus'): ?>
                    <div class="alert alert-success">
                        Catatan berhasil dihapus.
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <!-- Tabel catatan -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <div class="table-responsive">
                        <table id="tabelCatatan"
                               class="table table-bordered table-striped table-hover align-middle w-100">

                            <thead class="table-dark">
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
                            <?php $no = 1; ?>

                            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_siswa'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_kelas'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_pelanggaran'] ?? '') ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-danger">
                                            <?= htmlspecialchars((string)($row['poin'] ?? 0)) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_guru'] ?? '') ?>
                                    </td>

                                    <td>
                                        <?php
                                        $status = $row['status'] ?? '';
                                        $warna = 'secondary';

                                        if ($status == 'Belum diproses') {
                                            $warna = 'warning text-dark';
                                        } elseif ($status == 'Diproses') {
                                            $warna = 'info text-dark';
                                        } elseif ($status == 'Selesai') {
                                            $warna = 'success';
                                        }
                                        ?>

                                        <span class="badge bg-<?= $warna ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="edit_catatan.php?id=<?= (int)$row['id'] ?>"
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                                Edit
                                            </a>

                                            <a href="hapus_catatan.php?id=<?= (int)$row['id'] ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Yakin ingin menghapus catatan ini?')">
                                                <i class="bi bi-trash"></i>
                                                Hapus
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

        </div>
    </main>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    // Mengaktifkan pencarian dan pagination
    $('#tabelCatatan').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Belum ada data",
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
```