
<?php
// Mengambil nama halaman yang sedang dibuka
$current_page = basename($_SERVER['PHP_SELF']);

// Mengambil role pengguna yang login
$role = $_SESSION['role'] ?? '';

// Default: pengguna bukan wali kelas
$is_walikelas = false;

// Mengecek apakah guru yang login merupakan wali kelas aktif
if ($role == 'guru' && isset($koneksi)) {

    $id_user = $_SESSION['id_user'] ?? 0;

    // Mencari ID guru berdasarkan akun yang login
    $query_guru = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_guru WHERE user_id = ? LIMIT 1"
    );

    if ($query_guru) {
        mysqli_stmt_bind_param($query_guru, "i", $id_user);
        mysqli_stmt_execute($query_guru);

        $hasil_guru = mysqli_stmt_get_result($query_guru);
        $data_guru = mysqli_fetch_assoc($hasil_guru);

        // Jika data guru ditemukan, periksa penugasan wali kelas
        if ($data_guru) {
            $id_guru = $data_guru['id'];

            $query_wali = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM t_wali_kelas
                 WHERE guru_id = ?
                   AND status_aktif = 1
                 LIMIT 1"
            );

            if ($query_wali) {
                mysqli_stmt_bind_param(
                    $query_wali,
                    "i",
                    $id_guru
                );

                mysqli_stmt_execute($query_wali);

                $hasil_wali = mysqli_stmt_get_result($query_wali);

                if (mysqli_num_rows($hasil_wali) > 0) {
                    $is_walikelas = true;
                }

                mysqli_stmt_close($query_wali);
            }
        }

        mysqli_stmt_close($query_guru);
    }
}
?>

<!-- Sidebar -->
<div class="position-fixed top-0 start-0 vh-100
            bg-dark text-white p-2 overflow-y-auto"
     style="width: 222px; z-index: 1000;">

    <h5 class="fw-bold mt-2 mb-4 ms-1">
        Pelanggaran Siswa
    </h5>

    <nav class="nav nav-pills flex-column gap-1">

        <!-- Menu untuk semua pengguna yang sudah login -->
        <a href="about_me.php"
           class="nav-link <?= $current_page == 'about_me.php' ? 'active' : 'text-white'; ?>">
            About Me
        </a>

        <?php if ($role == 'admin') { ?>

            <!-- Menu khusus Admin -->
            <hr class="border-secondary">
            <div class="text-secondary small ms-2">MENU ADMIN</div>

            <a href="dashboard.php"
               class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : 'text-white'; ?>">
                dashboard
            </a>

            <a href="kelola_user.php"
               class="nav-link <?= $current_page == 'kelola_user.php' ? 'active' : 'text-white'; ?>">
                Kelola User
            </a>

            <a href="kelola_guru.php"
               class="nav-link <?= $current_page == 'kelola_guru.php' ? 'active' : 'text-white'; ?>">
                Kelola Guru
            </a>

            <a href="kelola_siswa.php"
               class="nav-link <?= $current_page == 'kelola_siswa.php' ? 'active' : 'text-white'; ?>">
                Kelola Siswa
            </a>

            <a href="kelola_kelas.php"
               class="nav-link <?= $current_page == 'kelola_kelas.php' ? 'active' : 'text-white'; ?>">
                Kelola Kelas
            </a>

            <a href="laporan.php"
               class="nav-link <?= $current_page == 'laporan.php' ? 'active' : 'text-white'; ?>">
                Laporan
            </a>

        <?php } elseif ($role == 'guru') { ?>

            <!-- Menu Guru -->
            <hr class="border-secondary">
            <div class="text-secondary small ms-2">MENU GURU</div>

            <a href="dashboard.php"
               class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : 'text-white'; ?>">
                Dashboard
            </a>

            <a href="catatan_pelanggaran.php"
               class="nav-link <?= $current_page == 'catatan_pelanggaran.php' ? 'active' : 'text-white'; ?>">
                Catat Pelanggaran
            </a>

            <a href="riwayat.php"
               class="nav-link <?= $current_page == 'riwayat.php' ? 'active' : 'text-white'; ?>">
                Riwayat
            </a>

            <a href="rekap_poin.php"
               class="nav-link <?= $current_page == 'rekap_poin.php' ? 'active' : 'text-white'; ?>">
                Rekap Poin
            </a>

            <!-- Menu tambahan khusus Wali Kelas -->
            <?php if ($is_walikelas) { ?>

                <hr class="border-secondary">

                <a href="laporan.php"
                   class="nav-link <?= $current_page == 'laporan.php' ? 'active' : 'text-white'; ?>">
                    Laporan Wali Kelas
                </a>

            <?php } ?>

        <?php } ?>

        <hr class="border-secondary">

        <a href="logout.php" class="nav-link text-danger">
            Logout
        </a>

    </nav>
</div>
