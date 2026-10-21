
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "PHP BERJALAN<br>";

include 'config/koneksi.php';
echo "KONEKSI DATABASE BERHASIL<br>";

session_start();
echo "SESSION BERJALAN<br>";

echo "Role: " . ($_SESSION['role'] ?? 'Tidak ditemukan') . "<br>";

$hasil = mysqli_query($koneksi, "SELECT * FROM t_tahun_ajaran");

if (!$hasil) {
    die("ERROR QUERY: " . mysqli_error($koneksi));
}

echo "QUERY BERHASIL<br>";
echo "Jumlah data: " . mysqli_num_rows($hasil);
?>
