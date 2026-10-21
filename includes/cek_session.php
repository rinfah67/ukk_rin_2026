
<?php
// includes/cek_session.php

// Memulai session hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

// Mengambil role pengguna
$role = $_SESSION['role'] ?? '';

// Memastikan role pengguna valid
if (!in_array($role, ['admin', 'guru'], true)) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}
?>
