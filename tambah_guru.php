
<?php
// tambah_guru.php

include 'includes/cek_session.php';

// Hanya admin yang boleh menambahkan guru
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru - SI PELANG</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- Konten Utama -->
        <main class="col-md-9 col-lg-10 p-4">

            <h2 class="mb-4">
                <i class="bi bi-person-plus"></i>
                Tambah Guru
            </h2>

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <i class="bi bi-person-plus-fill"></i>
                    Form Tambah Guru
                </div>

                <div class="card-body">

                    <form action="proses_tambah_guru.php"
                          method="POST"
                          autocomplete="off"
                          onsubmit="return cekForm()">

                        <!-- NIP -->
                        <div class="mb-3">
                            <label class="form-label">NIP</label>

                            <input
                                type="text"
                                name="nip"
                                id="nip"
                                class="form-control"
                                maxlength="18"
                                inputmode="numeric"
                                placeholder="Masukkan NIP"
                                required
                            >

                            <small class="text-muted">
                                NIP harus berupa angka maksimal 18 digit.
                            </small>
                        </div>

                        <!-- Nama Guru -->
                        <div class="mb-3">
                            <label class="form-label">Nama Guru</label>

                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                placeholder="Masukkan nama guru"
                                maxlength="255"
                                required
                            >
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email</label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                autocomplete="off"
                                value=""
                                placeholder="Masukkan email"
                                required
                            >
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label">Password</label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                autocomplete="new-password"
                                placeholder="Masukkan password"
                                required
                            >

                            <small class="text-muted">
                                Password minimal 6 karakter.
                            </small>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>

                            <input
                                type="password"
                                name="konfirmasi_password"
                                id="konfirmasi_password"
                                class="form-control"
                                autocomplete="new-password"
                                placeholder="Masukkan ulang password"
                                required
                            >
                        </div>

                        <!-- Status Aktif -->
                        <div class="mb-3">
                            <label class="form-label">Status Aktif</label>

                            <select
                                name="status_aktif"
                                id="status_aktif"
                                class="form-select"
                                required
                            >
                                <option value="1" selected>Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Tombol -->
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            Simpan Guru
                        </button>

                        <a href="kelola_guru.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Kembali
                        </a>

                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>

// Membatasi NIP hanya angka
document.getElementById('nip').addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18);
});

// Memeriksa data sebelum dikirim
function cekForm() {

    var nip = document.getElementById('nip').value;
    var password = document.getElementById('password').value;
    var konfirmasi = document.getElementById('konfirmasi_password').value;

    // Validasi NIP
    if (!/^[0-9]{1,18}$/.test(nip)) {
        alert('NIP harus berupa angka dan maksimal 18 digit!');
        return false;
    }

    // Validasi password
    if (password.length < 6) {
        alert('Password minimal 6 karakter!');
        return false;
    }

    // Validasi konfirmasi password
    if (password !== konfirmasi) {
        alert('Password dan konfirmasi password tidak sama!');
        return false;
    }

    return true;
}

</script>

</body>
</html>
