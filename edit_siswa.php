<?php
session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Hanya admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Memeriksa ID siswa
if (!isset($_GET['id'])) {
    header("Location: kelola_siswa.php");
    exit;
}

$id = (int) $_GET['id'];

// Mengambil data siswa
$stmt = mysqli_prepare($koneksi, "
    SELECT * FROM t_siswa WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$siswa = mysqli_fetch_assoc($result);

// Memeriksa data siswa
if (!$siswa) {
    die("Data siswa tidak ditemukan!");
}

// Mengambil data kelas
$query_kelas = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_kelas
    ORDER BY nama ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <div class="container-fluid p-4">

                <div class="card border-0 shadow-sm rounded-4">

                    <!-- Header -->
                    <div class="card-header bg-primary text-white py-3 rounded-top-4">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-pencil-square me-2"></i>
                            Form Edit Siswa
                        </h5>
                    </div>

                    <!-- Form -->
                    <div class="card-body p-4">

                        <form action="proses_edit_siswa.php" method="POST">

                            <input type="hidden" name="id"
                                   value="<?= (int)$siswa['id']; ?>">

                            <div class="row g-3">

                                <!-- NIS -->
                                <div class="col-md-6">
                                    <label for="nis" class="form-label">NIS</label>
                                    <input type="text"
                                           name="nis"
                                           id="nis"
                                           class="form-control"
                                           maxlength="10"
                                           pattern="[0-9]{1,10}"
                                           inputmode="numeric"
                                           title="NIS harus berupa angka maksimal 10 digit"
                                           value="<?= htmlspecialchars($siswa['nis'] ?? ''); ?>"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                           required>
                                </div>

                                <!-- NISN -->
                                <div class="col-md-6">
                                    <label for="nisn" class="form-label">NISN</label>
                                    <input type="text"
                                           name="nisn"
                                           id="nisn"
                                           class="form-control"
                                           maxlength="10"
                                           pattern="[0-9]{1,10}"
                                           inputmode="numeric"
                                           title="NISN harus berupa angka maksimal 10 digit"
                                           value="<?= htmlspecialchars($siswa['nisn'] ?? ''); ?>"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                           required>
                                </div>

                                <!-- Nama Siswa -->
                                <div class="col-md-6">
                                    <label class="form-label">Nama Siswa</label>
                                    <input type="text"
                                           name="nama"
                                           class="form-control"
                                           placeholder="Masukkan Nama Siswa"
                                           value="<?= htmlspecialchars($siswa['nama'] ?? ''); ?>"
                                           required>
                                </div>

                                <!-- Jenis Kelamin -->
                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-select" required>
                                        <option value="">-- Pilih Jenis Kelamin --</option>

                                        <option value="L"
                                            <?= ($siswa['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>
                                            Laki-laki
                                        </option>

                                        <option value="P"
                                            <?= ($siswa['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>
                                            Perempuan
                                        </option>
                                    </select>
                                </div>

                                <!-- Tanggal Lahir -->
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date"
                                           name="tanggal_lahir"
                                           class="form-control"
                                           value="<?= htmlspecialchars($siswa['tanggal_lahir'] ?? ''); ?>">
                                </div>

                                <!-- Alamat -->
                                <div class="col-md-6">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat"
                                              class="form-control"
                                              rows="1"
                                              placeholder="Masukkan Alamat"><?= htmlspecialchars($siswa['alamat'] ?? ''); ?></textarea>
                                </div>

                                <!-- Kelas -->
                                <div class="col-md-6">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>

                                        <?php while ($kelas = mysqli_fetch_assoc($query_kelas)) { ?>

                                            <option value="<?= $kelas['id']; ?>"
                                                <?= ($siswa['kelas_id'] ?? '') == $kelas['id'] ? 'selected' : ''; ?>>

                                                <?= htmlspecialchars($kelas['nama']); ?>

                                            </option>

                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Status Siswa -->
                                <div class="col-md-6">
                                    <label class="form-label">Status Siswa</label>
                                    <select name="status_aktif" class="form-select" required>

                                        <option value="1"
                                            <?= $siswa['status_aktif'] == 1 ? 'selected' : ''; ?>>
                                            Aktif
                                        </option>

                                        <option value="0"
                                            <?= $siswa['status_aktif'] == 0 ? 'selected' : ''; ?>>
                                            Tidak Aktif
                                        </option>

                                    </select>
                                </div>

                            </div>

                            <!-- Tombol -->
                            <div class="mt-4">

                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i>
                                    Simpan Perubahan
                                </button>

                                <a href="kelola_siswa.php" class="btn btn-outline-secondary px-4 ms-1">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Kembali
                                </a>

                            </div>

                        </form>

                    </div>
                </div>

            </div>

        </main>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
