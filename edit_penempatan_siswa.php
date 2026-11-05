
<?php
// edit_penempatan_siswa.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Membuat token keamanan
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mengambil ID penempatan
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['pesan_error'] = "ID penempatan tidak valid.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Mengambil data penempatan
$stmt = mysqli_prepare($koneksi, "
    SELECT *
    FROM t_kelas_siswa
    WHERE id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['pesan_error'] = "Data penempatan tidak ditemukan.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Mengambil siswa aktif dan siswa yang sedang diedit
$stmt_siswa = mysqli_prepare($koneksi, "
    SELECT s.id, s.nis, s.nama
    FROM t_siswa s
    WHERE s.status_aktif = 1
    AND (
        s.id = ?
        OR NOT EXISTS (
            SELECT 1
            FROM t_kelas_siswa ks
            WHERE ks.siswa_id = s.id
            AND ks.tahun_ajaran_id = ?
            AND ks.status_aktif = 1
            AND ks.id != ?
        )
    )
    ORDER BY s.nama ASC
");

mysqli_stmt_bind_param(
    $stmt_siswa,
    "iii",
    $data['siswa_id'],
    $data['tahun_ajaran_id'],
    $id
);

mysqli_stmt_execute($stmt_siswa);
$siswa = mysqli_stmt_get_result($stmt_siswa);

// Mengambil tahun ajaran aktif dan tahun ajaran yang sedang diedit
$stmt_tahun = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    OR id = ?
    ORDER BY id DESC
");

mysqli_stmt_bind_param($stmt_tahun, "i", $data['tahun_ajaran_id']);
mysqli_stmt_execute($stmt_tahun);
$tahun = mysqli_stmt_get_result($stmt_tahun);

// Mengambil kelas aktif dan kelas yang sedang digunakan
$stmt_kelas = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_kelas
    WHERE status_aktif = 1
    OR id = ?
    ORDER BY nama ASC
");

mysqli_stmt_bind_param($stmt_kelas, "i", $data['kelas_id']);
mysqli_stmt_execute($stmt_kelas);
$kelas = mysqli_stmt_get_result($stmt_kelas);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Penempatan Siswa - SI PELANG</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <div class="mb-4">
                <h3 class="fw-bold mb-1">Edit Penempatan Siswa</h3>
                <p class="text-secondary mb-0">
                    Ubah data penempatan siswa.
                </p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square me-2"></i>
                        Form Edit Penempatan
                    </h5>
                </div>

                <div class="card-body">

                    <form action="proses_edit_penempatan.php" method="POST">

                        <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <div class="row">

                            <!-- Siswa -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Siswa</label>

                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>

                                    <?php while ($row = mysqli_fetch_assoc($siswa)): ?>
                                        <option
                                            value="<?= (int)$row['id'] ?>"
                                            <?= (int)$row['id'] === (int)$data['siswa_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nis']) ?> -
                                            <?= htmlspecialchars($row['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Tahun ajaran -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Ajaran</label>

                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>

                                    <?php while ($row = mysqli_fetch_assoc($tahun)): ?>
                                        <option
                                            value="<?= (int)$row['id'] ?>"
                                            <?= (int)$row['id'] === (int)$data['tahun_ajaran_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Kelas -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas</label>

                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($row = mysqli_fetch_assoc($kelas)): ?>
                                        <option
                                            value="<?= (int)$row['id'] ?>"
                                            <?= (int)$row['id'] === (int)$data['kelas_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Tanggal mulai -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai</label>

                                <input
                                    type="date"
                                    name="tanggal_mulai"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['tanggal_mulai']) ?>"
                                    required
                                >
                            </div>

                            <!-- Tanggal selesai -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai</label>

                                <input
                                    type="date"
                                    name="tanggal_selesai"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['tanggal_selesai']) ?>"
                                    required
                                >
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>

                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" <?= (int)$data['status_aktif'] === 1 ? 'selected' : '' ?>>
                                        Aktif
                                    </option>

                                    <option value="0" <?= (int)$data['status_aktif'] === 0 ? 'selected' : '' ?>>
                                        Tidak Aktif
                                    </option>
                                </select>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>
                            Simpan Perubahan
                        </button>

                        <a href="penempatan_siswa.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali
                        </a>

                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
