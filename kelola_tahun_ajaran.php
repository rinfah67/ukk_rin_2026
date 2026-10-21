
<?php
// Menampilkan semua error PHP agar mudah mengetahui jika ada kesalahan
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Memulai session untuk memeriksa status login pengguna
session_start();

// Menghubungkan file ini dengan database
include 'config/koneksi.php';

// Memeriksa apakah pengguna sudah login
// Jika belum, pengguna diarahkan ke halaman login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Memeriksa hak akses pengguna
// Halaman ini hanya boleh dibuka oleh admin
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak! Halaman ini hanya untuk admin.");
}

// Mengambil seluruh data tahun ajaran dari tabel t_tahun_ajaran
// Data diurutkan berdasarkan ID dari yang terbesar ke terkecil
$sql = "SELECT * FROM t_tahun_ajaran ORDER BY id DESC";

// Menjalankan perintah SQL menggunakan koneksi database
$hasil = mysqli_query($koneksi, $sql);

// Memeriksa apakah pengambilan data berhasil
// Jika gagal, tampilkan pesan kesalahan database
if (!$hasil) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Mengatur karakter agar teks dapat menampilkan huruf dengan benar -->
    <meta charset="UTF-8">

    <!-- Membuat tampilan menyesuaikan ukuran layar perangkat -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Judul yang muncul pada tab browser -->
    <title>Kelola Tahun Ajaran - SI PELANG</title>

    <!-- Memanggil Bootstrap 5 untuk tampilan dan komponen halaman -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Memanggil DataTables dengan tampilan Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<!-- Memberikan warna latar belakang menggunakan kelas Bootstrap -->
<body class="bg-light">

<!-- Membuat wadah utama halaman selebar layar -->
<div class="container-fluid">

    <!-- Memanggil sidebar dari file terpisah -->
    <?php include "layout/sidebar.php"; ?>

    <!-- Membuat baris untuk menampung konten utama -->
    <main class="row">

        <!--
            col-9       : lebar konten 9 dari 12 kolom pada layar kecil
            offset-3    : menggeser konten 3 kolom agar tidak menimpa sidebar
            col-lg-10   : lebar konten 10 kolom pada layar besar
            offset-lg-2 : menggeser konten 2 kolom pada layar besar
            p-3 p-md-4  : memberi jarak di dalam konten
        -->
        <div class="col-9 offset-3 col-lg-10 offset-lg-2 p-3 p-md-4">

            <!-- Bagian judul halaman dan tombol navigasi -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

                <!-- Judul halaman -->
                <h3 class="mb-0">Kelola Tahun Ajaran</h3>

                <!-- Mengelompokkan tombol agar tersusun rapi -->
                <div class="d-flex flex-wrap gap-2">

                    <!-- Tombol untuk kembali ke dashboard -->
                    <a href="dashboard.php" class="btn btn-secondary">
                        Kembali ke Dashboard
                    </a>

                    <!-- Tombol untuk membuka halaman tambah tahun ajaran -->
                    <a href="tambah_tahun_ajaran.php" class="btn btn-primary">
                        + Tambah Tahun Ajaran
                    </a>

                </div>
            </div>

            <!-- Card Bootstrap sebagai wadah tabel -->
            <div class="card shadow-sm">
                <div class="card-body">

                    <!--
                        table-responsive membuat tabel dapat digulir
                        secara horizontal jika layar terlalu sempit
                    -->
                    <div class="table-responsive">

                        <!--
                            id digunakan DataTables untuk mengenali tabel
                            table-bordered menambahkan garis tabel
                            table-striped memberi warna selang-seling
                            table-hover memberi efek saat kursor diarahkan
                            align-middle meratakan isi sel secara vertikal
                        -->
                        <table id="tabelTahunAjaran"
                               class="table table-bordered table-striped table-hover align-middle w-100">

                            <!-- Bagian kepala tabel -->
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Tahun Ajaran</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <!-- Bagian isi tabel yang mengambil data dari database -->
                            <tbody>
                                <?php
                                // Membuat nomor urut yang dimulai dari 1
                                $no = 1;

                                // Mengulang setiap baris hasil query database
                                while ($data = mysqli_fetch_assoc($hasil)) {
                                ?>
                                    <tr>
                                        <!-- Menampilkan nomor urut -->
                                        <td><?= $no++; ?></td>

                                        <!-- Menampilkan nama tahun ajaran dengan pengamanan output HTML -->
                                        <td>
                                            <?= htmlspecialchars($data['nama'] ?? ''); ?>
                                        </td>

                                        <!-- Menampilkan tanggal mulai tahun ajaran -->
                                        <td>
                                            <?= htmlspecialchars($data['tanggal_mulai'] ?? ''); ?>
                                        </td>

                                        <!-- Menampilkan tanggal selesai tahun ajaran -->
                                        <td>
                                            <?= htmlspecialchars($data['tanggal_selesai'] ?? ''); ?>
                                        </td>

                                        <!-- Memeriksa status aktif tahun ajaran -->
                                        <td>
                                            <?php if (($data['status_aktif'] ?? 0) == 1) { ?>

                                                <!-- Badge hijau jika tahun ajaran aktif -->
                                                <span class="badge bg-success">Aktif</span>

                                            <?php } else { ?>

                                                <!-- Badge abu-abu jika tahun ajaran tidak aktif -->
                                                <span class="badge bg-secondary">Tidak Aktif</span>

                                            <?php } ?>
                                        </td>

                                        <!-- Menampilkan waktu data pertama kali dibuat -->
                                        <td>
                                            <?= htmlspecialchars($data['created_at'] ?? '-'); ?>
                                        </td>

                                        <!-- Menampilkan waktu data terakhir diperbarui -->
                                        <td>
                                            <?= htmlspecialchars($data['updated_at'] ?? '-'); ?>
                                        </td>

                                        <!-- Tombol untuk mengubah dan menghapus data -->
                                        <td>
                                            <div class="d-flex gap-1">

                                                <!--
                                                    Mengirim ID tahun ajaran ke halaman edit
                                                    agar data yang dipilih dapat diubah
                                                -->
                                                <a href="edit_tahun_ajaran.php?id=<?= (int)$data['id']; ?>"
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                <!--
                                                    Mengirim ID tahun ajaran ke halaman hapus
                                                    dan meminta konfirmasi sebelum melanjutkan
                                                -->
                                                <a href="hapus_tahun_ajaran.php?id=<?= (int)$data['id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                                                    Hapus
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                // Menutup perulangan while
                                }
                                ?>
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div>
    </main>
</div>

<!-- Memanggil jQuery yang diperlukan untuk inisialisasi DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Memanggil JavaScript Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Memanggil JavaScript utama DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<!-- Menghubungkan DataTables dengan tampilan Bootstrap 5 -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
// Menjalankan kode setelah dokumen HTML selesai dimuat
$(document).ready(function () {

    // Mengaktifkan fitur DataTables pada tabel tahun ajaran
    $('#tabelTahunAjaran').DataTable({

        // Mengubah teks bawaan DataTables menjadi bahasa Indonesia
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data tahun ajaran",

            // Mengatur teks tombol navigasi halaman tabel
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },

        // Menampilkan 10 data pada setiap halaman
        pageLength: 10,

        // Menyediakan pilihan jumlah data yang ditampilkan
        lengthMenu: [5, 10, 25, 50, 100],

        // Mengurutkan data berdasarkan kolom kedua secara menurun
        order: [[1, 'desc']],

        // Mengaktifkan gulir horizontal jika tabel terlalu lebar
        scrollX: true

    });

});
</script>

</body>
</html>
