
<?php
// layout/sidebar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
$id_user = (int) ($_SESSION['id_user'] ?? 0);

$current_page = $current_page ?? basename($_SERVER['PHP_SELF']);

// Memeriksa apakah guru merupakan wali kelas aktif
$is_wali_kelas = false;

if ($role === 'guru' && $id_user > 0) {
    $query_wali = mysqli_query($koneksi, "
        SELECT tw.id
        FROM t_guru tg
        INNER JOIN t_wali_kelas tw
            ON tw.guru_id = tg.id
        WHERE tg.user_id = $id_user
        AND tw.status_aktif = 1
        LIMIT 1
    ");

    if ($query_wali && mysqli_num_rows($query_wali) > 0) {
        $is_wali_kelas = true;
    }
}
?>

<style>
    body {
        overflow-x: hidden;
    }

    .sidebar {
    width: 250px !important;
    min-width: 250px;
    flex: 0 0 250px !important;
    min-height: 100vh;
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    }

    .sidebar .brand-title {
        color: #ffffff;
        font-size: 20px;
        font-weight: 800;
        white-space: nowrap;
    }

    .sidebar .nav-link {
        color: #94a3b8;
        padding: 12px 14px;
        border-radius: 8px;
        margin-bottom: 5px;
        transition: 0.2s;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }

    .sidebar .nav-link i {
        width: 20px;
        min-width: 20px;
        text-align: center;
        font-size: 15px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background-color: #2563eb;
        color: #ffffff;
    }

    .sidebar .nav-link.text-danger:hover {
        background-color: #dc2626;
        color: #ffffff !important;
    }

    .sidebar .menu-title {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        margin: 20px 12px 10px;
        letter-spacing: 1px;
    }

    @media (max-width: 767.98px) {
        .sidebar {
            width: 100%;
            min-height: auto;
            flex: 0 0 100%;
        }
    }
</style>

<nav class="sidebar col-md-3 col-lg-2 d-md-block collapse">

    <!-- Logo -->
    <div class="mb-4 px-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check text-primary fs-3"></i>
            <span class="brand-title">SI PELANG</span>
        </div>

        <small class="text-secondary">
            Sistem Pelanggaran Siswa
        </small>
    </div>

    <!-- Menu Admin -->
    <?php if ($role === 'admin') : ?>

        <div class="menu-title">Menu Utama</div>

        <ul class="nav flex-column">

            <li class="nav-item">
                <a href="dashboard.php"
                   class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="kelola_siswa.php"
                   class="nav-link <?= $current_page == 'kelola_siswa.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    Data Siswa
                </a>
            </li>

            <li class="nav-item">
                <a href="kelola_guru.php"
                   class="nav-link <?= $current_page == 'kelola_guru.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-badge"></i>
                    Data Guru
                </a>
            </li>

            <li class="nav-item">
                <a href="kelola_kelas.php"
                   class="nav-link <?= $current_page == 'kelola_kelas.php' ? 'active' : ''; ?>">
                    <i class="bi bi-building"></i>
                    Data Kelas
                </a>
            </li>

            <li class="nav-item">
                <a href="kelola_tahun_ajaran.php"
                   class="nav-link <?= $current_page == 'kelola_tahun_ajaran.php' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar3"></i>
                    Tahun Ajaran
                </a>
            </li>

            <li class="nav-item">
                <a href="kelola_wali_kelas.php"
                   class="nav-link <?= $current_page == 'kelola_wali_kelas.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-workspace"></i>
                    Wali Kelas
                </a>
            </li>

            <li class="nav-item">
                <a href="penempatan_siswa.php"
                   class="nav-link <?= $current_page == 'penempatan_siswa.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-lines-fill"></i>
                    Penempatan Siswa
                </a>
            </li>

        </ul>

        <div class="menu-title">Pelanggaran</div>

        <ul class="nav flex-column">

            <li class="nav-item">
                <a href="kelola_pelanggaran_kategori.php"
                   class="nav-link <?= $current_page == 'kelola_pelanggaran_kategori.php' ? 'active' : ''; ?>">
                    <i class="bi bi-tags"></i>
                    Kategori Pelanggaran
                </a>
            </li>

            <li class="nav-item">
                <a href="kelola_jenis_pelanggaran.php"
                   class="nav-link <?= $current_page == 'kelola_jenis_pelanggaran.php' ? 'active' : ''; ?>">
                    <i class="bi bi-exclamation-triangle"></i>
                    Kelola Jenis Pelanggaran
                </a>
            </li>
            
            <li class="nav-item">
                <a href="rekap_poin.php"
                   class="nav-link <?= $current_page == 'rekap_poin.php' ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    Rekap Poin
                </a>
            </li>

            <li class="nav-item">
                <a href="laporan.php"
                   class="nav-link <?= $current_page == 'laporan.php' ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    Laporan
                </a>
            </li>

        </ul>

    <?php elseif ($role === 'guru') : ?>

        <!-- Menu Guru -->
        <div class="menu-title">Menu Utama</div>

        <ul class="nav flex-column">

            <li class="nav-item">
                <a href="dashboard.php"
                   class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="kelola_catatan_pelanggaran.php"
                   class="nav-link <?= $current_page == 'kelola_catatan_pelanggaran.php' ? 'active' : ''; ?>">
                    <i class="bi bi-journal-text"></i>
                    Catat Pelanggaran
                </a>
            </li>

            <li class="nav-item">
                <a href="tindakan.php"
                   class="nav-link <?= $current_page == 'tindakan.php' ? 'active' : ''; ?>">
                    <i class="bi bi-clipboard-check"></i>
                    Tindakan
                </a>
            </li>

            <li class="nav-item">
                <a href="riwayat.php"
                   class="nav-link <?= $current_page == 'riwayat.php' ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    Riwayat
                </a>
            </li>

            <li class="nav-item">
                <a href="rekap_poin.php"
                   class="nav-link <?= $current_page == 'rekap_poin.php' ? 'active' : ''; ?>">
                    <i class="bi bi-clock-history"></i>
                    Rekap Poin
                </a>
            </li>
            <?php if ($is_wali_kelas) : ?>

                <li class="nav-item">
                    <a href="laporan.php"
                       class="nav-link <?= $current_page == 'laporan.php' ? 'active' : ''; ?>">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        Laporan
                    </a>
                </li>

            <?php endif; ?>

        </ul>

    <?php endif; ?>

    <!-- Menu Akun -->
    <div class="menu-title">Akun</div>

    <ul class="nav flex-column">

        <li class="nav-item">
            <a href="about_me.php"
               class="nav-link <?= $current_page == 'about_me.php' ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i>
                About Me
            </a>
        </li>

        <li class="nav-item">
            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </li>

    </ul>

</nav>
