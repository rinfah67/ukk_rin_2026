
<?php
// catat_pelanggaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// ============================================
// CEK HAK AKSES
// ============================================

if (!in_array($_SESSION['role'] ?? '', ['admin', 'guru'])) {
    header("Location: dashboard.php");
    exit;
}

// ============================================
// DATA GURU YANG LOGIN
// ============================================

$user_login = (int) ($_SESSION['id_user'] ?? 0);

$stmt_guru = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ?
    AND status_aktif = 1
    LIMIT 1
");

if (!$stmt_guru) {
    die("Query guru gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt_guru, "i", $user_login);
mysqli_stmt_execute($stmt_guru);

$data_guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_guru));
mysqli_stmt_close($stmt_guru);

$guru_login = (int) ($data_guru['id'] ?? 0);
$nama_guru_login = $data_guru['nama'] ?? 'Guru tidak ditemukan';

// ============================================
// TAHUN AJARAN AKTIF
// ============================================

$query_tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

if (!$query_tahun) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$tahun = mysqli_fetch_assoc($query_tahun);

$tahun_id = (int) ($tahun['id'] ?? 0);
$nama_tahun = $tahun['nama'] ?? 'Tahun ajaran tidak ditemukan';

// ============================================
// DATA SISWA
// ============================================

$siswa = [];

if ($tahun_id > 0) {

    $stmt_siswa = mysqli_prepare($koneksi, "
        SELECT
            ks.id AS penempatan_id,
            s.id AS siswa_id,
            s.nis,
            s.nama,
            k.id AS kelas_id,
            k.nama AS nama_kelas
        FROM t_kelas_siswa ks
        INNER JOIN t_siswa s ON ks.siswa_id = s.id
        INNER JOIN t_kelas k ON ks.kelas_id = k.id
        WHERE ks.tahun_ajaran_id = ?
          AND ks.status_aktif = 1
          AND s.status_aktif = 1
        ORDER BY s.nama ASC
    ");

    if (!$stmt_siswa) {
        die("Query siswa gagal: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt_siswa, "i", $tahun_id);
    mysqli_stmt_execute($stmt_siswa);

    $hasil_siswa = mysqli_stmt_get_result($stmt_siswa);

    while ($row = mysqli_fetch_assoc($hasil_siswa)) {
        $siswa[] = $row;
    }

    mysqli_stmt_close($stmt_siswa);
}

// ============================================
// DATA JENIS PELANGGARAN
// ============================================

$pelanggaran = mysqli_query($koneksi, "
    SELECT id, kode, nama, poin, pelanggaran_kategori_id
    FROM t_pelanggaran
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

if (!$pelanggaran) {
    die("Gagal mengambil data pelanggaran: " . mysqli_error($koneksi));
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catat Pelanggaran - SI PELANG</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
        }

        .sidebar {
            width: 250px !important;
            min-width: 250px;
            flex: 0 0 250px !important;
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .sidebar .brand-title {
            color: #fff;
            font-size: 20px;
            font-weight: 800;
            white-space: nowrap;
        }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 5px;
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
            background: #2563eb;
            color: #fff;
        }

        .sidebar .nav-link.text-danger:hover {
            background: #dc2626;
            color: #fff !important;
        }

        .sidebar .menu-title {
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 20px 12px 10px;
            letter-spacing: 1px;
        }

        main {
            min-width: 0;
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
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
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
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
        }

        .input-icon {
            color: #2563eb;
            margin-right: 5px;
        }

        .btn-primary-custom {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 9px;
            padding: 10px 22px;
            font-weight: 600;
        }

        .btn-primary-custom:hover {
            background: #1d4ed8;
            color: #fff;
        }

        .btn-primary-custom:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .btn-back {
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 9px 16px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .form-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            border-radius: 10px;
            padding: 13px 16px;
            font-size: 13px;
        }

        .form-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 7px;
        }

        .ts-control {
            border: 1px solid #cbd5e1 !important;
            border-radius: 9px !important;
            padding: 11px 14px !important;
            font-size: 14px;
            box-shadow: none !important;
        }

        .ts-wrapper.focus .ts-control {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .15) !important;
        }

        .ts-dropdown {
            border-radius: 9px;
            overflow: hidden;
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

        <?php include 'layout/sidebar.php'; ?>

        <main class="col px-md-4 py-4">

            <div class="d-flex justify-content-between align-items-center mb-4 header-page">
                <div>
                    <h3 class="page-title mb-1">
                        <i class="fa-solid fa-clipboard-list text-primary me-2"></i>
                        Catat Pelanggaran Siswa
                    </h3>

                    <p class="page-subtitle mb-0">
                        Masukkan data pelanggaran siswa sesuai kejadian.
                    </p>
                </div>

                <a href="kelola_catatan_pelanggaran.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-11">

                    <div class="card-custom">

                        <div class="card-header-custom">
                            <h5 class="fw-bold mb-1">
                                <i class="fa-solid fa-file-circle-plus me-2"></i>
                                Formulir Catatan Pelanggaran
                            </h5>

                            <p class="mb-0">
                                Lengkapi informasi pelanggaran siswa dengan benar.
                            </p>
                        </div>

                        <div class="p-4 p-md-5">

                            <?php if (isset($_SESSION['pesan_error'])): ?>
                                <div class="alert alert-danger">
                                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>
                                </div>
                                <?php unset($_SESSION['pesan_error']); ?>
                            <?php endif; ?>

                            <?php if ($tahun_id <= 0): ?>
                                <div class="alert alert-warning">
                                    Belum ada tahun ajaran aktif.
                                </div>
                            <?php endif; ?>

                            <?php if ($guru_login <= 0): ?>
                                <div class="alert alert-warning">
                                    Akun ini belum terhubung dengan data guru aktif.
                                </div>
                            <?php endif; ?>

                            <?php if (count($siswa) <= 0): ?>
                                <div class="alert alert-warning">
                                    Tidak ada siswa dengan penempatan aktif pada tahun ajaran ini.
                                </div>
                            <?php endif; ?>

                            <div class="form-info mb-4">
                                <i class="fa-solid fa-circle-info me-2"></i>
                                Pilih siswa terlebih dahulu. Kelas akan terisi otomatis sesuai penempatan siswa pada tahun ajaran aktif.
                            </div>

                            <form
                                id="formPelanggaran"
                                action="proses_simpan_pelanggaran.php"
                                method="POST">

                                <div class="row g-4">

                                    <!-- SISWA -->
                                    <div class="col-md-6">
                                        <label for="siswa_id" class="form-label">
                                            <i class="fa-solid fa-user input-icon"></i>
                                            Siswa <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="penempatan_id"
                                            id="siswa_id"
                                            class="form-select"
                                            required>

                                            <option value="">Cari nama atau NIS siswa...</option>

                                            <?php foreach ($siswa as $s): ?>
                                                <option
                                                    value="<?= (int) $s['penempatan_id']; ?>"
                                                    data-kelas-id="<?= (int) $s['kelas_id']; ?>"
                                                    data-kelas="<?= htmlspecialchars($s['nama_kelas'], ENT_QUOTES); ?>">

                                                    <?= htmlspecialchars($s['nama']); ?>
                                                    - <?= htmlspecialchars($s['nis']); ?>

                                                </option>
                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <!-- TAHUN AJARAN -->
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <i class="fa-solid fa-calendar input-icon"></i>
                                            Tahun Ajaran
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            value="<?= htmlspecialchars($nama_tahun); ?>"
                                            readonly>

                                        <input
                                            type="hidden"
                                            name="tahun_ajaran_id"
                                            value="<?= $tahun_id; ?>">
                                    </div>

                                    <!-- KELAS -->
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <i class="fa-solid fa-school input-icon"></i>
                                            Kelas
                                        </label>

                                        <input
                                            type="text"
                                            id="nama_kelas"
                                            class="form-control"
                                            placeholder="Kelas akan terisi otomatis"
                                            readonly>

                                        <input
                                            type="hidden"
                                            name="kelas_id"
                                            id="kelas_id">
                                    </div>

                                    <!-- JENIS PELANGGARAN -->
                                    <div class="col-md-6">
                                        <label for="pelanggaran_id" class="form-label">
                                            <i class="fa-solid fa-triangle-exclamation input-icon"></i>
                                            Jenis Pelanggaran <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="pelanggaran_id"
                                            id="pelanggaran_id"
                                            class="form-select"
                                            required>

                                            <option value="">Cari jenis pelanggaran...</option>

                                            <?php while ($p = mysqli_fetch_assoc($pelanggaran)): ?>
                                                <option
                                                    value="<?= (int) $p['id']; ?>"
                                                    data-poin="<?= (int) $p['poin']; ?>"
                                                    data-kategori="<?= (int) $p['pelanggaran_kategori_id']; ?>">

                                                    <?= htmlspecialchars($p['kode']); ?>
                                                    - <?= htmlspecialchars($p['nama']); ?>

                                                </option>
                                            <?php endwhile; ?>

                                        </select>
                                    </div>

                                    <!-- POIN -->
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <i class="fa-solid fa-star input-icon"></i>
                                            Poin Pelanggaran
                                        </label>

                                        <input
                                            type="text"
                                            id="poin"
                                            class="form-control"
                                            placeholder="Poin otomatis terisi"
                                            readonly>

                                        <input type="hidden" name="poin" id="poin_hidden">
                                        <input type="hidden" name="pelanggaran_kategori_id" id="kategori_id">
                                    </div>

                                    <!-- GURU -->
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <i class="fa-solid fa-user-tie input-icon"></i>
                                            Guru Pelapor
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control"
                                            value="<?= htmlspecialchars($nama_guru_login); ?>"
                                            readonly>

                                        <input
                                            type="hidden"
                                            name="guru_id"
                                            value="<?= $guru_login; ?>">
                                    </div>

                                    <!-- TANGGAL -->
                                    <div class="col-md-6">
                                        <label for="tanggal" class="form-label">
                                            <i class="fa-solid fa-calendar-days input-icon"></i>
                                            Tanggal <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="date"
                                            name="tanggal"
                                            id="tanggal"
                                            class="form-control"
                                            value="<?= date('Y-m-d'); ?>"
                                            required>
                                    </div>

                                    <!-- KETERANGAN -->
                                    <div class="col-12">
                                        <label for="keterangan" class="form-label">
                                            <i class="fa-solid fa-align-left input-icon"></i>
                                            Keterangan <span class="text-danger">*</span>
                                        </label>

                                        <textarea
                                            name="keterangan"
                                            id="keterangan"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Masukkan keterangan pelanggaran..."
                                            required></textarea>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-5 pt-4 border-top">

                                    <a
                                        href="kelola_catatan_pelanggaran.php"
                                        class="btn btn-outline-secondary"
                                        style="border-radius:9px; padding:10px 22px; font-weight:600;">

                                        <i class="fa-solid fa-xmark me-1"></i>
                                        Batal
                                    </a>

                                    <button
                                        type="submit"
                                        class="btn-primary-custom"
                                        <?= ($tahun_id <= 0 || $guru_login <= 0 || count($siswa) <= 0) ? 'disabled' : ''; ?>>

                                        <i class="fa-solid fa-floppy-disk me-1"></i>
                                        Simpan Pelanggaran
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>

// ============================================
// TOM SELECT SISWA
// ============================================

const siswaSelect = new TomSelect("#siswa_id", {
    create: false,
    allowEmptyOption: true,
    sortField: {
        field: "text",
        direction: "asc"
    },
    onChange: function(value) {
        const option = document.querySelector(
            '#siswa_id option[value="' + CSS.escape(value) + '"]'
        );

        document.getElementById("kelas_id").value =
            option ? option.dataset.kelasId : "";

        document.getElementById("nama_kelas").value =
            option ? option.dataset.kelas : "";
    }
});

// ============================================
// TOM SELECT PELANGGARAN
// ============================================

const pelanggaranSelect = new TomSelect("#pelanggaran_id", {
    create: false,
    allowEmptyOption: true,
    sortField: {
        field: "text",
        direction: "asc"
    },
    onChange: function(value) {
        const option = document.querySelector(
            '#pelanggaran_id option[value="' + CSS.escape(value) + '"]'
        );

        const poin = option ? option.dataset.poin : "";
        const kategori = option ? option.dataset.kategori : "";

        document.getElementById("poin").value = poin;
        document.getElementById("poin_hidden").value = poin;
        document.getElementById("kategori_id").value = kategori;
    }
});

// ============================================
// VALIDASI SEBELUM FORM DIKIRIM
// ============================================

document.getElementById("formPelanggaran").addEventListener("submit", function(e) {

    const penempatanId = siswaSelect.getValue();

    if (!penempatanId || parseInt(penempatanId) <= 0) {
        e.preventDefault();
        alert("Silakan pilih siswa terlebih dahulu.");
        siswaSelect.focus();
        return false;
    }

    // Pastikan nilai pilihan masuk ke form POST.
    document.getElementById("siswa_id").value = penempatanId;

});

</script>

</body>
</html>
