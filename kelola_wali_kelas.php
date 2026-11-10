
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// =====================================
// CEK LOGIN
// =====================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// =====================================
// CEK HAK AKSES ADMIN
// =====================================
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// =====================================
// AMBIL DATA WALI KELAS
// =====================================
$sql = "SELECT
            wk.id,
            wk.guru_id,
            wk.kelas_id,
            wk.tahun_ajaran_id,
            wk.tanggal_mulai,
            wk.tanggal_selesai,
            wk.status_aktif,
            g.nip,
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

$query = mysqli_query($koneksi, $sql);

// =====================================
// NOTIFIKASI
// =====================================
$error_query = $query ? '' : mysqli_error($koneksi);

$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

// =====================================
// FUNGSI FORMAT TANGGAL
// =====================================
function formatTanggal($tanggal)
{
    if (
        empty($tanggal) ||
        $tanggal == '0000-00-00' ||
        $tanggal == '9999-12-31'
    ) {
        return '-';
    }

    return date('d-m-Y', strtotime($tanggal));
}

// =====================================
// FUNGSI ESCAPE HTML
// =====================================
function e($nilai)
{
    return htmlspecialchars(
        (string)($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Wali Kelas - Pelanggaran Siswa</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Styling Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 4px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .sidebar .nav-link.text-danger:hover {
            background-color: #ef4444;
            color: #fff !important;
        }

        /* Badge R dalam Lingkaran */
        .circle-r {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border: 2px solid currentColor;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
        }

        /* Card Modern */
        .card-custom {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        /* Header Tabel */
        .table-custom-header th {
            background-color: #eff6ff !important;
            color: #1e40af !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #dbeafe !important;
            padding: 14px 12px;
            white-space: nowrap;
        }

        /* Tombol Edit */
        .btn-action-edit {
            background-color: #fef3c7;
            color: #d97706;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-action-edit:hover {
            background-color: #d97706;
            color: #ffffff;
        }

        /* Tombol Hapus */
        .btn-action-delete {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            border-radius: 6px;
            padding: 6px 12px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-action-delete:hover {
            background-color: #dc2626;
            color: #ffffff;
        }

        /* DataTables */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 7px 12px;
            margin-left: 8px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 5px 10px;
        }

        .table-responsive {
            width: 100%;
        }

        .table td {
            vertical-align: middle;
            white-space: nowrap;
        }
    </style>
</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <!-- HEADER HALAMAN -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fa-solid fa-chalkboard-user text-primary"></i>
                        Kelola Data Wali Kelas
                    </h2>

                    <p class="text-muted mb-0">
                        Kelola penugasan guru sebagai wali kelas berdasarkan kelas dan tahun ajaran.
                    </p>
                </div>

                <div class="d-flex gap-2">

                    <a href="dashboard.php"
                       class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                       style="border-radius: 10px;">

                        <i class="fa-solid fa-arrow-left me-1"></i>
                        Dashboard

                    </a>

                    <a href="tambah_wali_kelas.php"
                       class="btn btn-primary fw-medium px-4 shadow-sm"
                       style="border-radius: 10px; background-color: #2563eb;">

                        <i class="fa-solid fa-plus me-1"></i>
                        Tambah Wali Kelas

                    </a>

                </div>

            </div>

            <!-- NOTIFIKASI SUKSES -->
            <?php if ($pesan_sukses !== '') : ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">

                    <i class="fa-solid fa-circle-check me-2"></i>

                    <?= e($pesan_sukses); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>

            <!-- NOTIFIKASI ERROR -->
            <?php if ($pesan_error !== '') : ?>

                <div class="alert alert-danger alert-dismissible fade show" role="alert">

                    <i class="fa-solid fa-circle-exclamation me-2"></i>

                    <?= e($pesan_error); ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

            <?php endif; ?>

            <!-- ERROR DATABASE -->
            <?php if ($error_query !== '') : ?>

                <div class="alert alert-danger">

                    <strong>
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Query database gagal:
                    </strong>

                    <?= e($error_query); ?>

                </div>

            <?php endif; ?>

            <!-- CARD TABEL -->
            <div class="card card-custom p-4">

                <div class="border-bottom pb-3 mb-4">

                    <h5 class="fw-bold text-dark mb-1">
                        Daftar Data Wali Kelas
                    </h5>

                    <p class="text-muted small mb-0">
                        Cari, urutkan, dan kelola data penugasan wali kelas yang terdaftar.
                    </p>

                </div>

                <!-- TABEL -->
                <div class="table-responsive">

                    <table id="tabelWaliKelas"
                           class="table table-hover align-middle w-100">

                        <thead class="table-custom-header">

                            <tr>

                                <th class="text-center" style="width: 40px;">
                                    No
                                </th>

                                <th>NIP</th>

                                <th>Nama Guru</th>

                                <th>Kelas</th>

                                <th>Tingkat</th>

                                <th>Jurusan</th>

                                <th>Tahun Ajaran</th>

                                <th>Tanggal Mulai</th>

                                <th>Tanggal Selesai</th>

                                <th class="text-center">
                                    Status
                                </th>

                                <th class="text-center" style="width: 140px;">
                                    Aksi
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            if ($query && mysqli_num_rows($query) > 0) :

                                while ($data = mysqli_fetch_assoc($query)) :

                                    $id_val = (int) $data['id'];
                                    $status = (int) ($data['status_aktif'] ?? 0);
                            ?>

                            <tr>

                                <!-- NOMOR -->
                                <td class="text-center text-muted fw-medium">
                                    <?= $no++; ?>
                                </td>

                                <!-- NIP -->
                                <td class="fw-medium text-dark">
                                    <?= e($data['nip'] ?? '-'); ?>
                                </td>

                                <!-- NAMA GURU -->
                                <td class="fw-semibold text-dark">
                                    <?= e($data['nama_guru'] ?? '-'); ?>
                                </td>

                                <!-- KELAS -->
                                <td class="fw-medium">
                                    <?= e($data['nama_kelas'] ?? '-'); ?>
                                </td>

                                <!-- TINGKAT -->
                                <td>
                                    <?= e($data['tingkat'] ?? '-'); ?>
                                </td>

                                <!-- JURUSAN -->
                                <td>
                                    <?= e($data['jurusan'] ?? '-'); ?>
                                </td>

                                <!-- TAHUN AJARAN -->
                                <td>
                                    <?= e($data['tahun_ajaran'] ?? '-'); ?>
                                </td>

                                <!-- TANGGAL MULAI -->
                                <td>
                                    <?= e(formatTanggal($data['tanggal_mulai'] ?? '')); ?>
                                </td>

                                <!-- TANGGAL SELESAI -->
                                <td>
                                    <?= e(formatTanggal($data['tanggal_selesai'] ?? '')); ?>
                                </td>

                                <!-- STATUS -->
                                <td class="text-center">

                                    <?php if ($status === 1) : ?>

                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                            Aktif
                                        </span>

                                    <?php else : ?>

                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1">
                                            Tidak Aktif
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- AKSI -->
                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="edit_wali_kelas.php?id=<?= $id_val; ?>"
                                           class="btn-action-edit text-decoration-none btn-sm"
                                           title="Edit Data">

                                            <i class="fa-solid fa-pen-to-square"></i>

                                        </a>

                                        <a href="hapus_wali_kelas.php?id=<?= $id_val; ?>"
                                           class="btn-action-delete text-decoration-none btn-sm"
                                           title="Hapus Data"
                                           onclick="return confirm('Yakin ingin menghapus data wali kelas ini?')">

                                            <i class="fa-solid fa-trash"></i>

                                        </a>

                                    </div>

                                </td>

                            </tr>

                            <?php
                                endwhile;

                            endif;
                            ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </main>

    </div>

</div>

<!-- JQUERY -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DATATABLES JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- INISIALISASI DATATABLES -->
<script>
    $(document).ready(function() {

        $('#tabelWaliKelas').DataTable({

            "pageLength": 10,

            "lengthMenu": [5, 10, 25, 50, 100],

            "scrollX": true,

            "language": {

                "lengthMenu": "Tampilkan _MENU_ data",

                "zeroRecords": "Data wali kelas tidak ditemukan",

                "emptyTable": "Belum ada data wali kelas",

                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",

                "infoEmpty": "Menampilkan 0 data",

                "infoFiltered": "(difilter dari _MAX_ total data)",

                "search": "Cari:",

                "searchPlaceholder": "Cari wali kelas...",

                "paginate": {

                    "first": "Pertama",

                    "last": "Terakhir",

                    "next": "Selanjutnya",

                    "previous": "Sebelumnya"

                }

            }

        });

    });
</script>

</body>
</html>
