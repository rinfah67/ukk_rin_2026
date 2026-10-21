
<?php
// penempatan_siswa.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Ambil daftar siswa aktif
$siswa = mysqli_query($koneksi, "
    SELECT id, nis, nama
    FROM t_siswa
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// Ambil daftar tahun ajaran aktif
$tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY id DESC
");

// Ambil daftar kelas aktif
$kelas = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// Ambil data penempatan siswa
$sql = "
    SELECT
        ks.id,
        s.nis,
        s.nama AS nama_siswa,
        ta.nama AS tahun_ajaran,
        k.nama AS nama_kelas,
        k.tingkat,
        k.jurusan,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        ks.status_aktif
    FROM t_kelas_siswa ks
    LEFT JOIN t_siswa s ON ks.siswa_id = s.id
    LEFT JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    LEFT JOIN t_kelas k ON ks.kelas_id = k.id
    ORDER BY ks.id DESC
";

$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Penempatan Siswa - Si Pelang</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- CSS DataTables -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <?php include "layout/sidebar.php"; ?>

    <main class="row">
        <div class="col-12 col-lg-10 offset-lg-2 p-3 p-md-4">

            <!-- Header halaman -->
            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold text-primary mb-1">
                        <i class="bi bi-person-lines-fill me-2"></i>
                        Penempatan Siswa
                    </h2>

                    <p class="text-secondary mb-0">
                        Kelola penempatan siswa berdasarkan kelas dan tahun ajaran.
                    </p>
                </div>

                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="bi bi-house-door me-1"></i>
                    Dashboard
                </a>
            </div>

            <!-- Notifikasi sukses -->
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

            <!-- Notifikasi error -->
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

            <!-- Form penempatan siswa -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">

                <div class="card-header bg-primary text-white py-3 rounded-top-4">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-plus-circle me-2"></i>
                        Form Penempatan Siswa
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form action="proses_simpan_penempatan.php"
                          method="POST">

                        <div class="row g-3">

                            <!-- Pilih siswa -->
                            <div class="col-12 col-md-6">
                                <label for="siswa_id"
                                       class="form-label fw-semibold">
                                    Siswa
                                </label>

                                <select name="siswa_id" id="siswa_id"
                                        class="form-select" required>

                                    <option value="">-- Pilih Siswa --</option>

                                    <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                                        <option value="<?= (int)$s['id'] ?>">
                                            <?= htmlspecialchars(
                                                $s['nis'] . ' - ' . $s['nama']
                                            ) ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>

                            <!-- Pilih tahun ajaran -->
                            <div class="col-12 col-md-6">
                                <label for="tahun_ajaran_id"
                                       class="form-label fw-semibold">
                                    Tahun Ajaran
                                </label>

                                <select name="tahun_ajaran_id"
                                        id="tahun_ajaran_id"
                                        class="form-select" required>

                                    <option value="">
                                        -- Pilih Tahun Ajaran --
                                    </option>

                                    <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
                                        <option value="<?= (int)$ta['id'] ?>">
                                            <?= htmlspecialchars($ta['nama']) ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>

                            <!-- Pilih kelas -->
                            <div class="col-12 col-md-6">
                                <label for="kelas_id"
                                       class="form-label fw-semibold">
                                    Kelas
                                </label>

                                <select name="kelas_id" id="kelas_id"
                                        class="form-select" required>

                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?= (int)$k['id'] ?>">
                                            <?= htmlspecialchars(
                                                $k['nama'] . ' - Tingkat ' .
                                                $k['tingkat'] . ' - ' .
                                                $k['jurusan']
                                            ) ?>
                                        </option>
                                    <?php } ?>

                                </select>
                            </div>

                            <!-- Tanggal mulai -->
                            <div class="col-12 col-md-6">
                                <label for="tanggal_mulai"
                                       class="form-label fw-semibold">
                                    Tanggal Mulai
                                </label>

                                <input type="date" name="tanggal_mulai"
                                       id="tanggal_mulai"
                                       class="form-control" required>
                            </div>

                            <!-- Tanggal selesai -->
                            <div class="col-12 col-md-6">
                                <label for="tanggal_selesai"
                                       class="form-label fw-semibold">
                                    Tanggal Selesai
                                </label>

                                <input type="date" name="tanggal_selesai"
                                       id="tanggal_selesai"
                                       class="form-control" required>
                            </div>

                            <!-- Status -->
                            <div class="col-12 col-md-6">
                                <label for="status_aktif"
                                       class="form-label fw-semibold">
                                    Status Penempatan
                                </label>

                                <select name="status_aktif"
                                        id="status_aktif"
                                        class="form-select" required>

                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>

                                </select>
                            </div>

                        </div>

                        <!-- Tombol -->
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i>
                                Simpan Penempatan
                            </button>

                            <button type="reset"
                                    class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Reset
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Tabel data penempatan -->
            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-table text-primary me-2"></i>
                        Data Penempatan Siswa
                    </h5>

                    <p class="text-secondary mb-0 small">
                        Cari, urutkan, dan lihat data penempatan siswa.
                    </p>
                </div>

                <div class="card-body pt-0">

                    <div class="table-responsive">
                        <table id="tabelPenempatan"
                               class="table table-striped table-hover
                                      table-bordered align-middle w-100">

                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Kelas</th>
                                    <th>Tingkat</th>
                                    <th>Jurusan</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;

                                if ($hasil) {
                                    while ($row = mysqli_fetch_assoc($hasil)) {
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>

                                        <td>
                                            <?= htmlspecialchars($row['nis'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_siswa'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tahun_ajaran'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_kelas'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tingkat'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['jurusan'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tanggal_mulai'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tanggal_selesai'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <?php if ($row['status_aktif'] == 1) { ?>
                                                <span class="badge bg-success">
                                                    Aktif
                                                </span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">
                                                    Tidak Aktif
                                                </span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                }
                                ?>
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

            <p class="text-secondary text-center small mt-4">
                Si Pelang &copy; <?= date('Y') ?>
            </p>

        </div>
    </main>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery untuk DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- JavaScript DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelPenempatan').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        scrollX: true,
        language: {
            search: "Cari:",
            searchPlaceholder: "Cari data siswa...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data penempatan siswa",
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
