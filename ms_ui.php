<?php
//halaman mahasiswa melihat profil & riwayat
require_once __DIR__ . '/helpers.php';
$u = require_login();
if (($u['role'] ?? '') !== 'mahasiswa') {
    if (($u['role'] ?? '') === 'tu') {
        header('Location: tu_ui.php');
        exit;
    }
    header('Location: login.php');
    exit;
}
$koneksi = $GLOBALS['koneksi'];
$nim = $u['nim'] ?? null;

$student = null;
if ($nim) {
    $res = mysqli_query($koneksi, "SELECT * FROM students WHERE nim = '" . mysqli_real_escape_string($koneksi,$nim) . "' LIMIT 1");
    if ($res && mysqli_num_rows($res) === 1) $student = mysqli_fetch_assoc($res);
}

$notifications = [];
if ($nim) {
    $res = mysqli_query($koneksi, "SELECT * FROM notifications WHERE to_all = 1 OR nim = '" . mysqli_real_escape_string($koneksi,$nim) . "' ORDER BY ts DESC");
    if ($res) while ($r = mysqli_fetch_assoc($res)) $notifications[] = $r;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mahasiswa - Riwayat Status</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="ms_ui.css">
</head>
<body class="page-bg min-vh-100">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div><img src="logo.png" height="48" alt="logo"> <strong class="ms-2">Selamat datang, <?= esc($u['name'] ?? $u['username']) ?></strong></div>
      <div><a href="logout.php" class="btn btn-outline-secondary">Keluar</a></div>
    </div>

    <div class="row g-3">
      <div class="col-lg-4">
        <div class="profile-card p-3 rounded-3">
          <div class="avatar mb-3 mx-auto"></div>
          <div id="profileDetails" class="text-start mt-2">
            <p><strong>Nama:</strong> <?= esc($student['name'] ?? ($u['name'] ?? '-')) ?></p>
            <p><strong>NIM:</strong> <?= esc($student['nim'] ?? $u['nim'] ?? '-') ?></p>
            <p><strong>Kelas:</strong> <?= esc($student['kelas'] ?? '-') ?></p>
            <p><strong>Status:</strong> <?= esc($student['status'] ?? 'AKTIF') ?></p>
            <p><strong>Telepon:</strong> <?= esc($student['phone'] ?? '-') ?></p>
            <p><strong>Email:</strong> <?= esc($student['email'] ?? '-') ?></p>
            <p><strong>Alamat:</strong> <?= esc($student['alamat'] ?? '-') ?></p>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="app-frame p-3">
          <h5>Riwayat Status Surat Peringatan</h5>
          <?php if (empty($notifications)): ?>
            <div class="text-muted">Belum ada pemberitahuan.</div>
          <?php else: ?>
            <div class="list-group">
              <?php foreach ($notifications as $n): ?>
                <div class="list-group-item">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1"><?= esc($n['message']) ?></h6>
                    <small><?= esc($n['ts']) ?></small>
                  </div>
                  <p class="mb-1"><strong>Dari:</strong> <?= esc($n['actor'] ?? 'system') ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="mt-4 small text-muted">© Polibatam <?= date('Y') ?></div>
  </div>
</body>
</html>
