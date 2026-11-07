<?php
// tambah_siswa.php
include 'includes/cek_session.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">

            <h1 class="mb-4">Tambah Siswa</h1>

            <!-- NOTIFIKASI ERROR -->
            <?php if (isset($_SESSION['pesan_error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['pesan_error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php } ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Form Tambah Siswa
                </div>

                <div class="card-body">
                    <form action="proses_tambah_siswa.php" method="POST" id="formTambahSiswa">

                        <!-- NIS -->
                        <div class="col-md-6">
                            <label for="nis" class="form-label">NIS</label>
                            <input type="text"
                                name="nis"
                                id="nis"
                                class="form-control"
                                maxlength="15"
                                pattern="[0-9]{1,15}"
                                inputmode="numeric"
                                title="NIS harus berupa angka maksimal 15 digit"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15)"
                                required>
                                <div class="form-text">NIS harus terdiri dari 15 angka.</div>
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
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                required>
                                <div class="form-text">NISN harus terdiri dari 10 angka.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Siswa</label>
                            <input type="text" name="nama"
                                   class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Jenis Kelamin</label>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                       name="jenis_kelamin" id="laki"
                                       value="L" required>
                                <label class="form-check-label" for="laki">Laki-laki</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                       name="jenis_kelamin" id="perempuan"
                                       value="P" required>
                                <label class="form-check-label" for="perempuan">Perempuan</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                   class="form-control"
                                   min="2000-01-01" max="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control"
                                      rows="4" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Status Aktif</label>
                            <select name="status_aktif" class="form-select" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Simpan Siswa
                        </button>

                        <a href="kelola_siswa.php" class="btn btn-secondary">
                            Kembali
                        </a>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
document.getElementById('formTambahSiswa').addEventListener('submit', function(e) {
    const nis = document.getElementById('nis').value.trim();
    const nisn = document.getElementById('nisn').value.trim();

    if (!/^[0-9]{10}$/.test(nis)) {
        e.preventDefault();
        alert('NIS harus tepat 10 angka!');
        document.getElementById('nis').focus();
        return;
    }

    if (!/^[0-9]{10}$/.test(nisn)) {
        e.preventDefault();
        alert('NISN harus tepat 10 angka!');
        document.getElementById('nisn').focus();
        return;
    }

    const tgl = document.getElementById('tanggal_lahir').value;
    const hariIni = new Date().toISOString().slice(0, 10);
    if (tgl < '2000-01-01' || tgl > hariIni) {
        e.preventDefault();
        alert('Tanggal lahir harus antara 01-01-2000 dan hari ini!');
        document.getElementById('tanggal_lahir').focus();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>