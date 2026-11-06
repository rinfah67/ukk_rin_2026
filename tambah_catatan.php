<?php

include "config/koneksi.php";

if (isset($_POST['simpan'])) {

    $siswa = $_POST['siswa'];
    $pelanggaran = $_POST['pelanggaran'];

    mysqli_query($koneksi, "
        INSERT INTO t_pelanggaran_siswa
        (siswa_id, pelanggaran_id)
        VALUES
        ('$siswa', '$pelanggaran')
    ");

    header("Location: catatan_pelanggaran.php");
}

?>

<h2>Tambah Catatan Pelanggaran</h2>

<form method="POST">

Siswa:
<br>

<select name="siswa">

<?php

$data = mysqli_query($koneksi, "SELECT * FROM t_siswa WHERE status_aktif=1");

while ($siswa = mysqli_fetch_assoc($data)) {

?>

<option value="<?php echo $siswa['id']; ?>">
    <?php echo $siswa['nis']; ?> - <?php echo $siswa['nama']; ?>
</option>

<?php } ?>

</select>

<br><br>

Jenis Pelanggaran:
<br>

<select name="pelanggaran">

<?php

$data = mysqli_query($koneksi, "
    SELECT * FROM t_pelanggaran_kategori
    WHERE status_aktif=1
");

while ($pelanggaran = mysqli_fetch_assoc($data)) {

?>

<option value="<?php echo $pelanggaran['id']; ?>">
    <?php echo $pelanggaran['nama']; ?>
</option>

<?php } ?>

</select>

<br><br>

<button name="simpan">Simpan</button>

</form>

<br>

<a href="kelola_catatan_pelanggaran.php">Kembali</a>