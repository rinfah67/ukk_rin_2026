<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/koneksi.php';

if (isset($_POST['update'])) {
    $id              = mysqli_real_escape_string($koneksi, $_POST['id']);
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

    $col_id     = in_array('id', $columns) ? 'id' : (in_array('id_tahun_ajaran', $columns) ? 'id_tahun_ajaran' : $columns[0]);
    $col_status = in_array('status', $columns) ? 'status' : (in_array('stt', $columns) ? 'stt' : 'status');

    // 2. PAKSA Nonaktifkan Semua Record Lain Kecuali Data yang Sedang Di-edit
    if ($status === 'Aktif') {
        mysqli_query($koneksi, "UPDATE t_tahun_ajaran SET `$col_status` = 'Tidak Aktif' WHERE `$col_id` != '$id'");
    }

    // Deteksi nama kolom untuk 'tahun_ajaran'
    $col_tahun = 'tahun_ajaran';
    if (in_array('tahun_ajaran', $columns)) { $col_tahun = 'tahun_ajaran'; }
    elseif (in_array('tahun', $columns)) { $col_tahun = 'tahun'; }
    elseif (in_array('nama_tahun', $columns)) { $col_tahun = 'nama_tahun'; }
    else { $col_tahun = isset($columns[1]) ? $columns[1] : 'tahun_ajaran'; }

    // 3. Susun Query Update
    $update_pairs = [
        "`$col_tahun` = '$tahun_ajaran'",
        "`$col_status` = '$status'"
    ];

    if ($tanggal_mulai && (in_array('tanggal_mulai', $columns) || in_array('tgl_mulai', $columns))) {
        $field_tm = in_array('tanggal_mulai', $columns) ? 'tanggal_mulai' : 'tgl_mulai';
        $update_pairs[] = "`$field_tm` = '$tanggal_mulai'";
    }

    if ($tanggal_selesai && (in_array('tanggal_selesai', $columns) || in_array('tgl_selesai', $columns))) {
        $field_ts = in_array('tanggal_selesai', $columns) ? 'tanggal_selesai' : 'tgl_selesai';
        $update_pairs[] = "`$field_ts` = '$tanggal_selesai'";
    }

    if (in_array('updated_at', $columns)) {
        $update_pairs[] = "`updated_at` = NOW()";
    }

    $sql = "UPDATE t_tahun_ajaran SET " . implode(', ', $update_pairs) . " WHERE `$col_id` = '$id'";
    $update = mysqli_query($koneksi, $sql);

    if ($update) {
        echo "<script>alert('Tahun ajaran berhasil diperbarui!'); window.location='kelola_tahun_ajaran.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . addslashes(mysqli_error($koneksi)) . "'); window.location='edit_tahun_ajaran.php?id=$id';</script>";
    }

} else {
    header("Location: kelola_tahun_ajaran.php");
    exit();
}
?>