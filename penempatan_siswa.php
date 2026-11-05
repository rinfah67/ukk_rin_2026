
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Membuat token keamanan
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mengambil pesan
$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

// Mengambil tahun ajaran aktif
$query_tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY id DESC
");

// Mengambil siswa aktif yang BELUM PERNAH ditempatkan
$query_siswa = mysqli_query($koneksi, "
    SELECT
        s.id,
        s.nis,
        s.nama
    FROM t_siswa s
    WHERE s.status_aktif = 1
    AND NOT EXISTS (
        SELECT 1
        FROM t_kelas_siswa ks
        WHERE ks.siswa_id = s.id
    )
    ORDER BY s.nama ASC
");

// Mengambil kelas aktif
$query_kelas = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// Mengambil seluruh riwayat penempatan
$query_penempatan = mysqli_query($koneksi, "
    SELECT
        ks.id,
        s.nis,
        s.nama AS nama_siswa,
        ta.nama AS tahun_ajaran,
        k.nama AS nama_kelas,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        ks.status_aktif
    FROM t_kelas_siswa ks
    INNER JOIN t_siswa s ON ks.siswa_id = s.id
    INNER JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    INNER JOIN t_kelas k ON ks.kelas_id = k.id
    ORDER BY ks.id DESC
");

// Memeriksa kesalahan query
if (!$query_tahun || !$query_siswa || !$query_kelas || !$query_penempatan) {
    die("Terjadi kesalahan query: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Penempatan Siswa - SI PELANG</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten utama -->
        <main class="col-md-9 col-lg-10 p-4">

            <h4 class="mb-4">
                <i class="bi bi-person-lines-fill"></i>
                Penempatan Siswa
            </h4>

            <!-- Pesan sukses -->
            <?php if ($pesan_sukses != '') : ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($pesan_sukses) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Pesan error -->
            <?php if ($pesan_error != '') : ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($pesan_error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Form Tambah Penempatan -->
            <div class="card shadow-sm mb-4">

                <div class="card-header bg-primary text-white">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Penempatan Siswa
                </div>

                <div class="card-body">

                    <form action="proses_simpan_penempatan.php" method="POST">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <div class="row">

                            <!-- Tahun Ajaran -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Ajaran</label>

                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>

                                    <?php while ($tahun = mysqli_fetch_assoc($query_tahun)) : ?>
                                        <option value="<?= (int)$tahun['id'] ?>">
                                            <?= htmlspecialchars($tahun['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>
                            </div>

                            <!-- Siswa -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Siswa</label>

                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>

                                    <?php while ($siswa = mysqli_fetch_assoc($query_siswa)) : ?>
                                        <option value="<?= (int)$siswa['id'] ?>">
                                            <?= htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>

                                <small class="text-muted">
                                    Siswa yang sudah pernah ditempatkan tidak akan muncul di pilihan ini.
                                </small>
                            </div>

                            <!-- Kelas -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas</label>

                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($kelas = mysqli_fetch_assoc($query_kelas)) : ?>
                                        <option value="<?= (int)$kelas['id'] ?>">
                                            <?= htmlspecialchars($kelas['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>

                                </select>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" required>
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" required>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>

                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" selected>Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Simpan Penempatan
                        </button>

                    </form>

                </div>
            </div>

            <!-- Tabel Data Penempatan -->
            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <i class="bi bi-table"></i>
                    Data Penempatan Siswa
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table id="tabelPenempatan" class="table table-bordered table-striped table-hover align-middle">

                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Kelas</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php $no = 1; ?>

                                <?php while ($data = mysqli_fetch_assoc($query_penempatan)) : ?>

                                    <tr>
                                        <td><?= $no++ ?></td>

                                        <td>
                                            <?= htmlspecialchars($data['nis']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_siswa']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tahun_ajaran']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['nama_kelas']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tanggal_mulai']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($data['tanggal_selesai']) ?>
                                        </td>

                                        <td>
                                            <?php if ($data['status_aktif'] == 1) : ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-1">

                                                <!-- Tombol Edit -->
                                                <a href="edit_penempatan_siswa.php?id=<?= (int)$data['id'] ?>"
                                                   class="btn btn-warning btn-sm"
                                                   title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <!-- Tombol Hapus -->
                                                <form action="hapus_penempatan_siswa.php"
                                                      method="POST"
                                                      onsubmit="return confirm('Yakin ingin menghapus penempatan siswa ini?')">

                                                    <input type="hidden" name="id"
                                                           value="<?= (int)$data['id'] ?>">

                                                    <input type="hidden" name="csrf_token"
                                                           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>

                                                </form>

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

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelPenempatan').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        ordering: true,

        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",

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
