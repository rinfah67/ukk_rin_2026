<?php

include 'config/koneksi.php';

$name = 'Guru';
$email = 'guru@gmail.com';
$password = password_hash('guru123', PASSWORD_DEFAULT);
$role = 'guru';

$sql = "INSERT INTO t_users (name, email, password, role)
        VALUES ('$name', '$email', '$password', '$role')";

if (mysqli_query($koneksi, $sql)) {
    echo "User guru berhasil dibuat.";
} else {
    echo "Gagal membuat guru: " . mysqli_error($koneksi);
}

?>