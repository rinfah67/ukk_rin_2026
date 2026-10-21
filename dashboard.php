
<?php
// dashboard.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Menghitung jumlah siswa
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_siswa"
);

if (!$query_siswa) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

$total_siswa = mysqli_fetch_assoc($query_siswa)['total'];


// Menghitung jumlah guru yang memiliki akun ber-role guru
$query_guru = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM t_guru g
     INNER JOIN t_users u ON g.user_id = u.id
     WHERE u.role = 'guru'"
);

if (!$query_guru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

$total_guru = mysqli_fetch_assoc($query_guru)['total'];


// Menghitung jumlah kelas
$query_kelas = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_kelas"
);

if (!$query_kelas) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

$total_kelas = mysqli_fetch_assoc($query_kelas)['total'];


// Menghitung jumlah tahun ajaran
$query_tahun = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_tahun_ajaran"
);

if (!$query_tahun) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$total_tahun = mysqli_fetch_assoc($query_tahun)['total'];


// Menghitung jumlah catatan pelanggaran
$query_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa"
);

if (!$query_pelanggaran) {
    die("Gagal mengambil data pelanggaran: " . mysqli_error($koneksi));
}

$total_pelanggaran = mysqli_fetch_assoc($query_pelanggaran)['total'];


// Menghitung total poin pelanggaran
$query_poin = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(poin), 0) AS total
     FROM t_pelanggaran_siswa"
);

if (!$query_poin) {
    die("Gagal menghitung poin pelanggaran: " . mysqli_error($koneksi));
}

$total_poin = mysqli_fetch_assoc($query_poin)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

<div class="container-fluid">
    <?php include "layout/sidebar.php"; ?>

    <main class="row">
        <div class="col-9 offset-3 col-lg-10 offset-lg-2 p-4">

            <h2>Dashboard</h2>

            <p>
                Selamat datang,
                <strong>
                    <?= htmlspecialchars(
                        $_SESSION['name'] ?? 'Pengguna'
                    ); ?>
                </strong>
                di halaman dashboard.
            </p>

            <div class="row">

                <!-- CARD DATA SISWA -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-primary h-100">
                        <div class="card-body">
                            <h5 class="card-title">Data Siswa</h5>

                            <h2 class="text-primary">
                                <?= number_format((int) $total_siswa); ?>
                            </h2>

                            <a href="kelola_siswa.php"
                               class="btn btn-primary btn-sm">
                                Lihat Data Siswa
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD DATA GURU -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-success h-100">
                        <div class="card-body">
                            <h5 class="card-title">Data Guru</h5>

                            <h2 class="text-success">
                                <?= number_format((int) $total_guru); ?>
                            </h2>

                            <a href="kelola_guru.php"
                               class="btn btn-success btn-sm">
                                Lihat Data Guru
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD DATA KELAS -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-warning h-100">
                        <div class="card-body">
                            <h5 class="card-title">Kelas</h5>

                            <h2 class="text-warning">
                                <?= number_format((int) $total_kelas); ?>
                            </h2>

                            <a href="kelola_kelas.php"
                               class="btn btn-warning btn-sm">
                                Lihat Data Kelas
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD TAHUN AJARAN -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-info h-100">
                        <div class="card-body">
                            <h5 class="card-title">Tahun Ajaran</h5>

                            <h2 class="text-info">
                                <?= number_format((int) $total_tahun); ?>
                            </h2>

                            <a href="kelola_tahun_ajaran.php"
                               class="btn btn-info btn-sm">
                                Lihat Tahun Ajaran
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD PELANGGARAN -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-danger h-100">
                        <div class="card-body">
                            <h5 class="card-title">Catatan Pelanggaran</h5>

                            <h2 class="text-danger">
                                <?= number_format((int) $total_pelanggaran); ?>
                            </h2>

                            <a href="tindakan.php"
                               class="btn btn-danger btn-sm">
                                Lihat Pelanggaran
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CARD TOTAL POIN -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-secondary h-100">
                        <div class="card-body">
                            <h5 class="card-title">Total Poin Pelanggaran</h5>

                            <h2 class="text-secondary">
                                <?= number_format((int) $total_poin); ?>
                            </h2>

                            <a href="rekap_poin.php"
                               class="btn btn-secondary btn-sm">
                                Lihat Rekap Poin
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<!-- Bootstrap 5 JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
