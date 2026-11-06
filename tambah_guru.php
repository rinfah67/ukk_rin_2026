
<?php
// tambah_guru.php
include 'includes/cek_session.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <h2>Tambah Guru</h2>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Form Tambah Guru
                </div>

                <div class="card-body">

                    <form action="proses_tambah_guru.php" method="POST"
                          autocomplete="off" onsubmit="return cekForm()">

                        <div class="mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" id="nip"
                                   class="form-control"
                                   maxlength="18" inputmode="numeric" required>
                            <small>NIP harus berupa angka, maksimal 18 digit.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Guru</label>
                            <input type="text" name="nama"
                                   class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email"
                                   class="form-control"
                                   autocomplete="off"
                                   value=""
                                   placeholder="Ketik email sendiri"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password"
                                   id="password" class="form-control"
                                   autocomplete="new-password"
                                   value=""
                                   placeholder="Ketik password sendiri"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="konfirmasi_password"
                                   id="konfirmasi_password"
                                   class="form-control"
                                   autocomplete="new-password"
                                   value=""
                                   placeholder="Ketik ulang password"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Aktif</label>
                            <select name="status_aktif" class="form-select" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Simpan Guru
                        </button>

                        <a href="kelola_guru.php" class="btn btn-secondary">
                            Kembali
                        </a>

                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<script>
function cekForm() {
    var nip = document.getElementById('nip').value;
    var password = document.getElementById('password').value;
    var konfirmasi = document.getElementById('konfirmasi_password').value;

    if (!/^[0-9]+$/.test(nip) || nip.length > 18) {
        alert('NIP harus berupa angka dan maksimal 18 digit!');
        return false;
    }

    if (password != konfirmasi) {
        alert('Password tidak sama!');
        return false;
    }

    return true;
}
</script>

</body>
</html>