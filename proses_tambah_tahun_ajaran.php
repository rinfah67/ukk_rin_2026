<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

if (isset($_POST['simpan'])) {
    $tahun_ajaran    = isset($_POST['tahun_ajaran']) ? mysqli_real_escape_string($koneksi, $_POST['tahun_ajaran']) : '';
    $status          = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : 'Aktif';
    $tanggal_mulai   = !empty($_POST['tanggal_mulai']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']) : NULL;
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']) : NULL;

    // 1. Baca Struktur Kolom Tabel Asli
    $query_cols = mysqli_query($koneksi, "SHOW COLUMNS FROM t_tahun_ajaran");
    $columns = [];
    if ($query_cols) {
        while ($row = mysqli_fetch_assoc($query_cols)) {
            $columns[] = strtolower($row['Field']);
        }
    }

    // Deteksi nama kolom untuk 'status'
    $col_status = in_array('status', $columns) ? 'status' : (in_array('stt', $columns) ? 'stt' : 'status');

    // 2. PAKSA Nonaktifkan Semua Record Lain Jika Data Baru 'Aktif'
    if ($status === 'Aktif') {
        mysqli_query($koneksi, "UPDATE t_tahun_ajaran SET `$col_status` = 'Tidak Aktif'");
    }

    // Deteksi nama kolom untuk 'tahun_ajaran'
    $col_tahun = 'tahun_ajaran';
    if (in_array('tahun_ajaran', $columns)) { $col_tahun = 'tahun_ajaran'; }
    elseif (in_array('tahun', $columns)) { $col_tahun = 'tahun'; }
    elseif (in_array('nama_tahun', $columns)) { $col_tahun = 'nama_tahun'; }
    else { $col_tahun = isset($columns[1]) ? $columns[1] : 'tahun_ajaran'; }

    // 3. Susun Query Simpan
    $insert_fields = ["`$col_tahun`", "`$col_status`"];
    $insert_values = ["'$tahun_ajaran'", "'$status'"];

    if ($tanggal_mulai && (in_array('tanggal_mulai', $columns) || in_array('tgl_mulai', $columns))) {
        $field_tm = in_array('tanggal_mulai', $columns) ? 'tanggal_mulai' : 'tgl_mulai';
        $insert_fields[] = "`$field_tm`";
        $insert_values[] = "'$tanggal_mulai'";
    }

    if ($tanggal_selesai && (in_array('tanggal_selesai', $columns) || in_array('tgl_selesai', $columns))) {
        $field_ts = in_array('tanggal_selesai', $columns) ? 'tanggal_selesai' : 'tgl_selesai';
        $insert_fields[] = "`$field_ts`";
        $insert_values[] = "'$tanggal_selesai'";
    }

    if (in_array('created_at', $columns)) {
        $insert_fields[] = "`created_at`";
        $insert_values[] = "NOW()";
    }

    $sql = "INSERT INTO t_tahun_ajaran (" . implode(', ', $insert_fields) . ") VALUES (" . implode(', ', $insert_values) . ")";
    $simpan = mysqli_query($koneksi, $sql);

    if ($simpan) {
        echo "<script>alert('Tahun ajaran berhasil ditambahkan!'); window.location='kelola_tahun_ajaran.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data: " . addslashes(mysqli_error($koneksi)) . "'); window.location='tambah_tahun_ajaran.php';</script>";
    }

} else {
    header("Location: kelola_tahun_ajaran.php");
    exit();
}
?>