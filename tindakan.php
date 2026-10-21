
<?php
// tindakan.php

// Memeriksa session login
include 'includes/cek_session.php';

// Koneksi database
include 'config/koneksi.php';

// Variabel pesan error
$pesan_error = '';

// Proses menyimpan tindakan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Mengambil data dari form
    $id = (int) ($_POST['id'] ?? 0);
    $tindakan = trim($_POST['tindakan'] ?? '');

    // Memeriksa data yang dikirim
    if ($id <= 0) {
        $pesan_error = 'ID pelanggaran tidak valid.';
    } elseif ($tindakan === '') {
        $pesan_error = 'Tindakan tidak boleh kosong.';
    } else {

        // Memperbarui tindakan pada tabel t_pelanggaran_siswa
        $sql_update = "UPDATE t_pelanggaran_siswa
                       SET tindakan = ?
                       WHERE id = ?";

        $stmt = mysqli_prepare($koneksi, $sql_update);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $tindakan,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                // Kembali ke halaman tindakan setelah berhasil
                header('Location: tindakan.php?pesan=berhasil');
                exit;

            } else {

                $pesan_error = mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);

            }

        } else {

            $pesan_error = mysqli_error($koneksi);

        }
    }
}

// Mengambil seluruh data pelanggaran siswa
$sql = "SELECT
            id,
            nama_siswa,
            nama_kelas,
            nama_pelanggaran,
            tanggal,
            keterangan,
            tindakan
        FROM t_pelanggaran_siswa
        ORDER BY tanggal DESC, id DESC";

$hasil = mysqli_query($koneksi, $sql);

// Memeriksa apakah query berhasil
if (!$hasil) {
    die('Gagal mengambil data: ' . htmlspecialchars(mysqli_error($koneksi)));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tindakan Pelanggaran Siswa</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link
        href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten utama -->
        <main class="col-9 offset-3 col-lg-10 offset-lg-2 p-4">

            <h2 class="mb-2">Tindakan Pelanggaran Siswa</h2>

            <p class="text-secondary">
                Kelola tindakan untuk setiap pelanggaran siswa.
            </p>

            <!-- Notifikasi berhasil -->
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'berhasil') { ?>
                <div class="alert alert-success alert-dismissible fade show">
                    Tindakan berhasil disimpan.

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- Notifikasi error -->
            <?php if ($pesan_error !== '') { ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    Gagal menyimpan tindakan:
                    <?= htmlspecialchars($pesan_error); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- Tabel tindakan -->
            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">

                        <table id="tabelTindakan"
                               class="table table-striped table-bordered table-hover align-middle w-100">

                            <thead class="table-primary">
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Pelanggaran</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Tindakan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php
                            $no = 1;

                            while ($row = mysqli_fetch_assoc($hasil)) {
                            ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_pelanggaran'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?php
                                        if (!empty($row['tanggal'])) {
                                            echo htmlspecialchars(
                                                date('d-m-Y', strtotime($row['tanggal']))
                                            );
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['keterangan'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <!-- Form untuk mengisi tindakan -->
                                        <form method="POST" action="tindakan.php">

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $row['id']; ?>">

                                            <textarea
                                                name="tindakan"
                                                class="form-control"
                                                rows="2"
                                                placeholder="Masukkan tindakan..."
                                                required><?= htmlspecialchars($row['tindakan'] ?? ''); ?></textarea>
                                    </td>

                                    <td>
                                            <!-- Tombol menyimpan tindakan -->
                                            <button
                                                type="submit"
                                                class="btn btn-primary btn-sm">
                                                Simpan
                                            </button>

                                        </form>
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
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- Pengaturan DataTables -->
<script>
$(document).ready(function () {
    $('#tabelTindakan').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data pelanggaran",
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
