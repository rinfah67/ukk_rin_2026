```php
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek hak akses
if (
    !isset($_SESSION['role']) ||
    !in_array($_SESSION['role'], ['guru', 'admin'])
) {
    die("Akses ditolak.");
}

// Mengambil data siswa, kelas, dan tahun ajaran
$siswa = mysqli_query($koneksi, "
    SELECT
        ks.siswa_id,
        ks.kelas_id,
        ks.tahun_ajaran_id,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        ta.nama AS nama_tahun
    FROM t_kelas_siswa ks
    INNER JOIN t_siswa s ON s.id = ks.siswa_id
    INNER JOIN t_kelas k ON k.id = ks.kelas_id
    INNER JOIN t_tahun_ajaran ta ON ta.id = ks.tahun_ajaran_id
    WHERE ks.status_aktif = 1
      AND s.status_aktif = 1
    ORDER BY s.nama ASC
");

if (!$siswa) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

// Mengambil data pelanggaran yang aktif
$pelanggaran = mysqli_query($koneksi, "
    SELECT id, nama, poin
    FROM t_pelanggaran
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

if (!$pelanggaran) {
    die("Gagal mengambil data pelanggaran: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Catatan Pelanggaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Tambah Catatan Pelanggaran</h4>
                </div>

                <div class="card-body">

                    <form action="proses_tambah_catatan.php" method="POST">

                        <!-- Pilih siswa, kelas, dan tahun ajaran -->
                        <div class="mb-3">
                            <label class="form-label">
                                Siswa - Kelas - Tahun Ajaran
                            </label>

                            <select name="registrasi" class="form-select" required>
                                <option value="">-- Pilih Siswa --</option>

                                <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
                                    <option value="<?=
                                        (int)$s['siswa_id'] . '|' .
                                        (int)$s['kelas_id'] . '|' .
                                        (int)$s['tahun_ajaran_id']
                                    ?>">
                                        <?= htmlspecialchars($s['nama_siswa']) ?>
                                        -
                                        <?= htmlspecialchars($s['nama_kelas']) ?>
                                        -
                                        <?= htmlspecialchars($s['nama_tahun']) ?>
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                        <!-- Pilih pelanggaran -->
                        <div class="mb-3">
                            <label class="form-label">
                                Jenis Pelanggaran
                            </label>

                            <select name="pelanggaran_id" class="form-select" required>
                                <option value="">-- Pilih Pelanggaran --</option>

                                <?php while ($p = mysqli_fetch_assoc($pelanggaran)): ?>
                                    <option value="<?= (int)$p['id'] ?>">
                                        <?= htmlspecialchars($p['nama']) ?>
                                        (<?= (int)$p['poin'] ?> poin)
                                    </option>
                                <?php endwhile; ?>

                            </select>
                        </div>

                        <!-- Tanggal -->
                        <div class="mb-3">
                            <label class="form-label">
                                Tanggal Pelanggaran
                            </label>

                            <input
                                type="date"
                                name="tanggal"
                                class="form-control"
                                value="<?= date('Y-m-d') ?>"
                                required
                            >
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>

                            <textarea
                                name="keterangan"
                                class="form-control"
                                rows="3"
                                placeholder="Masukkan keterangan pelanggaran"
                                required
                            ></textarea>
                        </div>

                        <!-- Tindakan -->
                        <div class="mb-3">
                            <label class="form-label">Tindakan</label>

                            <textarea
                                name="tindakan"
                                class="form-control"
                                rows="3"
                                placeholder="Masukkan tindakan yang diberikan"
                            ></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label">Status</label>

                            <select name="status" class="form-select" required>
                                <option value="Belum Ditindaklanjuti">
                                    Belum Ditindaklanjuti
                                </option>

                                <option value="Diproses">
                                    Diproses
                                </option>

                                <option value="Selesai">
                                    Selesai
                                </option>
                            </select>
                        </div>

                        <!-- Tombol -->
                        <button type="submit" class="btn btn-primary">
                            Simpan Catatan
                        </button>

                        <a
                            href="catatan_pelanggaran.php"
                            class="btn btn-secondary"
                        >
                            Batal
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>
```