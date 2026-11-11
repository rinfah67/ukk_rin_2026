
<?php
// tambah_wali_kelas.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// =====================================
// CEK HAK AKSES
// =====================================
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// =====================================
// MENGAMBIL TAHUN AJARAN AKTIF
// =====================================
$query_tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY id DESC
");

// =====================================
// MENGAMBIL KELAS YANG BELUM MEMILIKI
// WALI KELAS AKTIF
// =====================================
$query_kelas = mysqli_query($koneksi, "
    SELECT
        k.id,
        k.nama,
        k.tingkat,
        k.jurusan
    FROM t_kelas k
    WHERE k.status_aktif = 1
    AND NOT EXISTS (
        SELECT 1
        FROM t_wali_kelas wk
        WHERE wk.kelas_id = k.id
        AND wk.status_aktif = 1
        AND wk.tahun_ajaran_id IN (
            SELECT id
            FROM t_tahun_ajaran
            WHERE status_aktif = 1
        )
    )
    ORDER BY k.tingkat ASC, k.nama ASC
");

// =====================================
// MENGAMBIL GURU YANG BELUM MENJADI
// WALI KELAS AKTIF
// =====================================
$query_guru = mysqli_query($koneksi, "
    SELECT
        g.id,
        g.nip,
        g.nama
    FROM t_guru g
    WHERE g.status_aktif = 1
    AND NOT EXISTS (
        SELECT 1
        FROM t_wali_kelas wk
        WHERE wk.guru_id = g.id
        AND wk.status_aktif = 1
    )
    ORDER BY g.nama ASC
");

// =====================================
// CEK QUERY
// =====================================
if (!$query_tahun || !$query_kelas || !$query_guru) {
    die("Query gagal: " . mysqli_error($koneksi));
}

// =====================================
// MENGHITUNG DATA DROPDOWN
// =====================================
$jumlah_tahun = mysqli_num_rows($query_tahun);
$jumlah_kelas = mysqli_num_rows($query_kelas);
$jumlah_guru = mysqli_num_rows($query_guru);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Wali Kelas - SI PELANG</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
        }

        .sidebar {
            width: 250px !important;
            min-width: 250px;
            flex: 0 0 250px !important;
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar .brand-title {
            color: #ffffff;
            font-size: 20px;
            font-weight: 800;
            white-space: nowrap;
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: 0.2s;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .sidebar .nav-link i {
            width: 20px;
            min-width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #2563eb;
            color: #ffffff;
        }

        .sidebar .nav-link.text-danger:hover {
            background-color: #dc2626;
            color: #ffffff !important;
        }

        .sidebar .menu-title {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 20px 12px 10px;
            letter-spacing: 1px;
        }

        .page-title {
            color: #1e293b;
            font-weight: 800;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
        }

        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 20px 25px;
        }

        .card-header-custom p {
            color: #dbeafe;
            font-size: 13px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 11px 14px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .input-icon {
            color: #2563eb;
            margin-right: 5px;
        }

        .btn-primary-custom {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 10px 22px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-primary-custom:hover {
            background: #1d4ed8;
            color: white;
        }

        .btn-back {
            background: white;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-cancel {
            border-radius: 9px;
            padding: 10px 22px;
            font-weight: 600;
        }

        .form-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            border-radius: 10px;
            padding: 13px 16px;
            font-size: 13px;
        }

        main {
            min-width: 0;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                width: 250px !important;
                min-width: 250px;
                flex: 0 0 250px !important;
            }
        }

        @media (max-width: 576px) {
            main {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .header-page {
                align-items: flex-start !important;
                gap: 12px;
            }

            .btn-back {
                white-space: nowrap;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <?php
        // Menampilkan sidebar
        $current_page = basename($_SERVER['PHP_SELF']);
        include 'layout/sidebar.php';
        ?>

        <main class="col px-md-4 py-4">

            <!-- HEADER HALAMAN -->
            <div class="d-flex justify-content-between align-items-center mb-4 header-page">

                <div>
                    <h3 class="page-title mb-1">
                        <i class="fa-solid fa-user-tie text-primary me-2"></i>
                        Tambah Wali Kelas
                    </h3>

                    <p class="page-subtitle mb-0">
                        Tambahkan penugasan guru sebagai wali kelas.
                    </p>
                </div>

                <a href="kelola_wali_kelas.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Kembali
                </a>

            </div>

            <!-- FORM TAMBAH WALI KELAS -->
            <div class="row justify-content-center">
                <div class="col-xl-9 col-lg-10">

                    <div class="card-custom">

                        <!-- HEADER CARD -->
                        <div class="card-header-custom">
                            <h5 class="fw-bold mb-1">
                                <i class="fa-solid fa-clipboard-list me-2"></i>
                                Formulir Penugasan Wali Kelas
                            </h5>

                            <p class="mb-0">
                                Lengkapi informasi penugasan wali kelas dengan benar.
                            </p>
                        </div>

                        <div class="p-4 p-md-5">

                            <!-- INFORMASI -->
                            <div class="form-info mb-4">
                                <i class="fa-solid fa-circle-info me-2"></i>
                                Pilih tahun ajaran aktif, kelas yang belum memiliki wali kelas,
                                dan guru yang belum mendapatkan penugasan wali kelas aktif.
                            </div>

                            <!-- PERINGATAN JIKA DATA KOSONG -->
                            <?php if ($jumlah_tahun == 0) : ?>
                                <div class="alert alert-warning">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    Belum ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.
                                </div>
                            <?php endif; ?>

                            <?php if ($jumlah_kelas == 0) : ?>
                                <div class="alert alert-warning">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    Tidak ada kelas yang tersedia atau semua kelas sudah memiliki wali kelas aktif.
                                </div>
                            <?php endif; ?>

                            <?php if ($jumlah_guru == 0) : ?>
                                <div class="alert alert-warning">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                    Tidak ada guru yang tersedia atau semua guru sudah menjadi wali kelas aktif.
                                </div>
                            <?php endif; ?>

                            <form action="proses_tambah_wali_kelas.php" method="POST">

                                <div class="row g-4">

                                    <!-- TAHUN AJARAN -->
                                    <div class="col-12">
                                        <label for="tahun_ajaran_id" class="form-label">
                                            <i class="fa-solid fa-calendar input-icon"></i>
                                            Tahun Ajaran <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            class="form-select"
                                            id="tahun_ajaran_id"
                                            name="tahun_ajaran_id"
                                            required
                                        >
                                            <option value="" selected disabled>
                                                -- Pilih Tahun Ajaran --
                                            </option>

                                            <?php while ($tahun = mysqli_fetch_assoc($query_tahun)) : ?>
                                                <option value="<?= (int) $tahun['id']; ?>">
                                                    <?= htmlspecialchars($tahun['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <!-- GURU -->
                                    <div class="col-md-6">
                                        <label for="guru_id" class="form-label">
                                            <i class="fa-solid fa-user-tie input-icon"></i>
                                            Guru <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            class="form-select"
                                            id="guru_id"
                                            name="guru_id"
                                            required
                                        >
                                            <option value="" selected disabled>
                                                -- Pilih Guru --
                                            </option>

                                            <?php while ($guru = mysqli_fetch_assoc($query_guru)) : ?>
                                                <option value="<?= (int) $guru['id']; ?>">
                                                    <?= htmlspecialchars($guru['nip'], ENT_QUOTES, 'UTF-8'); ?>
                                                    -
                                                    <?= htmlspecialchars($guru['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <!-- KELAS -->
                                    <div class="col-md-6">
                                        <label for="kelas_id" class="form-label">
                                            <i class="fa-solid fa-school input-icon"></i>
                                            Kelas <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            class="form-select"
                                            id="kelas_id"
                                            name="kelas_id"
                                            required
                                        >
                                            <option value="" selected disabled>
                                                -- Pilih Kelas --
                                            </option>

                                            <?php while ($kelas = mysqli_fetch_assoc($query_kelas)) : ?>
                                                <option value="<?= (int) $kelas['id']; ?>">
                                                    <?= htmlspecialchars($kelas['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                                    (<?= htmlspecialchars($kelas['tingkat'], ENT_QUOTES, 'UTF-8'); ?>)
                                                    -
                                                    <?= htmlspecialchars($kelas['jurusan'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <!-- TANGGAL MULAI -->
                                    <div class="col-md-6">
                                        <label for="tanggal_mulai" class="form-label">
                                            <i class="fa-solid fa-calendar-check input-icon"></i>
                                            Tanggal Mulai <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            id="tanggal_mulai"
                                            name="tanggal_mulai"
                                            required
                                        >
                                    </div>

                                    <!-- TANGGAL SELESAI -->
                                    <div class="col-md-6">
                                        <label for="tanggal_selesai" class="form-label">
                                            <i class="fa-solid fa-calendar-xmark input-icon"></i>
                                            Tanggal Selesai
                                        </label>

                                        <input
                                            type="date"
                                            class="form-control"
                                            id="tanggal_selesai"
                                            name="tanggal_selesai"
                                        >

                                        <small class="text-muted">
                                            Kosongkan jika penugasan masih berlangsung.
                                        </small>
                                    </div>

                                    <!-- STATUS -->
                                    <div class="col-md-6">
                                        <label for="status_aktif" class="form-label">
                                            <i class="fa-solid fa-toggle-on input-icon"></i>
                                            Status Penugasan <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            class="form-select"
                                            id="status_aktif"
                                            name="status_aktif"
                                            required
                                        >
                                            <option value="1" selected>Aktif</option>
                                            <option value="0">Tidak Aktif</option>
                                        </select>
                                    </div>

                                </div>

                                <!-- TOMBOL -->
                                <div class="d-flex justify-content-end gap-2 mt-5 pt-4 border-top">

                                    <a href="kelola_wali_kelas.php"
                                       class="btn btn-outline-secondary btn-cancel">
                                        <i class="fa-solid fa-xmark me-1"></i>
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        name="simpan"
                                        class="btn-primary-custom"
                                        <?= ($jumlah_tahun == 0 || $jumlah_kelas == 0 || $jumlah_guru == 0) ? 'disabled' : ''; ?>
                                    >
                                        <i class="fa-solid fa-floppy-disk me-1"></i>
                                        Simpan Data
                                    </button>

                                </div>

                            </form>

                        </div>
                    </div>

                </div>
            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Validasi tanggal penugasan
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalSelesai = document.getElementById('tanggal_selesai');

    tanggalMulai.addEventListener('change', function () {
        tanggalSelesai.min = this.value;

        if (tanggalSelesai.value && tanggalSelesai.value < this.value) {
            tanggalSelesai.value = '';
        }
    });

    tanggalSelesai.addEventListener('change', function () {
        if (
            tanggalMulai.value &&
            this.value &&
            this.value < tanggalMulai.value
        ) {
            alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
            this.value = '';
        }
    });
</script>

</body>
</html>
