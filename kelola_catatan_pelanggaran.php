
<?php
// kelola_catatan_pelanggaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// ============================================
// CEK HAK AKSES
// ============================================

if (($_SESSION['role'] ?? '') !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

// ============================================
// TOKEN KEAMANAN
// ============================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================
// MENGAMBIL DATA CATATAN PELANGGARAN
// ============================================

$query_catatan = mysqli_query($koneksi, "
    SELECT *
    FROM t_pelanggaran_siswa
    ORDER BY tanggal DESC, id DESC
");

if (!$query_catatan) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catatan Pelanggaran - SI PELANG</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #334155;
        }

        .main-content {
            min-width: 0;
        }

        .page-title {
            font-size: 25px;
            font-weight: 800;
            color: #1e293b;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
        }

        .content-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .table thead th {
            background: #eff6ff;
            color: #1e40af;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 14px 12px;
            white-space: nowrap;
        }

        .table tbody td {
            font-size: 13px;
            vertical-align: middle;
            padding: 12px;
        }

        .btn {
            font-size: 12px;
            font-weight: 600;
        }

        .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 10px;
        }

        .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 5px;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col px-md-4 py-4 main-content">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                <div>
                    <h4 class="page-title mb-1">Catatan Pelanggaran</h4>
                    <p class="page-subtitle mb-0">
                        Kelola data catatan pelanggaran siswa.
                    </p>
                </div>

                <a href="tambah_catatan_pelanggaran.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Catatan
                </a>

            </div>

            <!-- PESAN NOTIFIKASI -->

            <?php if (!empty($_SESSION['pesan_sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>

            <!-- TABEL CATATAN -->

            <div class="content-card">

                <div class="table-responsive">

                    <table id="tabelCatatan" class="table table-hover table-bordered align-middle w-100">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Pelanggaran</th>
                                <th>Poin</th>
                                <th>Guru</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;

                            while ($row = mysqli_fetch_assoc($query_catatan)):
                            ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= !empty($row['tanggal'])
                                            ? date('d-m-Y', strtotime($row['tanggal']))
                                            : '-'; ?>
                                    </td>

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
                                        <?= (int) ($row['poin'] ?? 0); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_guru'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?php
                                        $status = $row['status'] ?? 'Belum diproses';

                                        if ($status === 'Selesai') {
                                            echo '<span class="badge bg-success">Selesai</span>';
                                        } elseif ($status === 'Diproses') {
                                            echo '<span class="badge bg-warning text-dark">Diproses</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Belum diproses</span>';
                                        }
                                        ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">

                                            <a href="edit_catatan.php?id=<?= (int) $row['id']; ?>"
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                                Edit
                                            </a>

                                            <form action="hapus_catatan_pelanggaran.php"
                                                  method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus catatan pelanggaran ini?');">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= (int) $row['id']; ?>">

                                                <input type="hidden"
                                                       name="csrf_token"
                                                       value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                    Hapus
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

        </main>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#tabelCatatan').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada catatan pelanggaran",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });
    });
</script>

</body>
</html>
