
<?php
session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Si Pelang</title>

    <!-- Bootstrap 5.3.8 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-primary-subtle">

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center p-3 p-md-4">

    <div class="row w-100 shadow-lg rounded-4 overflow-hidden bg-white"
         style="max-width: 950px; min-height: 560px;">

        <!-- Panel kiri -->
        <div class="col-lg-6 bg-primary text-white d-flex flex-column
                    align-items-center justify-content-center text-center p-4 p-md-5">

            <img src="img/halo.png"
                 alt="Logo Si Pelang"
                 class="img-fluid mb-4"
                 style="width: 145px; height: 145px; object-fit: contain;">

            <h1 class="fw-bold mb-3">SI PELANG</h1>

            <p class="fs-5 mb-4">
                Sistem Informasi Pelanggaran Siswa
            </p>

            <div class="border-top border-light border-opacity-50 pt-3">
                <p class="mb-0 small">
                    Kelola dan pantau catatan pelanggaran siswa
                    dengan lebih mudah dan terorganisir.
                </p>
            </div>
        </div>

        <!-- Panel kanan: Form login -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5">

            <div class="w-100" style="max-width: 370px;">

                <div class="mb-4">
                    <span class="badge text-bg-primary rounded-pill px-3 py-2 mb-3">
                        <i class="bi bi-shield-lock me-1"></i>
                        LOGIN PENGGUNA
                    </span>

                    <h2 class="fw-bold text-dark">Selamat Datang!</h2>

                    <p class="text-secondary">
                        Silakan masuk menggunakan akun kamu.
                    </p>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2"
                         role="alert">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <div>
                            Login gagal. Periksa username dan password.
                        </div>
                    </div>
                <?php endif; ?>

                <form action="proses_login.php" method="POST">

                    <!-- Username atau email -->
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            Username atau Email
                        </label>

                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-person text-primary"></i>
                            </span>

                            <input
                                type="text"
                                name="email"
                                id="email"
                                class="form-control bg-light"
                                placeholder="Masukkan username atau email"
                                autocomplete="username"
                                required
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            Password
                        </label>

                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-lock text-primary"></i>
                            </span>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control bg-light"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                class="btn btn-light border"
                                type="button"
                                id="togglePassword"
                                aria-label="Tampilkan password"
                                aria-pressed="false"
                            >
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol login -->
                    <button type="submit"
                            name="login"
                            class="btn btn-primary btn-lg w-100 fw-semibold mt-4 py-3 shadow-sm">
                        MASUK
                        <i class="bi bi-arrow-right-circle ms-2"></i>
                    </button>

                </form>

                <div class="text-center mt-4">
                    <small class="text-secondary">
                        <i class="bi bi-shield-check text-primary me-1"></i>
                        Akses hanya untuk pengguna terdaftar
                    </small>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Tombol untuk menampilkan atau menyembunyikan password
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {
        const passwordTampil = passwordInput.type === 'password';

        passwordInput.type = passwordTampil ? 'text' : 'password';
        eyeIcon.className = passwordTampil ? 'bi bi-eye-slash' : 'bi bi-eye';

        togglePassword.setAttribute('aria-pressed', passwordTampil);
        togglePassword.setAttribute(
            'aria-label',
            passwordTampil ? 'Sembunyikan password' : 'Tampilkan password'
        );
    });
</script>

</body>
</html>
