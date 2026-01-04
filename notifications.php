<?php
//halaman untuk menampilkan riwayat notifikasi berdasarkan nim
require_once __DIR__ . '/helpers.php';
$user = require_login();
$koneksi = $GLOBALS['koneksi'];

$nim_q = trim($_GET['nim'] ?? '');

$notifications = [];
if (($user['role'] ?? '') === 'tu') {
    if ($nim_q !== '') {
        $res = mysqli_query($koneksi, "SELECT * FROM notifications WHERE to_all = 1 OR nim = '" . mysqli_real_escape_string($koneksi,$nim_q) . "' ORDER BY ts DESC");
    } else {
        $res = mysqli_query($koneksi, "SELECT * FROM notifications ORDER BY ts DESC");
    }
} else {
    $nim = $user['nim'] ?? null;
    if ($nim) {
        $res = mysqli_query($koneksi, "SELECT * FROM notifications WHERE to_all = 1 OR nim = '" . mysqli_real_escape_string($koneksi,$nim) . "' ORDER BY ts DESC");
    } else {
        $res = false;
    }
}

if ($res) while ($r = mysqli_fetch_assoc($res)) $notifications[] = $r;
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Riwayat SP Mahasiswa</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h4>Riwayat SP Mahasiswa <?= $nim_q ? '(' . esc($nim_q) . ')' : '' ?></h4>
    <a href="<?= ($user['role'] === 'tu') ? 'tu_ui.php' : 'ms_ui.php' ?>" class="btn btn-sm btn-secondary mb-3">Kembali</a>
    <?php if (empty($notifications)): ?>
      <div class="text-muted">Tidak ada notifikasi</div>
    <?php else: ?>
      <table class="table table-striped">
        <thead><tr><th>Waktu</th><th>NIM</th><th>Pesan</th><th>Dari</th></tr></thead>
        <tbody>
        <?php foreach ($notifications as $n): ?>
          <tr>
            <td><?= esc($n['ts']) ?></td>
            <td><?= esc($n['nim']) ?></td>
            <td><?= esc($n['message']) ?></td>
            <td><?= esc($n['actor'] ?? 'system') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>