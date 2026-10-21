<?php

include 'config/koneksi.php';

// =========================
// DATA USER ADMIN
// =========================
$name = 'Administrator';
$email = 'admin@gmail.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$role = 'admin';

$sql = "INSERT INTO t_users 
              (name, email, password, role) 
              VALUES 
              ('$name', '$email', '$password', '$role')";

if (mysqli_query($koneksi, $sql)) {
    echo "User admin berhasil dibuat.<br>";
} else {
    echo "Gagal membuat admin: " . mysqli_error($koneksi) . "<br>";
}

?>