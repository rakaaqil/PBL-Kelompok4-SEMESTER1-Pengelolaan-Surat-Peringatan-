<?php
// menambahkan notifikasi otomatis jika status mahasiswa berubah
require_once __DIR__ . '/helpers.php';
$u = require_tu();
$koneksi = $GLOBALS['koneksi'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tu_ui.php');
    exit;
}

$nim = trim($_POST['nim'] ?? '');
$name = trim($_POST['name'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$status = trim($_POST['status'] ?? 'AKTIF');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

if ($nim === '') {
    header('Location: tu_ui.php?error=' . urlencode('NIM wajib'));
    exit;
}

$nim_s = mysqli_real_escape_string($koneksi, $nim);

$res = mysqli_query($koneksi, "SELECT status FROM students WHERE nim = '$nim_s' LIMIT 1");
$old_status = null;
if ($res && mysqli_num_rows($res) === 1) {
    $row = mysqli_fetch_assoc($res);
    $old_status = $row['status'];
}

//sanitize 
$name_s = mysqli_real_escape_string($koneksi, $name);
$kelas_s = mysqli_real_escape_string($koneksi, $kelas);
$status_s = mysqli_real_escape_string($koneksi, $status);
$phone_s = mysqli_real_escape_string($koneksi, $phone);
$email_s = mysqli_real_escape_string($koneksi, $email);
$alamat_s = mysqli_real_escape_string($koneksi, $alamat);

//perbarui student
$q = "UPDATE students SET name='$name_s', kelas='$kelas_s', status='$status_s', phone='$phone_s', email='$email_s', alamat='$alamat_s' WHERE nim='$nim_s'";
$ok = mysqli_query($koneksi, $q);

if ($ok) {
    //jika status berubah, masukkan notifikasi(riwayat)
    if ($old_status === null || strtoupper($old_status) !== strtoupper($status_s)) {
        if (strtoupper($status_s) === 'AKTIF') {
            $msg = "Status kembali AKTIF";
        } else {
            $msg = "Anda mendapatkan " . strtoupper($status_s);
        }
        $msg_s = mysqli_real_escape_string($koneksi, $msg);
        $actor = mysqli_real_escape_string($koneksi, $u['username'] ?? 'system');
        mysqli_query($koneksi, "INSERT INTO notifications (to_all,nim,message,level,actor,ts) VALUES (0,'$nim_s','$msg_s','" . mysqli_real_escape_string($koneksi,$status_s) . "','$actor',NOW())");
    }

    //pastikan akun pengguna sudah ada untuk students (username = nim)
    $ucheck = mysqli_query($koneksi, "SELECT id FROM users WHERE nim = '$nim_s' LIMIT 1");
    if (!$ucheck || mysqli_num_rows($ucheck) === 0) {
        $uuser = mysqli_real_escape_string($koneksi, $nim);
        $upass = mysqli_real_escape_string($koneksi, $nim);
        $uname = mysqli_real_escape_string($koneksi, $name);
        mysqli_query($koneksi, "INSERT INTO users (username,password,role,name,nim) VALUES ('$uuser','$upass','mahasiswa','$uname','$nim_s')");
    } else {
        //juga update nama di tabel 
        mysqli_query($koneksi, "UPDATE users SET name='" . mysqli_real_escape_string($koneksi,$name) . "' WHERE nim='$nim_s'");
    }

    header('Location: tu_ui.php?success=' . urlencode('Data mahasiswa diperbarui'));
    exit;
} else {
    header('Location: tu_ui.php?error=' . urlencode('Gagal update: ' . mysqli_error($koneksi)));
    exit;
}
?>