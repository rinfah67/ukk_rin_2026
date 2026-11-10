```php
<?php
// proses_edit_guru.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang boleh mengedit guru
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_guru.php");
    exit;
}

// Ambil data dari form
$id = (int) ($_POST['id'] ?? 0);
$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
$email = trim($_POST['email'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';

$password = $_POST['password'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

// Validasi data guru
if (
    $id <= 0 ||
    $nip === '' ||
    !preg_match('/^[0-9]{1,18}$/', $nip) ||
    $nama === '' ||
    !in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'], true) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !in_array($status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = "Data guru tidak valid.";
    header("Location: edit_guru.php?id=" . $id);
    exit;
}

// Validasi password jika ingin mengganti password
if ($password !== '' || $konfirmasi_password !== '') {

    if ($password === '' || $konfirmasi_password === '') {
        $_SESSION['pesan_error'] =
            "Password dan konfirmasi password harus diisi.";

        header("Location: edit_guru.php?id=" . $id);
        exit;
    }

    if ($password !== $konfirmasi_password) {
        $_SESSION['pesan_error'] =
            "Password dan konfirmasi password tidak sama.";

        header("Location: edit_guru.php?id=" . $id);
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['pesan_error'] =
            "Password minimal harus 8 karakter.";

        header("Location: edit_guru.php?id=" . $id);
        exit;
    }
}

// Mulai transaksi database
mysqli_begin_transaction($koneksi);

try {

    // Ambil user_id berdasarkan ID guru
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT user_id FROM t_guru WHERE id = ? FOR UPDATE"
    );

    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $guru = mysqli_fetch_assoc($hasil);

    mysqli_stmt_close($stmt);

    if (!$guru) {
        throw new Exception("Data guru tidak ditemukan.");
    }

    $user_id = (int) $guru['user_id'];

    // Update data guru
    $sql = "UPDATE t_guru SET
                nip = ?,
                nama = ?,
                jenis_kelamin = ?,
                email = ?,
                status_aktif = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }

    $status = (int) $status_aktif;

    mysqli_stmt_bind_param(
        $stmt,
        "ssssii",
        $nip,
        $nama,
        $jenis_kelamin,
        $email,
        $status,
        $id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    // Update password jika diisi
    if ($password !== '') {

        if ($user_id <= 0) {
            throw new Exception(
                "Guru belum memiliki akun pengguna."
            );
        }

        // Mengubah password menjadi hash
        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        if ($password_hash === false) {
            throw new Exception("Gagal mengenkripsi password.");
        }

        // Update password pada akun pengguna
        $sql = "UPDATE t_users
                SET password = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($koneksi, $sql);

        if (!$stmt) {
            throw new Exception(mysqli_error($koneksi));
        }

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $password_hash,
            $user_id
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }

        // Pastikan akun pengguna ditemukan
        if (mysqli_stmt_affected_rows($stmt) === 0) {

            mysqli_stmt_close($stmt);

            $cek = mysqli_prepare(
                $koneksi,
                "SELECT id FROM t_users WHERE id = ?"
            );

            if (!$cek) {
                throw new Exception(mysqli_error($koneksi));
            }

            mysqli_stmt_bind_param($cek, "i", $user_id);
            mysqli_stmt_execute($cek);

            $cek_hasil = mysqli_stmt_get_result($cek);
            $akun = mysqli_fetch_assoc($cek_hasil);

            mysqli_stmt_close($cek);

            if (!$akun) {
                throw new Exception("Akun pengguna tidak ditemukan.");
            }

        } else {
            mysqli_stmt_close($stmt);
        }
    }

    // Simpan seluruh perubahan
    mysqli_commit($koneksi);

    $_SESSION['pesan_sukses'] =
        "Data guru berhasil diperbarui.";

    header("Location: kelola_guru.php");
    exit;

} catch (Exception $e) {

    // Batalkan perubahan jika terjadi kesalahan
    mysqli_rollback($koneksi);

    $_SESSION['pesan_error'] =
        "Data guru gagal diperbarui: " . $e->getMessage();

    header("Location: edit_guru.php?id=" . $id);
    exit;
}
?>
```