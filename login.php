<?php
require_once __DIR__ . '/helpers.php';
$koneksi = $GLOBALS['koneksi'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleSel  = trim($_POST['role'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Mohon isi username dan password.';
    } else {
        $u = mysqli_real_escape_string($koneksi, $username);
        $q = "SELECT id,username,password,role,name,nim FROM users WHERE username = '$u' LIMIT 1";
        $res = mysqli_query($koneksi, $q);
        if ($res && mysqli_num_rows($res) === 1) {
            $row = mysqli_fetch_assoc($res);
            if ($row['password'] === $password) {
                //session
                $_SESSION['user'] = [
                    'id' => (int)$row['id'],
                    'username' => $row['username'],
                    'role' => $row['role'],
                    'name' => $row['name'],
                    'nim' => $row['nim'] ?? null
                ];
                //sesuai role
                if ($row['role'] === 'tu') header('Location: tu_ui.php');
                else header('Location: ms_ui.php');
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="Login.css">
  <style>
    .role-toggle { margin: 0 auto 16px; display:flex; gap:8px; max-width:320px; }
    .role-btn { padding:10px 14px; border-radius:10px; cursor:pointer; border:0; font-weight:700; }
    .role-btn.active { box-shadow: 0 6px 18px rgba(3,55,127,0.18); transform: translateY(-1px); }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" style="background: radial-gradient(1200px 600px at 10% 20%, rgba(255,255,255,0.06), transparent 10%), linear-gradient(135deg, #78c7ff 0%, #36a6ff 40%, #2b98e6 100%);">
  <div class="login-wrap">
    <div class="login-card p-4">
      <h3 class="login-title text-center">Login</h3>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" id="loginForm">
        <div class="role-toggle" role="tablist" aria-label="Pilih role">
          <button type="button" id="role-tu" class="role-btn" data-role="tu">Tata Usaha</button>
          <button type="button" id="role-mahasiswa" class="role-btn active" data-role="mahasiswa">Mahasiswa</button>
        </div>
        <input type="hidden" name="role" id="roleInput" value="mahasiswa">

        <div class="mb-3">
          <input type="text" name="username" class="form-control fancy-input" placeholder="Username" required>
        </div>
        <div class="mb-3">
          <input type="password" name="password" class="form-control fancy-input" placeholder="Password" required>
        </div>

        <button class="btn btn-login w-100" type="submit">MASUK</button>
      </form>
    </div>
  </div>

<script>
  (function(){
    const roleInput = document.getElementById('roleInput');
    const btnMhs = document.getElementById('role-mahasiswa');
    const btnTu = document.getElementById('role-tu');
    function setRole(r) {
      roleInput.value = r;
      if (r === 'tu') {
        btnTu.classList.add('active');
        btnMhs.classList.remove('active');
      } else {
        btnMhs.classList.add('active');
        btnTu.classList.remove('active');
      }
    }
    btnMhs.addEventListener('click', () => setRole('mahasiswa'));
    btnTu.addEventListener('click', () => setRole('tu'));
    setRole('mahasiswa');
  })();
</script>
</body>
</html>
