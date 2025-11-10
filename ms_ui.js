// ms_ui.js - updated: use session (DataAPI.getCurrentUser), require role, live updates
document.addEventListener('DOMContentLoaded', () => {
  if (typeof Auth === 'undefined' || typeof DataAPI === 'undefined') {
    console.error('Auth or DataAPI missing');
    return;
  }
  if (!Auth.requireRole(['mahasiswa'])) return;

  const session = DataAPI.getCurrentUser();
  if (!session) return;

  const CURRENT_NIM = session.nim || null;
  const CURRENT_USER = session.username || session.name || 'mahasiswa';

  // DOM refs
  const userLabel = document.getElementById('userLabel');
  const userIdEl = document.getElementById('userId');
  const profileDetails = document.getElementById('profileDetails');
  const notifBtn = document.getElementById('notifBtn');
  const notifPanel = document.getElementById('notifPanel');
  const notifItems = document.getElementById('notifItems');
  const statusBox = document.getElementById('statusBox');
  const statusTitle = document.getElementById('statusTitle');
  const statusMessage = document.getElementById('statusMessage');
  const appRoot = document.getElementById('app');
  const liveToast = new bootstrap.Toast(document.getElementById('liveToast'));

  userLabel.textContent = CURRENT_USER;
  userIdEl.textContent = CURRENT_USER;

  function renderProfile(student) {
    profileDetails.innerHTML = `
      <p><strong>Nama:</strong> ${student.name}</p>
      <p><strong>Nim:</strong> ${student.nim}</p>
      <p><strong>Kelas:</strong> ${student.kelas || '-'}</p>
      <p><strong>Status:</strong> ${student.status || 'AKTIF'}</p>
      <p><strong>No Telp:</strong> ${student.phone || '-'}</p>
      <p><strong>Email:</strong> ${student.email || '-'}</p>
      <p><strong>Alamat:</strong> ${student.alamat || '-'}</p>
    `;
  }

  function mapStatusToBg(status) {
    switch ((status || '').toUpperCase()) {
      case 'SP1': return 'sp1-bg';
      case 'SP2': return 'sp2-bg';
      case 'SP3': return 'sp3-bg';
      case 'DROP': return 'drop-bg';
      default: return 'neutral-bg';
    }
  }

  function mapStatusToTitle(status) {
    switch ((status || '').toUpperCase()) {
      case 'SP1': return 'KAMU TERKENA SP 1';
      case 'SP2': return 'KAMU TERKENA SP 2';
      case 'SP3': return 'KAMU TERKENA SP 3';
      case 'DROP': return 'KAMU TERKENA DROP OUT';
      default: return '';
    }
  }

  function loadNotifications() {
    const all = DataAPI.getNotifications();
    const mine = all.filter(n => n.to === 'all' || n.nim === CURRENT_NIM);
    mine.sort((a,b) => new Date(b.ts) - new Date(a.ts));
    notifItems.innerHTML = '';
    if (!mine.length) {
      notifItems.innerHTML = '<div class="text-muted small">Tidak ada pemberitahuan</div>';
      return;
    }
    mine.forEach(n => {
      const div = document.createElement('div');
      div.className = 'notif-item';
      div.dataset.id = n.id;
      div.innerHTML = `
        <div style="font-weight:700">${n.actor || 'system'}</div>
        <div style="font-size:13px; color:#333;">${n.message}</div>
        <div style="font-size:11px; color:#666; margin-top:6px;">${new Date(n.ts).toLocaleString()}</div>
      `;
      if (!n.read) div.style.border = '2px solid rgba(43,152,230,0.6)';
      div.addEventListener('click', () => {
        DataAPI.markNotificationRead(n.id);
        if (n.level) {
          statusBox.classList.remove('d-none');
          statusTitle.textContent = mapStatusToTitle(n.level);
          statusMessage.innerHTML = `<strong>Ditambahkan Oleh ${n.actor || ''}</strong><br><br>${n.note || '(tidak ada catatan)'}`;
          applyStudentBg(n.level);
          showToast(`Pemberitahuan diterima: ${n.message}`);
        } else {
          showToast(n.message);
        }
        loadNotifications();
      });
      notifItems.appendChild(div);
    });
  }

  function applyStudentBg(status) {
    appRoot.classList.remove('neutral-bg','sp1-bg','sp2-bg','sp3-bg','drop-bg');
    appRoot.classList.add(mapStatusToBg(status));
  }

  function showToast(text) {
    document.getElementById('liveToastBody').textContent = text;
    liveToast.show();
  }

  function loadStudentAndUI() {
    const students = DataAPI.getStudents();
    const me = students.find(s => s.nim === CURRENT_NIM);
    if (me) {
      renderProfile(me);
      if (me.status && me.status !== 'AKTIF') {
        statusBox.classList.remove('d-none');
        statusTitle.textContent = mapStatusToTitle(me.status);
        statusMessage.textContent = `Anda mendapatkan ${me.status}. Silakan cek pemberitahuan untuk detail.`;
        applyStudentBg(me.status);
      } else {
        statusBox.classList.add('d-none');
        applyStudentBg('');
      }
    } else {
      profileDetails.innerHTML = '<div class="text-danger">Data mahasiswa tidak ditemukan</div>';
    }
    loadNotifications();
  }

  notifBtn.addEventListener('click', (e) => {
    notifPanel.classList.toggle('d-none');
  });
  document.addEventListener('click', (e) => {
    if (!notifPanel.contains(e.target) && e.target !== notifBtn) {
      notifPanel.classList.add('d-none');
    }
  });

  // listen to storage and custom event
  window.addEventListener('storage', (ev) => {
    if (ev.key === 'notifications' || ev.key === 'students') loadStudentAndUI();
  });
  window.addEventListener('dataapi-update', (ev) => {
    if (!ev.detail) return;
    const key = ev.detail.key;
    if (key === 'notifications' || key === 'students') loadStudentAndUI();
  });

  // logout link
  const logoutLink = document.getElementById('logoutLink');
  if (logoutLink) {
    logoutLink.addEventListener('click', (e) => {
      e.preventDefault();
      DataAPI.logout();
      window.location.href = 'Login.html';
    });
  }

  loadStudentAndUI();
  window.StudentUI = { reload: loadStudentAndUI, applyBg: applyStudentBg };
});