
<?php
session_start();

include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Mengambil role dan ID pengguna
$role = $_SESSION['role'] ?? '';
$id_user = (int) ($_SESSION['id_user'] ?? 0);

// Hanya admin dan guru yang boleh mengakses
if ($role !== 'admin' && $role !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

$is_walikelas = false;
$id_guru = 0;

// Memeriksa apakah guru adalah wali kelas aktif
if ($role === 'guru') {
    $stmt_guru = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_guru WHERE user_id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt_guru, "i", $id_user);
    mysqli_stmt_execute($stmt_guru);
    $hasil_guru = mysqli_stmt_get_result($stmt_guru);
    $data_guru = mysqli_fetch_assoc($hasil_guru);

    if ($data_guru) {
        $id_guru = (int) $data_guru['id'];

        $stmt_wali = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM t_wali_kelas
             WHERE guru_id = ?
               AND status_aktif = 1
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt_wali, "i", $id_guru);
        mysqli_stmt_execute($stmt_wali);
        $hasil_wali = mysqli_stmt_get_result($stmt_wali);

        if (mysqli_num_rows($hasil_wali) > 0) {
            $is_walikelas = true;
        }
    }

    // Guru yang bukan wali kelas tidak boleh membuka laporan
    if (!$is_walikelas) {
        echo "<script>
            alert('Halaman laporan hanya dapat diakses oleh wali kelas!');
            window.location.href = 'dashboard.php';
        </script>";
        exit;
    }
}

// Mengambil laporan berdasarkan hak akses
if ($role === 'admin') {
    // Admin melihat seluruh laporan
    $sql = "SELECT *
            FROM t_pelanggaran_siswa
            ORDER BY tanggal DESC, id DESC";

    $query = mysqli_query($koneksi, $sql);
} else {
    // Wali kelas melihat laporan siswa yang ditempatkan
    // pada kelas dan tahun ajaran yang menjadi tanggung jawabnya
    $sql = "SELECT DISTINCT p.*
            FROM t_pelanggaran_siswa p
            INNER JOIN t_kelas_siswa ks
                ON ks.siswa_id = p.siswa_id
            INNER JOIN t_wali_kelas wk
                ON wk.kelas_id = ks.kelas_id
                AND wk.tahun_ajaran_id = ks.tahun_ajaran_id
            WHERE wk.guru_id = ?
              AND wk.status_aktif = 1
              AND ks.status_aktif = 1
            ORDER BY p.tanggal DESC, p.id DESC";

    $stmt_laporan = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt_laporan, "i", $id_guru);
    mysqli_stmt_execute($stmt_laporan);
    $query = mysqli_stmt_get_result($stmt_laporan);
}

// Memeriksa error query
$error_query = $query ? '' : mysqli_error($koneksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laporan Pelanggaran | SI PELANG</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f4f7fb;
        }

        .content-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        }

        .page-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background-color: #cfe2ff;
            color: #0d6efd;
            font-size: 24px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table thead th {
            white-space: nowrap;
        }

        @media print {
            body {
                background: white !important;
            }

            .sidebar,
            aside,
            .no-print,
            .dataTables_wrapper .dt-search,
            .dataTables_wrapper .dt-length,
            .dataTables_wrapper .dt-paging,
            .dataTables_wrapper .dt-info {
                display: none !important;
            }

            main {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .content-card {
                box-shadow: none !important;
            }

            .table {
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 5px !important;
            }
        }
    </style>
</head>

<body>

<?php include 'layout/sidebar.php'; ?>

<main class="col-12 col-lg-10 offset-lg-2 p-3 p-md-4">

    <!-- Judul halaman -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">
                <i class="bi bi-file-earmark-bar-graph"></i>
            </div>

            <div>
                <h3 class="fw-bold mb-1">Laporan Pelanggaran</h3>
                <p class="text-secondary mb-0">
                    Rekap data pelanggaran siswa SI PELANG.
                </p>
            </div>
        </div>

        <div class="d-flex gap-2 no-print">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-house-door me-1"></i>
                Dashboard
            </a>

            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>
                Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Error query -->
    <?php if ($error_query !== ''): ?>
        <div class="alert alert-danger">
            <strong>Gagal mengambil data:</strong>
            <?= htmlspecialchars($error_query); ?>
        </div>
    <?php endif; ?>

    <!-- Informasi akses -->
    <div class="alert alert-info no-print">
        <i class="bi bi-info-circle me-2"></i>
        <?php if ($role === 'admin'): ?>
            Anda melihat seluruh laporan pelanggaran siswa.
        <?php else: ?>
            Anda melihat laporan siswa pada kelas yang menjadi tanggung jawab Anda.
        <?php endif; ?>
    </div>

    <!-- Tabel laporan -->
    <div class="card content-card">

        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-1">
                <i class="bi bi-table text-primary me-2"></i>
                Data Laporan Pelanggaran
            </h5>
            <p class="text-secondary small mb-0">
                Cari dan cetak data laporan sesuai hak akses.
            </p>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="tabelLaporan"
                       class="table table-bordered table-striped table-hover align-middle w-100">

                    <thead class="table-primary">
                        <tr>
                            <th>No.</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Tanggal</th>
                            <th>Poin</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($query && mysqli_num_rows($query) > 0): ?>
                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($query)): ?>
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
                                        echo date(
                                            'd-m-Y',
                                            strtotime($row['tanggal'])
                                        );
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <span class="badge bg-danger">
                                        <?= (int) ($row['poin'] ?? 0); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['tindakan'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php
                                    $status = $row['status'] ?? '-';

                                    if ($status === 'Selesai') {
                                        echo '<span class="badge bg-success">Selesai</span>';
                                    } elseif ($status === 'Proses') {
                                        echo '<span class="badge bg-warning text-dark">Proses</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">'
                                            . htmlspecialchars($status)
                                            . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>

                </table>

                <?php if ($query && mysqli_num_rows($query) === 0): ?>
                    <p class="text-center text-secondary mt-3 mb-0">
                        Belum ada data laporan pelanggaran.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-footer bg-white border-0 text-secondary small px-4 pb-3">
            SI PELANG &copy; <?= date('Y'); ?>
        </div>
    </div>

</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tabelLaporan').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [],
            scrollX: true,
            language: {
                search: 'Cari:',
                searchPlaceholder: 'Cari laporan...',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data untuk ditampilkan',
                infoFiltered: '(disaring dari _MAX_ total data)',
                zeroRecords: 'Data laporan tidak ditemukan',
                emptyTable: 'Belum ada data laporan',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            }
        });
    });
</script>

</body>
</html>
