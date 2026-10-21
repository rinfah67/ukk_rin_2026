
<?php
session_start();
include 'config/koneksi.php';

// Memeriksa login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Mengambil nama pengguna
$nama = $_SESSION['name'] ?? 'Pelajar';
$role = $_SESSION['role'] ?? 'guru';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>About Me</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<?php include "layout/sidebar.php"; ?>

<!-- Konten utama -->
<main class="container-fluid p-4" style="margin-left: 222px; width: calc(100% - 222px);">

    <!-- Judul halaman -->
    <div class="mb-4">
        <h3 class="fw-bold mb-1">About Me</h3>
        <p class="text-secondary mb-0">
            Informasi singkat tentang diri saya.
        </p>
    </div>

    <!-- Kartu profil -->
    <div class="card border-0 shadow-sm mx-auto" style="max-width: 650px;">

        <!-- Header profil -->
        <div class="card-body bg-primary text-white text-center p-4 rounded-top">
            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold mx-auto mb-3"
                 style="width: 85px; height: 85px; font-size: 32px;">
                F
            </div>

            <h4 class="fw-bold mb-1">
                <?= htmlspecialchars($nama) ?>
            </h4>

            <p class="mb-0">
                Pelajar SMK
            </p>
        </div>

        <!-- Informasi diri -->
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">Informasi Pribadi</h5>

            <div class="row border-bottom py-3">
                <div class="col-4 text-secondary">Status</div>
                <div class="col-8 fw-semibold">Pelajar</div>
            </div>

            <div class="row border-bottom py-3">
                <div class="col-4 text-secondary">Sekolah</div>
                <div class="col-8 fw-semibold">
                    SMK Muhammadiyah Kota Tasikmalaya
                </div>
            </div>

            <div class="row border-bottom py-3">
                <div class="col-4 text-secondary">Umur</div>
                <div class="col-8 fw-semibold">17 tahun</div>
            </div>

            <div class="row border-bottom py-3">
                <div class="col-4 text-secondary">Hobi</div>
                <div class="col-8 fw-semibold">Main game</div>
            </div>

            <div class="row py-3">
                <div class="col-4 text-secondary">Role</div>
                <div class="col-8">
                    <span class="badge text-bg-primary">
                        <?= htmlspecialchars(ucfirst($role)) ?>
                    </span>
                </div>
            </div>

            <div class="alert alert-primary mb-0 mt-3">
                Halo! Saya seorang pelajar yang sedang belajar
                dan mengembangkan kemampuan di bidang teknologi.
            </div>

        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
