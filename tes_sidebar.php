
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config/koneksi.php';

echo "BAGIAN 1: PHP BERJALAN<br>";

if (!isset($_SESSION['login'])) {
    die("Session login tidak ditemukan");
}

echo "BAGIAN 2: SESSION LOGIN OK<br>";
echo "Role: " . htmlspecialchars($_SESSION['role']) . "<br>";

echo "BAGIAN 3: SEBELUM SIDEBAR<br>";

include 'layout/sidebar.php';

echo "<div style='margin-left:250px; padding:20px;'>";
echo "<h1>SIDEBAR BERHASIL DILOAD</h1>";
echo "<p>Jika tulisan ini muncul, sidebar berhasil dimuat.</p>";
echo "</div>";

echo "BAGIAN 4: SESUDAH SIDEBAR<br>";
?>
