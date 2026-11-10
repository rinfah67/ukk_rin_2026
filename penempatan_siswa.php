
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
// AMBIL DATA PENEMPATAN SISWA
// =====================================
$sql = "SELECT
            ks.id,
            ks.siswa_id,
            ks.kelas_id,
            ks.tahun_ajaran_id,
            ks.tanggal_mulai,
            ks.tanggal_selesai,
            ks.status_aktif,
            s.nis,
            s.nama AS nama_siswa,
            k.nama AS nama_kelas,
            k.tingkat,
            k.jurusan,
            ta.nama AS tahun_ajaran
        FROM t_kelas_siswa ks
        LEFT JOIN t_siswa s ON ks.siswa_id = s.id
        LEFT JOIN t_kelas k ON ks.kelas_id = k.id
        LEFT JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
        ORDER BY ks.id DESC";

$query = mysqli_query($koneksi, $sql);

// =====================================
// AMBIL DATA TAHUN AJARAN AKTIF
// =====================================
$query_tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY id DESC
");

if (!$query_tahun) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

// =====================================
// AMBIL SISWA AKTIF YANG BELUM DITEMPATKAN
// =====================================
$query_siswa = mysqli_query($koneksi, "
    SELECT s.id, s.nis, s.nama
    FROM t_siswa s
    WHERE s.status_aktif = 1
    AND NOT EXISTS (
        SELECT 1
        FROM t_kelas_siswa ks
        WHERE ks.siswa_id = s.id
    )
    ORDER BY s.nama ASC
");

if (!$query_siswa) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

// =====================================
// AMBIL DATA KELAS AKTIF
// =====================================
$query_kelas = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

if (!$query_kelas) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

// =====================================
// NOTIFIKASI
// =====================================
$error_query = $query ? '' : mysqli_error($koneksi);

$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

// =====================================
// HITUNG DATA
// =====================================
$total_penempatan = $query ? mysqli_num_rows($query) : 0;
$total_belum_ditempatkan = mysqli_num_rows($query_siswa);

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

    <title>Penempatan Siswa - Pelanggaran Siswa</title>

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

        /* Statistik */
        .card-statistik {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .icon-statistik {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 20px;
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
                        <i class="fa-solid fa-users text-primary"></i>
                        Penempatan Siswa
                    </h2>

                    <p class="text-muted mb-0">
                        Kelola penempatan siswa berdasarkan kelas dan tahun ajaran.
                    </p>
                </div>

                <div class="d-flex gap-2">

                    <a href="dashboard.php"
                       class="btn btn-white text-secondary border fw-medium px-3 shadow-sm"
                       style="border-radius: 10px;">

                        <i class="fa-solid fa-arrow-left me-1"></i>
                        Dashboard

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

            <!-- STATISTIK -->
            <div class="row g-3 mb-4">

                <div class="col-md-6">

                    <div class="card-statistik p-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="icon-statistik bg-primary-subtle text-primary">
                                <i class="fa-solid fa-users"></i>
                            </div>

                            <div>
                                <h4 class="fw-bold mb-0">
                                    <?= $total_penempatan; ?>
                                </h4>

                                <small class="text-muted">
                                    Total Riwayat Penempatan
                                </small>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="card-statistik p-3">

                        <div class="d-flex align-items-center gap-3">

                            <div class="icon-statistik bg-warning-subtle text-warning">
                                <i class="fa-solid fa-user-clock"></i>
                            </div>

                            <div>
                                <h4 class="fw-bold mb-0">
                                    <?= $total_belum_ditempatkan; ?>
                                </h4>

                                <small class="text-muted">
                                    Siswa Belum Ditempatkan
                                </small>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CARD FORM TAMBAH -->
            <div class="card card-custom p-4 mb-4">

                <div class="border-bottom pb-3 mb-4">

                    <h5 class="fw-bold text-dark mb-1">
                        Tambah Penempatan Siswa
                    </h5>

                    <p class="text-muted small mb-0">
                        Isi data siswa, kelas, dan tahun ajaran untuk melakukan penempatan.
                    </p>

                </div>

                <form action="proses_simpan_penempatan.php" method="POST">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?= e($_SESSION['csrf_token'] ?? ''); ?>">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Tahun Ajaran
                            </label>

                            <select name="tahun_ajaran_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Pilih Tahun Ajaran --
                                </option>

                                <?php while ($tahun = mysqli_fetch_assoc($query_tahun)) : ?>

                                    <option value="<?= (int)$tahun['id']; ?>">
                                        <?= e($tahun['nama']); ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Siswa
                            </label>

                            <select name="siswa_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Pilih Siswa --
                                </option>

                                <?php while ($siswa = mysqli_fetch_assoc($query_siswa)) : ?>

                                    <option value="<?= (int)$siswa['id']; ?>">
                                        <?= e($siswa['nis']); ?> -
                                        <?= e($siswa['nama']); ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Kelas
                            </label>

                            <select name="kelas_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    -- Pilih Kelas --
                                </option>

                                <?php while ($kelas = mysqli_fetch_assoc($query_kelas)) : ?>

                                    <option value="<?= (int)$kelas['id']; ?>">
                                        <?= e($kelas['nama']); ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Tanggal Mulai
                            </label>

                            <input type="date"
                                   name="tanggal_mulai"
                                   class="form-control"
                                   required>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Tanggal Selesai
                            </label>

                            <input type="date"
                                   name="tanggal_selesai"
                                   class="form-control">

                            <small class="text-muted">
                                Kosongkan jika penempatan masih berlangsung.
                            </small>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Status
                            </label>

                            <select name="status_aktif"
                                    class="form-select"
                                    required>

                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>

                            </select>

                        </div>

                    </div>

                    <button type="submit"
                            class="btn btn-primary px-4"
                            style="background-color: #2563eb;">

                        <i class="fa-solid fa-save me-1"></i>
                        Simpan Penempatan

                    </button>

                </form>

            </div>

            <!-- CARD TABEL -->
            <div class="card card-custom p-4">

                <div class="border-bottom pb-3 mb-4">

                    <h5 class="fw-bold text-dark mb-1">
                        Daftar Penempatan Siswa
                    </h5>

                    <p class="text-muted small mb-0">
                        Cari, urutkan, dan kelola data penempatan siswa yang terdaftar.
                    </p>

                </div>

                <!-- TABEL -->
                <div class="table-responsive">

                    <table id="tabelPenempatan"
                           class="table table-hover align-middle w-100">

                        <thead class="table-custom-header">

                            <tr>

                                <th class="text-center" style="width: 40px;">
                                    No
                                </th>

                                <th>NIS</th>

                                <th>Nama Siswa</th>

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

                                    $id_val = (int)$data['id'];
                                    $status = (int)($data['status_aktif'] ?? 0);
                            ?>

                            <tr>

                                <td class="text-center text-muted fw-medium">
                                    <?= $no++; ?>
                                </td>

                                <td class="fw-medium text-dark">
                                    <?= e($data['nis'] ?? '-'); ?>
                                </td>

                                <td class="fw-semibold text-dark">
                                    <?= e($data['nama_siswa'] ?? '-'); ?>
                                </td>

                                <td class="fw-medium">
                                    <?= e($data['nama_kelas'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= e($data['tingkat'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= e($data['jurusan'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= e($data['tahun_ajaran'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= e(formatTanggal($data['tanggal_mulai'] ?? '')); ?>
                                </td>

                                <td>
                                    <?= e(formatTanggal($data['tanggal_selesai'] ?? '')); ?>
                                </td>

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

                                <td class="text-center">

                                    <div class="d-flex justify-content-center gap-2">

                                        <a href="edit_penempatan_siswa.php?id=<?= $id_val; ?>"
                                           class="btn-action-edit text-decoration-none btn-sm"
                                           title="Edit Data">

                                            <i class="fa-solid fa-pen-to-square"></i>

                                        </a>

                                        <a href="hapus_penempatan_siswa.php?id=<?= $id_val; ?>"
                                           class="btn-action-delete text-decoration-none btn-sm"
                                           title="Hapus Data"
                                           onclick="return confirm('Yakin ingin menghapus data penempatan siswa ini?')">

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

        $('#tabelPenempatan').DataTable({

            "pageLength": 10,

            "lengthMenu": [5, 10, 25, 50, 100],

            "scrollX": true,

            "language": {

                "lengthMenu": "Tampilkan _MENU_ data",

                "zeroRecords": "Data penempatan siswa tidak ditemukan",

                "emptyTable": "Belum ada data penempatan siswa",

                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",

                "infoEmpty": "Menampilkan 0 data",

                "infoFiltered": "(difilter dari _MAX_ total data)",

                "search": "Cari:",

                "searchPlaceholder": "Cari penempatan siswa...",

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
