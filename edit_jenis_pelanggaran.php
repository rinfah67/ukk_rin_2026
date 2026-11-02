<?php
// edit_jenis_pelanggaran.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['pesan_error'] = 'ID jenis pelanggaran tidak valid.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

// Ambil data yang akan diedit
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_pelanggaran WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['pesan_error'] = 'Data jenis pelanggaran tidak ditemukan.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

// Ambil kategori aktif dan kategori yang sedang digunakan
$sql_kategori = "SELECT id, nama, status_aktif
                 FROM t_pelanggaran_kategori
                 WHERE status_aktif = 1
                    OR id = " . (int) $data['pelanggaran_kategori_id'] . "
                 ORDER BY nama ASC";

$kategori = mysqli_query($koneksi, $sql_kategori);

if (!$kategori) {
    die("Gagal mengambil kategori: " .
        htmlspecialchars(mysqli_error($koneksi)));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Jenis Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 p-4">

            <h1 class="mb-4">Edit Jenis Pelanggaran</h1>

            <?php if (isset($_SESSION['pesan_error'])) { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php } ?>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form action="proses_edit_jenis_pelanggaran.php"
                          method="POST">

                        <input type="hidden"
                               name="id"
                               value="<?= (int) $data['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">
                                Kategori Pelanggaran
                            </label>

                            <select name="pelanggaran_kategori_id"
                                    class="form-select" required>

                                <?php while ($kat = mysqli_fetch_assoc($kategori)) { ?>
                                    <option
                                        value="<?= (int) $kat['id']; ?>"
                                        <?= (int) $kat['id'] === (int) $data['pelanggaran_kategori_id']
                                            ? 'selected'
                                            : ''; ?>>

                                        <?= htmlspecialchars($kat['nama']); ?>

                                        <?= (int) $kat['status_aktif'] === 0
                                            ? ' (Tidak Aktif)'
                                            : ''; ?>
                                    </option>
                                <?php } ?>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kode Pelanggaran</label>

                            <input type="text"
                                   name="kode"
                                   class="form-control"
                                   maxlength="30"
                                   value="<?= htmlspecialchars($data['kode'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Pelanggaran</label>

                            <input type="text"
                                   name="nama"
                                   class="form-control"
                                   maxlength="150"
                                   value="<?= htmlspecialchars($data['nama'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Poin</label>

                            <input type="number"
                                   name="poin"
                                   class="form-control"
                                   min="0"
                                   value="<?= (int) $data['poin']; ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>

                            <textarea name="deskripsi"
                                      class="form-control"
                                      rows="4"><?= htmlspecialchars($data['deskripsi'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Aktif</label>

                            <select name="status_aktif"
                                    class="form-select" required>

                                <option value="1"
                                    <?= (int) $data['status_aktif'] === 1
                                        ? 'selected'
                                        : ''; ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= (int) $data['status_aktif'] === 0
                                        ? 'selected'
                                        : ''; ?>>
                                    Tidak Aktif
                                </option>

                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Update
                        </button>

                        <a href="kelola_jenis_pelanggaran.php"
                           class="btn btn-secondary">
                            Kembali
                        </a>

                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

</body>
</html>