<?php
require_once __DIR__ . '/helpers.php';
$user = current_user();
$koneksi = $GLOBALS['koneksi'];

$totalMahasiswa = 0; $sp1 = $sp2 = $sp3 = $drop = 0;
$res = mysqli_query($koneksi, "SELECT COUNT(*) as c FROM students");
if ($res) { $row = mysqli_fetch_assoc($res); $totalMahasiswa = (int)$row['c']; }
$res = mysqli_query($koneksi, "SELECT COUNT(*) as c FROM students WHERE UPPER(status) = 'SP1'");
if ($res) { $row = mysqli_fetch_assoc($res); $sp1 = (int)$row['c']; }
$res = mysqli_query($koneksi, "SELECT COUNT(*) as c FROM students WHERE UPPER(status) = 'SP2'");
if ($res) { $row = mysqli_fetch_assoc($res); $sp2 = (int)$row['c']; }
$res = mysqli_query($koneksi, "SELECT COUNT(*) as c FROM students WHERE UPPER(status) = 'SP3'");
if ($res) { $row = mysqli_fetch_assoc($res); $sp3 = (int)$row['c']; }
$res = mysqli_query($koneksi, "SELECT COUNT(*) as c FROM students WHERE UPPER(status) LIKE 'DROP%'");
if ($res) { $row = mysqli_fetch_assoc($res); $drop = (int)$row['c']; }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard - Polibatam</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Dashboard.css">
</head>
<body>
  <header class="site-header py-3">
    <div class="container d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-3">
        <div class="logo-wrap"><img src="logo.png" alt="logo" class="logo-img"></div>
        <span class="dept-name">Politeknik Negeri Batam</span>
      </div>
      <div>
        <?php if ($user): ?>
          <a href="logout.php" class="btn btn-login">LOGOUT (<?= esc($user['username']) ?>)</a>
        <?php else: ?>
          <a href="login.php" class="btn btn-login">LOGIN</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <section class="bg-hero py-5">
    <div class="container">
      <div class="row align-items-start gx-4">
        <div class="col-lg-8 mb-4">
          <div class="hero-card-large p-4">
            <img src="Kampus.jpeg" alt="kampus" class="img-fluid rounded-main">
          </div>
        </div>
        <div class="col-lg-4">
          <div class="d-flex flex-column gap-4">
            <div class="stat-panel-blue text-center py-4">
              <div class="stat-number" data-target="<?= esc($totalMahasiswa) ?>"><?= esc($totalMahasiswa) ?></div>
              <div class="stat-label">Mahasiswa Terdaftar</div>
            </div>
            <div class="stat-panel-blue text-center py-4">
              <div class="stat-number" data-target="<?= esc($sp1) ?>"><?= esc($sp1) ?></div>
              <div class="stat-label">Mahasiswa SP1</div>
            </div>
            <div class="stat-panel-blue text-center py-4">
              <div class="stat-number" data-target="<?= esc($sp2 + $sp3 + $drop) ?>"><?= esc($sp2 + $sp3 + $drop) ?></div>
              <div class="stat-label">SP2 / SP3 / DO</div>
            </div>
          </div>
        </div>
      </div>
      <div class="row"><div class="col-12 text-center mt-4 footer-copy">© Polibatam <?= date('Y') ?></div></div>
    </div>
  </section>
</body>
</html>
