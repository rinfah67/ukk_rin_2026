
<?php
// riwayat.php

// Memeriksa session login
include 'includes/cek_session.php';

// Menghubungkan ke database
include 'config/koneksi.php';

// Mengambil kata pencarian
$cari = isset($_GET['cari'])
    ? mysqli_real_escape_string($koneksi, trim($_GET['cari']))
    : '';

// Mengambil filter status
$status = isset($_GET['status'])
    ? mysqli_real_escape_string($koneksi, trim($_GET['status']))
    : '';

// Query dasar
$sql = "SELECT *
        FROM t_pelanggaran_siswa
        WHERE 1=1";

// Pencarian riwayat
if ($cari != '') {
    $sql .= " AND (
        nama_siswa LIKE '%$cari%'
        OR nama_pelanggaran LIKE '%$cari%'
        OR nama_kelas LIKE '%$cari%'
        OR nama_guru LIKE '%$cari%'
    )";
}

// Filter status
if ($status != '') {
    $sql .= " AND status = '$status'";
}

// Urutkan dari tanggal terbaru
$sql .= " ORDER BY tanggal DESC, id DESC";

// Jalankan query
$query = mysqli_query($koneksi, $sql);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

// Hitung jumlah data
$jumlah = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Riwayat Pelanggaran Siswa</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- CSS DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <?php include "layout/sidebar.php"; ?>

    <main class="row">
        <div class="col-9 offset-3 col-lg-10 offset-lg-2 p-4">

            <!-- Judul halaman -->
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h3>Riwayat Pelanggaran Siswa</h3>
                    <p class="text-muted mb-0">
                        Daftar catatan pelanggaran siswa yang telah dicatat.
                    </p>
                </div>

                <a href="dashboard.php" class="btn btn-secondary">
                    Kembali ke Dashboard
                </a>
            </div>

            <!-- Ringkasan jumlah data -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="text-muted">Jumlah Riwayat Ditampilkan</h6>
                    <h3 class="fw-bold text-primary">
                        <?= $jumlah ?> data
                    </h3>
                </div>
            </div>

            <!-- Pencarian dan filter status dari PHP -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    <form method="GET" action="riwayat.php">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Cari Riwayat</label>
                                <input
                                    type="text"
                                    name="cari"
                                    class="form-control"
                                    placeholder="Nama siswa, pelanggaran, kelas..."
                                    value="<?= htmlspecialchars($cari) ?>"
                                >
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Filter Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>

                                    <option value="belum"
                                        <?= $status == 'belum' ? 'selected' : '' ?>>
                                        Belum Ditindak
                                    </option>

                                    <option value="selesai"
                                        <?= $status == 'selesai' ? 'selected' : '' ?>>
                                        Selesai
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Cari
                                </button>

                                <a href="riwayat.php" class="btn btn-outline-secondary">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>

                </div>
            </div>

            <!-- Tabel riwayat -->
            <div class="card shadow-sm">
                <div class="card-body">

                    <div class="table-responsive">
                        <table id="tabelRiwayat"
                               class="table table-bordered table-striped table-hover align-middle w-100">

                            <thead class="table-primary">
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th>Poin</th>
                                    <th>Guru Pencatat</th>
                                    <th>Keterangan</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php
                            $no = 1;

                            while ($data = mysqli_fetch_assoc($query)) {
                                $status_data = strtolower(
                                    trim($data['status'] ?? '')
                                );
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td>
                                        <?= htmlspecialchars($data['tanggal'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['nama_siswa'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['nama_kelas'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['nama_pelanggaran'] ?? '-') ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">
                                            <?= htmlspecialchars((string)($data['poin'] ?? 0)) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['nama_guru'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['keterangan'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data['tindakan'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?php if ($status_data == 'selesai') { ?>

                                            <span class="badge bg-success">
                                                Selesai
                                            </span>

                                        <?php } elseif (
                                            $status_data == 'belum' ||
                                            $status_data == 'belum ditindak'
                                        ) { ?>

                                            <span class="badge bg-danger">
                                                Belum Ditindak
                                            </span>

                                        <?php } else { ?>

                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($data['status'] ?? '-') ?>
                                            </span>

                                        <?php } ?>
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

<!-- JavaScript DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Mengaktifkan DataTables -->
<script>
$(document).ready(function () {
    $('#tabelRiwayat').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        language: {
            search: "Cari di tabel:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada riwayat pelanggaran",
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
