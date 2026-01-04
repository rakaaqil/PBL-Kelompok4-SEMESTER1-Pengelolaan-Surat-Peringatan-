<?php
require_once __DIR__ . '/helpers.php';
$u = require_tu();
$koneksi = $GLOBALS['koneksi'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tu_ui.php');
    exit;
}

$action = $_POST['bulk_action'] ?? '';
$nims = $_POST['nims'] ?? [];

if (!is_array($nims) || empty($nims)) {
    header('Location: tu_ui.php?error=' . urlencode('Tidak ada mahasiswa terpilih'));
    exit;
}

$clean = [];
foreach ($nims as $nm) {
    $nm = trim($nm);
    if ($nm === '') continue;
    $clean[] = mysqli_real_escape_string($koneksi, $nm);
}
if (empty($clean)) {
    header('Location: tu_ui.php?error=' . urlencode('Tidak ada NIM valid'));
    exit;
}

if ($action === 'clearstatus') {
    //reset status menjadi AKTIF untuk setiap nim
    mysqli_begin_transaction($koneksi);
    try {
        $stmt = mysqli_prepare($koneksi, "UPDATE students SET status='AKTIF' WHERE nim = ?");
        foreach ($clean as $nim) {
            mysqli_stmt_bind_param($stmt, 's', $nim);
            mysqli_stmt_execute($stmt);
            //notification
            $msg = "Status kembali AKTIF";
            $actor = mysqli_real_escape_string($koneksi, $u['username'] ?? 'system');
            mysqli_query($koneksi, "INSERT INTO notifications (to_all,nim,message,level,actor,ts) VALUES (0,'$nim','" . mysqli_real_escape_string($koneksi,$msg) . "','AKTIF','$actor',NOW())");
        }
        mysqli_commit($koneksi);
        header('Location: tu_ui.php?success=' . urlencode('Status mahasiswa terpilih telah direset'));
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header('Location: tu_ui.php?error=' . urlencode('Gagal reset status: ' . $e->getMessage()));
        exit;
    }
} elseif ($action === 'delete') {
    mysqli_begin_transaction($koneksi);
    try {
        $delS = mysqli_prepare($koneksi, "DELETE FROM students WHERE nim = ?");
        $delN = mysqli_prepare($koneksi, "DELETE FROM notifications WHERE nim = ?");
        $delU = mysqli_prepare($koneksi, "DELETE FROM users WHERE nim = ?");
        foreach ($clean as $nim) {
            mysqli_stmt_bind_param($delS, 's', $nim); mysqli_stmt_execute($delS);
            mysqli_stmt_bind_param($delN, 's', $nim); mysqli_stmt_execute($delN);
            mysqli_stmt_bind_param($delU, 's', $nim); mysqli_stmt_execute($delU);
        }
        mysqli_commit($koneksi);
        header('Location: tu_ui.php?success=' . urlencode('Mahasiswa terpilih berhasil dihapus'));
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header('Location: tu_ui.php?error=' . urlencode('Gagal hapus: ' . $e->getMessage()));
        exit;
    }
} else {
    header('Location: tu_ui.php?error=' . urlencode('Aksi tidak dikenal'));
    exit;
}
?>