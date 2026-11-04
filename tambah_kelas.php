
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Memeriksa hak akses admin
if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

// Mengambil data siswa
$siswa = mysqli_query($koneksi,
    "SELECT id, nis, nama
     FROM t_siswa
     WHERE status_aktif = 1
     ORDER BY nama"
);

// Mengambil data kelas
$kelas = mysqli_query($koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     WHERE status_aktif = 1
     ORDER BY nama"
);

// Mengambil data tahun ajaran
$tahun = mysqli_query($koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY tanggal_mulai DESC"
);

// Mengambil daftar kelas siswa
$data_kelas_siswa = mysqli_query($koneksi, "
    SELECT
        s.nis,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        ta.nama AS tahun_ajaran,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        ks.status_aktif
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON ks.siswa_id = s.id
    JOIN t_kelas k ON ks.kelas_id = k.id
    JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    ORDER BY s.nama ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Kelas Siswa</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">
    <!-- Judul halaman -->
    <div class="mb-4">
        <h3>Tambah Kelas Siswa</h3>
        <a href="kelola_kelas.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Form Tambah Kelas Siswa -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Tambah Kelas Siswa</h5>
        </div>

        <div class="card-body">

            <form action="proses_tambah_kelas.php" method="POST">

                <div class="row g-3">

                    <!-- Nama Siswa -->
                    <div class="col-md-6">
                        <label class="form-label">Nama Siswa</label>
                        <select name="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>

                            <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                                <option value="<?php echo $s['id']; ?>">
                                    <?php echo htmlspecialchars($s['nis'] . ' - ' . $s['nama']); ?>
                                </option>
                            <?php } ?>

                        </select>
                    </div>

                    <!-- Kelas -->
                    <div class="col-md-6">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>

                            <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                                <option value="<?php echo $k['id']; ?>">
                                    <?php
                                    echo htmlspecialchars(
                                        $k['nama'] . ' - ' .
                                        $k['tingkat'] . ' ' .
                                        $k['jurusan']
                                    );
                                    ?>
                                </option>
                            <?php } ?>

                        </select>
                    </div>

                    <!-- Tahun Ajaran -->
                    <div class="col-md-6">
                        <label class="form-label">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" class="form-select" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>

                            <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
                                <option value="<?php echo $ta['id']; ?>">
                                    <?php echo htmlspecialchars($ta['nama']); ?>
                                </option>
                            <?php } ?>

                        </select>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" required>
                    </div>

                    <!-- Tanggal Selesai -->
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" required>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status_aktif" class="form-select" required>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>

                </div>

                <div class="mt-4">
                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>

                    <button type="reset" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- Tabel Data Kelas Siswa -->
    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Data Kelas Siswa</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table id="tabelKelasSiswa" class="table table-bordered table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $no = 1;

                        while ($d = mysqli_fetch_assoc($data_kelas_siswa)) {
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>

                                <td>
                                    <?php echo htmlspecialchars($d['nis']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($d['nama_siswa']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($d['nama_kelas']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($d['tahun_ajaran']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($d['tanggal_mulai']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($d['tanggal_selesai']); ?>
                                </td>

                                <td>
                                    <?php if ($d['status_aktif'] == 1) { ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php } else { ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<!-- Inisialisasi DataTables -->
<script>
$(document).ready(function() {
    $('#tabelKelasSiswa').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        ordering: true,
        searching: true,
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
