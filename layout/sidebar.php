<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

// Ambil ID guru dan role dari session login
$id_guru = $_SESSION['id_guru'] ?? 0;
$role    = $_SESSION['role'] ?? 'guru'; 
$halaman_aktif = basename($_SERVER['PHP_SELF']);

// Cek apakah guru terdaftar sebagai Wali Kelas di t_wali_kelas
$is_wali_kelas = false;
if ($id_guru > 0) {
    $query_cek_wali = mysqli_query($koneksi, "SELECT * FROM t_wali_kelas WHERE id_guru = '$id_guru'");
    if ($query_cek_wali && mysqli_num_rows($query_cek_wali) > 0) {
        $is_wali_kelas = true;
    }
}
?>

<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
    <!-- Judul Brand Sidebar -->
    <h4 class="text-white mb-4 px-2 fs-5 fw-bold d-flex align-items-center gap-2">
        <i class="fa-solid fa-shield-halved text-primary"></i> Si Pelang
    </h4>
    
    <ul class="nav flex-column">
        <!-- Dashboard (Untuk semua role) -->
        <li class="nav-item">
            <a class="nav-link <?= ($halaman_aktif == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fa-solid fa-house me-2"></i> Dashboard
            </a>
        </li>

        <?php if ($role == 'admin') : ?>
            <!-- MENU KHUSUS ADMIN -->
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_siswa.php') ? 'active' : ''; ?>" href="kelola_siswa.php">
                    <i class="fa-solid fa-users me-2"></i> Data Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_guru.php') ? 'active' : ''; ?>" href="kelola_guru.php">
                    <i class="fa-solid fa-chalkboard-user me-2"></i> Data Guru
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_kelas.php') ? 'active' : ''; ?>" href="kelola_kelas.php">
                    <i class="fa-solid fa-school me-2"></i> Data Kelas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_tahun_ajaran.php') ? 'active' : ''; ?>" href="kelola_tahun_ajaran.php">
                    <i class="fa-solid fa-calendar-days me-2"></i> Data Tahun Ajaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_wali_kelas.php') ? 'active' : ''; ?>" href="kelola_wali_kelas.php">
                    <i class="fa-solid fa-user-tie me-2"></i> Data Wali Kelas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'penempatan_siswa.php') ? 'active' : ''; ?>" href="penempatan_siswa.php">
                    <i class="fa-solid fa-user-gear me-2"></i> Penempatan Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_pelanggaran_kategori.php') ? 'active' : ''; ?>" href="kelola_pelanggaran_kategori.php">
                    <i class="fa-solid fa-layer-group me-2"></i> Kategori Pelanggaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_jenis_pelanggaran.php') ? 'active' : ''; ?>" href="kelola_jenis_pelanggaran.php">
                    <i class="fa-solid fa-list-check me-2"></i> Jenis Pelanggaran
                </a>
            </li>

        <?php else : ?>
            <!-- MENU KHUSUS GURU -->
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_siswa.php') ? 'active' : ''; ?>" href="kelola_siswa.php">
                    <i class="fa-solid fa-users me-2"></i> Data Siswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'kelola_catatan_pelanggaran.php') ? 'active' : ''; ?>" href="kelola_catatan_pelanggaran.php">
                    <i class="fa-solid fa-clipboard-list me-2"></i> Input Pelanggaran
                </a>
            </li>

            <!-- MENU TAMBAHAN GURU WALI KELAS -->
            <?php if ($is_wali_kelas) : ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($halaman_aktif == 'rekap_poin.php') ? 'active' : ''; ?>" href="rekap_poin.php">
                        <i class="fa-solid fa-chart-pie me-2"></i> Rekap Poin Kelas
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Menu About Me -->
        <li class="nav-item">
            <a class="nav-link <?= ($halaman_aktif == 'about_me.php') ? 'active' : ''; ?>" href="about_me.php">
                <i class="fa-solid fa-circle-info me-2"></i> About Me
            </a>
        </li>
        
        <hr class="text-secondary my-3">

        <!-- Laporan (Admin atau Wali Kelas) -->
        <?php if ($role == 'admin' || $is_wali_kelas) : ?>
            <li class="nav-item">
                <a class="nav-link <?= ($halaman_aktif == 'laporan.php') ? 'active' : ''; ?>" href="laporan.php">
                    <i class="fa-solid fa-file-invoice me-2"></i> Laporan
                </a>
            </li>
        <?php endif; ?>

        <!-- Logout -->
        <li class="nav-item mt-2">
            <a class="nav-link text-danger fw-bold" href="logout.php">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
            </a>
        </li>
    </ul>
</nav>