
<?php
// Memulai session
session_start();

// Menghubungkan ke database
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Memastikan hanya admin yang bisa mengelola data wali kelas
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak!");
}

// Mengambil data wali kelas beserta nama guru, kelas, dan tahun ajaran
$query = "SELECT
              wk.*,
              g.nama AS nama_guru,
              k.nama AS nama_kelas,
              k.tingkat,
              k.jurusan,
              ta.nama AS tahun_ajaran
          FROM t_wali_kelas wk
          LEFT JOIN t_guru g ON wk.guru_id = g.id
          LEFT JOIN t_kelas k ON wk.kelas_id = k.id
          LEFT JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
          ORDER BY wk.id DESC";

// Menjalankan query
$data = mysqli_query($koneksi, $query);

// Memeriksa apakah query berhasil
if (!$data) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Mengatur karakter dan ukuran layar -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Judul halaman -->
    <title>Kelola Wali Kelas - SI PELANG</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- CSS DataTables untuk Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">

    <!-- Memanggil sidebar -->
    <?php include "layout/sidebar.php"; ?>

    <!-- Konten utama agar tidak tertutup sidebar -->
    <main class="row">
        <div class="col-9 offset-3 col-lg-10 offset-lg-2 p-3 p-md-4">

            <!-- Judul dan tombol -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <h3 class="mb-0">Data Wali Kelas</h3>

                <div class="d-flex gap-2">
                    <a href="dashboard.php" class="btn btn-secondary">
                        Kembali ke Dashboard
                    </a>

                    <a href="tambah_walikelas.php" class="btn btn-primary">
                        + Tambah Wali Kelas
                    </a>
                </div>
            </div>

            <!-- Wadah tabel -->
            <div class="card shadow-sm">
                <div class="card-body">

                    <!-- Membuat tabel responsif -->
                    <div class="table-responsive">

                        <!-- Tabel yang akan diaktifkan DataTables -->
                        <table id="tabelWaliKelas"
                               class="table table-bordered table-striped table-hover align-middle w-100">

                            <!-- Kepala tabel -->
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <!-- Isi tabel dari database -->
                            <tbody>
                                <?php
                                // Membuat nomor urut
                                $no = 1;

                                // Mengulang data yang diperoleh dari database
                                while ($row = mysqli_fetch_assoc($data)) {
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <!-- Nama guru -->
                                        <td>
                                            <?= htmlspecialchars($row['nama_guru'] ?? '-'); ?>
                                        </td>

                                        <!-- Nama, tingkat, dan jurusan kelas -->
                                        <td>
                                            <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                            (<?= htmlspecialchars($row['tingkat'] ?? '-'); ?>
                                            <?= htmlspecialchars($row['jurusan'] ?? ''); ?>)
                                        </td>

                                        <!-- Tahun ajaran -->
                                        <td>
                                            <?= htmlspecialchars($row['tahun_ajaran'] ?? '-'); ?>
                                        </td>

                                        <!-- Tanggal mulai penugasan -->
                                        <td>
                                            <?= htmlspecialchars($row['tanggal_mulai'] ?? '-'); ?>
                                        </td>

                                        <!-- Tanggal selesai penugasan -->
                                        <td>
                                            <?= htmlspecialchars($row['tanggal_selesai'] ?? '-'); ?>
                                        </td>

                                        <!-- Status aktif atau tidak aktif -->
                                        <td>
                                            <?php if (($row['status_aktif'] ?? 0) == 1) { ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>

                                        <!-- Tombol edit dan hapus -->
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="edit_walikelas.php?id=<?= (int)$row['id']; ?>"
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                <a href="hapus_walikelas.php?id=<?= (int)$row['id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
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

        </div>
    </main>
</div>

<!-- Memanggil jQuery untuk DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- JavaScript Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<!-- Integrasi DataTables dengan Bootstrap 5 -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
// Menjalankan kode setelah halaman selesai dimuat
$(document).ready(function () {

    // Mengaktifkan DataTables pada tabel wali kelas
    $('#tabelWaliKelas').DataTable({

        // Mengatur teks menjadi bahasa Indonesia
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data wali kelas",

            // Mengatur tombol navigasi halaman
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },

        // Jumlah data yang ditampilkan pertama kali
        pageLength: 10,

        // Pilihan jumlah data per halaman
        lengthMenu: [5, 10, 25, 50, 100],

        // Mengurutkan berdasarkan kolom No secara menurun
        order: [[0, 'desc']],

        // Mengaktifkan gulir horizontal jika tabel terlalu lebar
        scrollX: true
    });

});
</script>

</body>
</html>
