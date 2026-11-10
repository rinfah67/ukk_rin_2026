
<?php
// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengambil data session
$role = $_SESSION['role'] ?? 'guru';
$id_user = (int) ($_SESSION['id_user'] ?? 0);

// Halaman aktif
$halaman_aktif = basename($_SERVER['PHP_SELF']);

// Mengecek wali kelas
$is_wali_kelas = false;

if ($role === 'guru' && $id_user > 0) {

    require_once __DIR__ . '/../config/koneksi.php';

    $query_wali = mysqli_query($koneksi, "
        SELECT tw.id
        FROM t_guru tg
        INNER JOIN t_wali_kelas tw ON tw.guru_id = tg.id
        WHERE tg.user_id = $id_user
        AND tw.status_aktif = 1
        LIMIT 1
    ");

    if ($query_wali && mysqli_num_rows($query_wali) > 0) {
        $is_wali_kelas = true;
    }
}
?>

<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">

    <!-- Judul Sidebar -->
    <h4 class="text-white mb-4 px-2 fs-5 fw-bold d-flex align-items-center gap-2">
        <i class="fa-solid fa-shield-halved text-primary"></i>
        Si Pelang
    </h4>

    <ul class="nav flex-column">

        <?php if ($role === 'admin') : ?>

            <!-- MENU ADMIN -->

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fa-solid fa-house me-2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'kelola_siswa.php' ? 'active' : ''; ?>" href="kelola_siswa.php">
                    <i class="fa-solid fa-users me-2"></i>
                    Data Siswa
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'kelola_guru.php' ? 'active' : ''; ?>" href="kelola_guru.php">
                    <i class="fa-solid fa-chalkboard-user me-2"></i>
                    Data Guru
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'kelola_kelas.php' ? 'active' : ''; ?>" href="kelola_kelas.php">
                    <i class="fa-solid fa-school me-2"></i>
                    Data Kelas
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'kelola_tahun_ajaran.php' ? 'active' : ''; ?>" href="kelola_tahun_ajaran.php">
                    <i class="fa-solid fa-calendar-days me-2"></i>
                    Data Tahun Ajaran
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'kelola_wali_kelas.php' ? 'active' : ''; ?>" href="kelola_wali_kelas.php">
                    <i class="fa-solid fa-user-tie me-2"></i>
                    Data Wali Kelas
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'penempatan_siswa.php' ? 'active' : ''; ?>" href="penempatan_siswa.php">
                    <i class="fa-solid fa-user-gear me-2"></i>
                    Penempatan Siswa
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'kelola_pelanggaran_kategori.php' ? 'active' : ''; ?>" href="kelola_pelanggaran_kategori.php">
                    <i class="fa-solid fa-layer-group me-2"></i>
                    Kategori Pelanggaran
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'kelola_jenis_pelanggaran.php' ? 'active' : ''; ?>" href="kelola_jenis_pelanggaran.php">
                    <i class="fa-solid fa-list-check me-2"></i>
                    Jenis Pelanggaran
                </a>
            </li>

            <hr class="text-secondary my-3">

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'laporan.php' ? 'active' : ''; ?>" href="laporan.php">
                    <i class="fa-solid fa-file-invoice me-2"></i>
                    Laporan
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'about_me.php' ? 'active' : ''; ?>" href="about_me.php">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    About Me
                </a>
            </li>

        <?php elseif ($role === 'guru') : ?>

            <!-- MENU GURU -->

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fa-solid fa-house me-2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'kelola_catatan_pelanggaran.php' ? 'active' : ''; ?>" href="kelola_catatan_pelanggaran.php">
                    <i class="fa-solid fa-clipboard-list me-2"></i>
                    Catatan Pelanggaran
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'tindakan.php' ? 'active' : ''; ?>" href="tindakan.php">
                    <i class="fa-solid fa-hand-holding-heart me-2"></i>
                    Tindakan
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'riwayat.php' ? 'active' : ''; ?>" href="riwayat.php">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>
                    Riwayat
                </a>
            </li>

            <?php if ($is_wali_kelas) : ?>

                <hr class="text-secondary my-3">

                <li class="nav-item">
                    <a class="nav-link <?= $halaman_aktif == 'laporan.php' ? 'active' : ''; ?>" href="laporan.php">
                        <i class="fa-solid fa-file-invoice me-2"></i>
                        Laporan
                    </a>
                </li>

            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link <?= $halaman_aktif == 'about_me.php' ? 'active' : ''; ?>" href="about_me.php">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    About Me
                </a>
            </li>

        <?php endif; ?>

        <!-- LOGOUT -->
        <hr class="text-secondary my-3">

        <li class="nav-item mt-2">
            <a class="nav-link text-danger fw-bold" href="logout.php">
                <i class="fa-solid fa-right-from-bracket me-2"></i>
                Logout
            </a>
        </li>

    </ul>
</nav>
