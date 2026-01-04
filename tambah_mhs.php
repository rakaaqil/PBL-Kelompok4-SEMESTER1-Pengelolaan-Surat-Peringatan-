<?php
//proses tambah mahasiswa
//membuat notifikasi otomatis
require_once __DIR__ . '/helpers.php';
$u = require_tu();
$koneksi = $GLOBALS['koneksi'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tu_ui.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$nim = trim($_POST['nim'] ?? '');
$kelas = trim($_POST['kelas'] ?? '');
$status = trim($_POST['status'] ?? 'AKTIF');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

if ($name === '' || $nim === '') {
    //validation
    header('Location: tu_ui.php?error=' . urlencode('Nama dan NIM wajib'));
    exit;
}

//sanitize
$name_s = mysqli_real_escape_string($koneksi, $name);
$nim_s  = mysqli_real_escape_string($koneksi, $nim);
$kelas_s= mysqli_real_escape_string($koneksi, $kelas);
$status_s= mysqli_real_escape_string($koneksi, $status);
$phone_s = mysqli_real_escape_string($koneksi, $phone);
$email_s = mysqli_real_escape_string($koneksi, $email);
$alamat_s= mysqli_real_escape_string($koneksi, $alamat);

$check = mysqli_query($koneksi, "SELECT id FROM students WHERE nim='$nim_s' LIMIT 1");
if ($check && mysqli_num_rows($check) > 0) {
    header('Location: tu_ui.php?error=' . urlencode('NIM sudah terdaftar'));
    exit;
}

//insert student
$q = "INSERT INTO students (name,nim,kelas,status,phone,email,alamat) VALUES ('$name_s','$nim_s','$kelas_s','$status_s','$phone_s','$email_s','$alamat_s')";
$ok = mysqli_query($koneksi, $q);

if ($ok) {
    //membuat akun mahasiswa (username = nim, password = nim)
    $ucheck = mysqli_query($koneksi, "SELECT id FROM users WHERE nim = '" . mysqli_real_escape_string($koneksi,$nim) . "' LIMIT 1");
    if (!$ucheck || mysqli_num_rows($ucheck) === 0) {
        $uuser = mysqli_real_escape_string($koneksi, $nim);
        $upass = mysqli_real_escape_string($koneksi, $nim);
        $uname = mysqli_real_escape_string($koneksi, $name);
        mysqli_query($koneksi, "INSERT INTO users (username,password,role,name,nim) VALUES ('$uuser','$upass','mahasiswa','$uname','$nim_s')");
    }

    header('Location: tu_ui.php?success=' . urlencode('Berhasil menambah mahasiswa'));
    exit;
} else {
    header('Location: tu_ui.php?error=' . urlencode('Gagal menyimpan: ' . mysqli_error($koneksi)));
    exit;
}
?>