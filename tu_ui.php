<?php
require_once __DIR__ . '/helpers.php';
$user = require_tu();
$koneksi = $GLOBALS['koneksi'];

$students = [];
$res = mysqli_query($koneksi, "SELECT * FROM students ORDER BY created_at DESC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) $students[] = $r;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>TU - Kelola Mahasiswa</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom CSS -->
<link rel="stylesheet" href="tu_ui.css">

<style>
.small-action{ font-size:13px; }
</style>
</head>
<body class="page-bg min-vh-100">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div><img src="logo.png" height="48" alt="logo"> <strong class="ms-2">SURAT PERINGATAN</strong></div>
      <div>
        <span class="me-3">Halo, <?= esc($user['username']) ?></span>
        <a href="logout.php" class="btn btn-outline-secondary">Keluar</a>
      </div>
    </div>

    <div class="mb-3 d-flex justify-content-between align-items-center">
      <div class="search-wrap me-3" style="flex:1 1 60%; max-width:60%;">
        <input id="searchInput" class="form-control" placeholder="Cari mahasiswa (nama / NIM)">
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Tambah Mahasiswa</button>
        <button id="btnManage" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manageModal">Kelola SP</button>
      </div>
    </div>

    <div class="app-frame">
      <div class="table-wrap p-3 rounded-3">
        <div class="table-responsive">
          <table class="table table-borderless mb-0">
            <thead>
              <tr><th>Nama</th><th>NIM</th><th>Kelas</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody id="studentTbody">
              <?php foreach ($students as $s): ?>
                <tr>
                  <td><?= esc($s['name']) ?></td>
                  <td><?= esc($s['nim']) ?></td>
                  <td><?= esc($s['kelas']) ?></td>
                  <td><?= esc($s['status']) ?></td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-success small-action" onclick="openEdit('<?= esc($s['nim']) ?>','<?= esc(addslashes($s['name'])) ?>','<?= esc($s['kelas']) ?>','<?= esc($s['status']) ?>','<?= esc($s['phone']) ?>','<?= esc($s['email']) ?>','<?= esc($s['alamat']) ?>')">Edit</button>
                    <a class="btn btn-sm btn-danger small-action" href="hapus_mhs.php?nim=<?= urlencode($s['nim']) ?>" onclick="return confirm('Hapus mahasiswa <?= esc($s['nim']) ?>?')">Hapus</a>
                    <a class="btn btn-sm btn-secondary small-action" href="notifications.php?nim=<?= urlencode($s['nim']) ?>">Riwayat</a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($students)): ?>
                <tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="mt-4 small text-muted">© Polibatam <?= date('Y') ?></div>
    </div>
  </div>

  <!-- Tambah Popup -->
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="post" action="tambah_mhs.php">
        <div class="modal-header"><h5 class="modal-title">Tambah Mahasiswa</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nama</label><input name="name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">NIM</label><input name="nim" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Kelas</label><input name="kelas" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="AKTIF">AKTIF</option>
              <option value="SP1">SP1</option>
              <option value="SP2">SP2</option>
              <option value="SP3">SP3</option>
              <option value="DROP">DROP</option>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Telepon</label><input name="phone" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Email</label><input name="email" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
      </form>
    </div>
  </div>

  <!-- Edit Popup -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="post" action="ubah_mhs.php">
        <div class="modal-header"><h5 class="modal-title">Edit Mahasiswa</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
          <input type="hidden" name="nim" id="edit-nim">
          <div class="mb-3"><label class="form-label">Nama</label><input name="name" id="edit-name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Kelas</label><input name="kelas" id="edit-kelas" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Status</label>
            <select name="status" id="edit-status" class="form-select">
              <option value="AKTIF">AKTIF</option>
              <option value="SP1">SP1</option>
              <option value="SP2">SP2</option>
              <option value="SP3">SP3</option>
              <option value="DROP">DROP</option>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Telepon</label><input name="phone" id="edit-phone" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Email</label><input name="email" id="edit-email" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="edit-alamat" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Batal</button><button type="submit" class="btn btn-primary">Perbarui</button></div>
      </form>
    </div>
  </div>

  <!-- Manage popup -->
  <div class="modal fade" id="manageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" method="post" action="manage_action.php" onsubmit="return confirmManageAction();">
        <div class="modal-header">
          <h5 class="modal-title">Kelola Mahasiswa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 d-flex gap-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="bulk_action" id="action_clear" value="clearstatus" checked>
              <label class="form-check-label" for="action_clear">Reset Status ke AKTIF</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="bulk_action" id="action_delete" value="delete">
              <label class="form-check-label" for="action_delete">Hapus Mahasiswa</label>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th style="width:40px;"><input type="checkbox" id="masterCheckbox"></th>
                  <th>Nama</th>
                  <th>NIM</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                  <td><input type="checkbox" name="nims[]" value="<?= esc($s['nim']) ?>" class="rowCheckbox"></td>
                  <td><?= esc($s['name']) ?></td>
                  <td><?= esc($s['nim']) ?></td>
                  <td><?= esc($s['status']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($students)): ?>
                  <tr><td colspan="4" class="text-center text-muted">Tidak ada mahasiswa</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-danger">Jalankan</button>
        </div>
      </form>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
//buka popup edit 
function openEdit(nim,name,kelas,status,phone,email,alamat){
  document.getElementById('edit-nim').value = nim;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-kelas').value = kelas;
  document.getElementById('edit-status').value = status;
  document.getElementById('edit-phone').value = phone;
  document.getElementById('edit-email').value = email;
  document.getElementById('edit-alamat').value = alamat;
  var em = new bootstrap.Modal(document.getElementById('editModal'));
  em.show();
}

//pencarian simpel client-side 
document.getElementById('searchInput').addEventListener('input', function(){
  var q = this.value.toLowerCase();
  document.querySelectorAll('#studentTbody tr').forEach(function(tr){
    var text = tr.innerText.toLowerCase();
    tr.style.display = text.indexOf(q) === -1 ? 'none' : '';
  });
});

//master checkbox
document.addEventListener('DOMContentLoaded', function(){
  var master = document.getElementById('masterCheckbox');
  if (master) {
    master.addEventListener('change', function(){
      var rows = document.querySelectorAll('.rowCheckbox');
      rows.forEach(function(cb){ cb.checked = master.checked; });
    });
  }
});

function confirmManageAction(){
  var checked = document.querySelectorAll('input.rowCheckbox:checked').length;
  if (!checked) { alert('Pilih minimal satu mahasiswa.'); return false; }
  var action = document.querySelector('input[name="bulk_action"]:checked').value;
  if (action === 'delete') {
    return confirm('Yakin menghapus mahasiswa terpilih? Tindakan ini tidak dapat dibatalkan.');
  }
  return confirm('Jalankan aksi pada ' + checked + ' mahasiswa?');
}
</script>
</body>
</html>
