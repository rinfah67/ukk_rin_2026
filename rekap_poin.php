
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Khusus admin
if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: rekap_poin.php");
    exit;
}

// Ambil input pencarian dan filter
$cari = trim($_GET['cari'] ?? '');
$kelas_filter = (int) ($_GET['kelas'] ?? 0);

// Daftar kelas
$data_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

// Ringkasan jumlah siswa
$hasil = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM t_siswa");
$jumlah_siswa = mysqli_fetch_assoc($hasil)['jumlah'] ?? 0;

// Ringkasan total poin
$hasil = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(poin), 0) AS total FROM t_pelanggaran_siswa"
);
$total_poin = mysqli_fetch_assoc($hasil)['total'] ?? 0;

// Jumlah catatan
$hasil = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS jumlah FROM t_pelanggaran_siswa"
);
$jumlah_catatan = mysqli_fetch_assoc($hasil)['jumlah'] ?? 0;

// Query rekap poin
$sql = "SELECT
            s.id,
            s.nis,
            s.nama,
            k.nama AS nama_kelas,
            COUNT(p.id) AS jumlah_pelanggaran,
            COALESCE(SUM(p.poin), 0) AS total_poin
        FROM t_siswa s
        LEFT JOIN t_kelas_siswa ks
            ON ks.siswa_id = s.id
            AND ks.status_aktif = 1
        LEFT JOIN t_kelas k ON k.id = ks.kelas_id
        LEFT JOIN t_pelanggaran_siswa p ON p.siswa_id = s.id
        WHERE (s.nama LIKE ? OR s.nis LIKE ?)
        AND (? = 0 OR k.id = ?)
        GROUP BY s.id, s.nis, s.nama, k.nama
        ORDER BY total_poin DESC, s.nama ASC";

$stmt = mysqli_prepare($koneksi, $sql);
$kata_cari = "%$cari%";

mysqli_stmt_bind_param(
    $stmt,
    "ssii",
    $kata_cari,
    $kata_cari,
    $kelas_filter,
    $kelas_filter
);

mysqli_stmt_execute($stmt);
$rekap = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Rekap Poin Pelanggaran</title>

    <!-- Bootstrap saja, tanpa CSS tambahan -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<div class="container-fluid">
    <?php include "layout/sidebar.php"; ?>

    <main class="row">
        <div class="col-9 offset-3 col-lg-10 offset-lg-2 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="text-primary fw-bold">
                        Rekap Poin Pelanggaran Siswa
                    </h3>
                    <p class="text-secondary mb-0">
                        Ringkasan jumlah pelanggaran dan poin setiap siswa.
                    </p>
                </div>

                <div class="d-flex gap-2">
                    <a href="dashboard.php" class="btn btn-primary">
                        Kembali ke Dashboard
                    </a>

                    <button onclick="window.print()" class="btn btn-success">
                        Cetak Rekap
                    </button>
                </div>
            </div>

            <!-- RINGKASAN -->
            <div class="row g-3 mb-4">

                <div class="col-12 col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Jumlah Siswa</p>
                            <h3 class="text-primary fw-bold">
                                <?= number_format((int)$jumlah_siswa) ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Total Poin</p>
                            <h3 class="text-primary fw-bold">
                                <?= number_format((int)$total_poin) ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <p class="text-secondary mb-1">
                                Jumlah Catatan Pelanggaran
                            </p>
                            <h3 class="text-primary fw-bold">
                                <?= number_format((int)$jumlah_catatan) ?>
                            </h3>
                        </div>
                    </div>
                </div>

            </div>

            <!-- PENCARIAN DAN FILTER -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="GET" action="rekap_poin.php">
                        <div class="row g-3 align-items-end">

                            <div class="col-12 col-lg-5">
                                <label for="cari" class="form-label">
                                    Cari Siswa
                                </label>
                                <input type="text"
                                       name="cari"
                                       id="cari"
                                       class="form-control"
                                       placeholder="Masukkan nama atau NIS siswa"
                                       value="<?= htmlspecialchars($cari) ?>">
                            </div>

                            <div class="col-12 col-lg-4">
                                <label for="kelas" class="form-label">
                                    Filter Kelas
                                </label>
                                <select name="kelas" id="kelas" class="form-select">
                                    <option value="0">Semua Kelas</option>

                                    <?php while ($kelas = mysqli_fetch_assoc($data_kelas)) { ?>
                                        <option value="<?= (int)$kelas['id'] ?>"
                                            <?= $kelas_filter == $kelas['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kelas['nama']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-12 col-lg-3">
                                <button type="submit" class="btn btn-primary">
                                    Cari
                                </button>

                                <a href="rekap_poin.php" class="btn btn-secondary">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

           
            <!-- TABEL REKAP -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabelRekap"
                               class="table table-bordered table-hover table-striped align-middle w-100">

                            <thead class="table-primary">
                                <tr>
                                    <th>No.</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th class="text-center">Jumlah Pelanggaran</th>
                                    <th class="text-center">Total Poin</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php if (mysqli_num_rows($rekap) > 0) { ?>
                                <?php $no = 1; ?>

                                <?php while ($row = mysqli_fetch_assoc($rekap)) { ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nis'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['nama'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?= (int)$row['jumlah_pelanggaran'] ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-bg-warning">
                                                <?= number_format((int)$row['total_poin']) ?>
                                                poin
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>

                            <?php } else { ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary p-4">
                                        Data siswa tidak ditemukan.
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

<!-- DataTables -->
<link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
      rel="stylesheet">

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Mengaktifkan DataTables -->
<script>
$(document).ready(function () {
    $('#tabelRekap').DataTable({
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
            emptyTable: "Belum ada data rekap",
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
