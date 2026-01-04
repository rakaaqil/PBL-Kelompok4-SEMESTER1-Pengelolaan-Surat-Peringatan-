<?php
//hapus mahasiswa
require_once __DIR__ . '/helpers.php';
$u = require_tu();
$koneksi = $GLOBALS['koneksi'];

$nim = trim($_GET['nim'] ?? '');
if ($nim === '') {
    header('Location: tu_ui.php?error=' . urlencode('NIM tidak diberikan'));
    exit;
}
$nim_s = mysqli_real_escape_string($koneksi, $nim);

mysqli_begin_transaction($koneksi);
$ok = true;
try {
    mysqli_query($koneksi, "DELETE FROM students WHERE nim = '$nim_s'");
    mysqli_query($koneksi, "DELETE FROM notifications WHERE nim = '$nim_s'");
    mysqli_query($koneksi, "DELETE FROM users WHERE nim = '$nim_s'");
    mysqli_commit($koneksi);
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $ok = false;
}

if ($ok) header('Location: tu_ui.php?success=' . urlencode('Mahasiswa dihapus'));
else header('Location: tu_ui.php?error=' . urlencode('Gagal menghapus'));
exit;