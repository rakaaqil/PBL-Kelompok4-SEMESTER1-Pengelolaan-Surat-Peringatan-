// tu_ui.js - updated: move notification panel to body and position it dynamically under the bell button
document.addEventListener('DOMContentLoaded', () => {
  if (typeof Auth === 'undefined' || typeof DataAPI === 'undefined') {
    console.error('Auth or DataAPI missing');
    return;
  }
  // Allow TU and DOSEN to access this page
  if (!Auth.requireRole(['tu', 'dosen'])) return;

  const session = DataAPI.getCurrentUser();
  if (!session) return;
  const ACTOR = session.username || session.name || 'user_tu';

  // update UI user id if possible
  const userIdEl = document.querySelector('.user-id');
  if (userIdEl) userIdEl.textContent = ACTOR;

  // DOM refs
  const tbody = document.getElementById('studentTbody');
  const classListEl = document.getElementById('classList');
  const searchInput = document.getElementById('searchInput');
  const notifBtn = document.getElementById('notifBtn');
  let notifPanel = document.getElementById('notifPanel'); // will be moved to body
  const notifItems = document.getElementById('notifItems');
  const addForm = document.getElementById('addForm');
  const addModalEl = document.getElementById('addModal');
  const liveToastEl = document.getElementById('liveToast');
  const liveToast = new bootstrap.Toast(liveToastEl);

  const statusMap = {
    SP1: {label: 'SURAT PERINGATAH 1', cls: 'bad-sp1'},
    SP2: {label: 'SURAT PERINGATAH 2', cls: 'bad-sp2'},
    SP3: {label: 'SURAT PERINGATAH 3', cls: 'bad-sp3'},
    DROP: {label: 'DROP OUT', cls: 'bad-drop'},
    AKTIF: {label: 'AKTIF', cls: ''}
  };

  // Move notifPanel to body so it's outside any stacking/clipping parent
  if (notifPanel && notifPanel.parentElement !== document.body) {
    // hide before moving to avoid visual glitch
    notifPanel.classList.add('d-none');
    // set initial aria-hidden
    notifPanel.setAttribute('aria-hidden', 'true');
    document.body.appendChild(notifPanel);
    notifPanel = document.getElementById('notifPanel');
  }

  function getAllStudents() { return DataAPI.getStudents(); }
  function getClassList(students) { return Array.from(new Set(students.map(s => s.kelas || 'Lainnya'))).sort(); }

  function renderClassList(classes) {
    classListEl.innerHTML = '';
    const liAll = document.createElement('li');
    liAll.innerHTML = `<a class="dropdown-item" href="#" data-class="all">Semua Kelas</a>`;
    classListEl.appendChild(liAll);
    classes.forEach(k => {
      const li = document.createElement('li');
      li.innerHTML = `<a class="dropdown-item" href="#" data-class="${k}">${k}</a>`;
      classListEl.appendChild(li);
    });
  }

  // ... rest of table rendering logic unchanged (omitted here for brevity) ...
  // (In your real file keep the existing functions: renderTable, applyFilters, etc.)
  // For completeness, below are the existing omitted functions re-used from original file:

  function renderTable(data) {
    tbody.innerHTML = '';
    if (!data.length) {
      tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>`;
      return;
    }
    data.forEach((s, idx) => {
      const tr = document.createElement('tr');
      tr.dataset.kelas = s.kelas;
      tr.innerHTML = `
        <td>${s.name}</td>
        <td>${s.nim}</td>
        <td class="text-end">
          <div class="btn-group">
            <button class="btn status-btn ${statusMap[s.status]?.cls || ''}" type="button" id="statusBtn-${idx}" data-bs-toggle="dropdown" aria-expanded="false">
              ${statusMap[s.status]?.label || s.status || 'AKTIF'}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item change-status" href="#" data-status="SP1" data-nim="${s.nim}">SURAT PERINGATAN 1</a></li>
              <li><a class="dropdown-item change-status" href="#" data-status="SP2" data-nim="${s.nim}">SURAT PERINGATAN 2</a></li>
              <li><a class="dropdown-item change-status" href="#" data-status="SP3" data-nim="${s.nim}">SURAT PERINGATAN 3</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item change-status text-danger" href="#" data-status="DROP" data-nim="${s.nim}">DROP OUT</a></li>
            </ul>
          </div>
        </td>
      `;
      tbody.appendChild(tr);
    });

    // attach status change handlers
    tbody.querySelectorAll('.change-status').forEach(el => {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        const newStatus = el.dataset.status;
        const nim = el.dataset.nim;
        DataAPI.addNotification({
          to: nim,
          nim,
          message: `Anda mendapatkan ${newStatus}`,
          level: newStatus,
          actor: ACTOR,
          note: `Diubah oleh ${ACTOR}`
        });
        showToast(`Status ${nim} -> ${newStatus} tersimpan dan notifikasi dikirim`);
        refreshUI();
      });
    });
  }

  let currentClassFilter = 'all';
  function applyFilters() {
    const q = (searchInput.value || '').trim().toLowerCase();
    const students = getAllStudents();
    const filtered = students.filter(s => {
      const matchClass = (currentClassFilter === 'all' || (s.kelas || '') === currentClassFilter);
      const matchQuery = (s.name.toLowerCase().includes(q) || s.nim.includes(q));
      return matchClass && matchQuery;
    });
    renderTable(filtered);
  }

  classListEl.addEventListener('click', (ev) => {
    ev.preventDefault();
    const a = ev.target.closest('a[data-class]');
    if (!a) return;
    currentClassFilter = a.dataset.class;
    document.getElementById('classDropdown').textContent = a.textContent;
    applyFilters();
  });

  searchInput.addEventListener('input', () => applyFilters());

  // --- NOTIF: dynamic positioning logic below ---

  // Compute panel coords to sit below the notifBtn, with viewport clamping.
  function positionNotifPanel() {
    if (!notifPanel || !notifBtn) return;
    const btnRect = notifBtn.getBoundingClientRect();
    const panelRect = notifPanel.getBoundingClientRect();
    const margin = 8; // small gap between button and panel

    // Preferred: align panel's top to button bottom, right edge aligned with button's right
    let top = Math.round(window.scrollY + btnRect.bottom + margin);
    let left = Math.round(window.scrollX + btnRect.right - panelRect.width);

    // If panel would overflow right edge, shift left
    const overflowRight = left + panelRect.width - (window.scrollX + window.innerWidth);
    if (overflowRight > 0) left -= overflowRight + 12;

    // If panel would overflow left edge, clamp to 12px
    if (left < window.scrollX + 12) left = window.scrollX + 12;

    // If not enough space below, open above the button
    const spaceBelow = window.innerHeight - btnRect.bottom;
    if (spaceBelow < panelRect.height + margin && btnRect.top > panelRect.height + margin) {
      // place above
      top = Math.round(window.scrollY + btnRect.top - panelRect.height - margin);
    }

    notifPanel.style.top = top + 'px';
    notifPanel.style.left = left + 'px';
    // unset right to avoid CSS fixed-right interfering
    notifPanel.style.right = 'auto';

    // mark visible for CSS transitions
    notifPanel.setAttribute('aria-hidden', 'false');
  }

  function hideNotifPanel() {
    if (!notifPanel) return;
    notifPanel.classList.add('d-none');
    notifPanel.setAttribute('aria-hidden', 'true');
    // cleanup inline position style if desired
    // notifPanel.style.left = '';
    // notifPanel.style.top = '';
  }

  function showNotifPanel() {
    if (!notifPanel) return;
    // ensure content updated before measuring
    loadNotifPanel();
    notifPanel.classList.remove('d-none');
    // allow browser to apply display then measure
    requestAnimationFrame(() => {
      positionNotifPanel();
    });
  }

  // Toggle with robust event handling; stopPropagation so document click won't immediately close
  notifBtn.addEventListener('click', (evt) => {
    evt.stopPropagation();
    if (!notifPanel) return;
    if (notifPanel.classList.contains('d-none')) {
      showNotifPanel();
    } else {
      hideNotifPanel();
    }
  });

  // Close panel when clicking outside
  document.addEventListener('click', (e) => {
    if (!notifPanel) return;
    if (notifPanel.classList.contains('d-none')) return;
    const target = e.target;
    if (notifBtn.contains(target)) return; // ignore clicks on button
    if (!notifPanel.contains(target)) {
      hideNotifPanel();
    }
  });

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && notifPanel && !notifPanel.classList.contains('d-none')) {
      hideNotifPanel();
    }
  });

  // Reposition on resize/scroll so panel stays attached to button
  window.addEventListener('resize', () => {
    if (!notifPanel || notifPanel.classList.contains('d-none')) return;
    positionNotifPanel();
  });
  window.addEventListener('scroll', () => {
    if (!notifPanel || notifPanel.classList.contains('d-none')) return;
    positionNotifPanel();
  }, { passive: true });

  function loadNotifPanel() {
    const notifs = DataAPI.getNotifications().slice().reverse();
    notifItems.innerHTML = '';
    if (!notifs.length) {
      notifItems.innerHTML = '<div class="text-muted small">Tidak ada pemberitahuan</div>';
      return;
    }
    notifs.forEach(n => {
      const div = document.createElement('div');
      div.className = 'notif-item';
      div.innerHTML = `<div style="font-weight:700">${n.actor || 'system'}</div><div style="font-size:13px;">${n.message}</div><div style="font-size:11px;color:#666">${new Date(n.ts).toLocaleString()}</div>`;
      notifItems.appendChild(div);
    });
  }

  function showToast(text) {
    document.getElementById('liveToastBody').textContent = text;
    liveToast.show();
  }

  // ... rest of original code for add form, storage listeners, refreshUI etc. unchanged ...
  addForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = document.getElementById('addName').value.trim();
    const nim = document.getElementById('addNim').value.trim();
    const level = document.getElementById('addLevel').value;
    const note = document.getElementById('addNote').value.trim();
    if (!name || !nim || !level) { alert('Mohon isi Nama, NIM, dan Level Surat.'); return; }

    const students = getAllStudents();
    const existing = students.find(s => s.nim === nim);
    if (!existing) {
      students.unshift({ name, nim, kelas: currentClassFilter === 'all' ? '' : currentClassFilter, status: level });
      DataAPI.saveStudents(students);
    } else {
      existing.name = name;
      existing.status = level;
      DataAPI.saveStudents(students);
    }

    // create notification and update student status
    DataAPI.addNotification({
      to: nim,
      nim,
      message: `Anda mendapatkan ${level}`,
      level,
      actor: ACTOR,
      note: note
    });

    // create student user account: username = name, password = nim
    try {
      DataAPI.createUser({ username: name, password: nim, role: 'mahasiswa', name, nim });
      showToast(`Akun mahasiswa dibuat: username="${name}" password="${nim}"`);
    } catch (err) {
      if (err && (err.message === 'username_exists' || err === 'username_exists')) {
        // already exists, ignore
      } else {
        console.warn('createUser error', err);
      }
    }

    const modal = bootstrap.Modal.getInstance(addModalEl);
    if (modal) modal.hide();
    addForm.reset();
    refreshUI();
    showToast(`Mahasiswa ${name} ditambahkan/diupdate dan notifikasi dikirim`);
  });

  function refreshUI() {
    const students = getAllStudents();
    const classes = getClassList(students);
    renderClassList(classes);
    applyFilters();
  }

  window.addEventListener('storage', (ev) => {
    if (ev.key === 'notifications' || ev.key === 'students' || ev.key === 'users') refreshUI();
  });
  window.addEventListener('dataapi-update', (ev) => {
    if (!ev.detail) return;
    const key = ev.detail.key;
    if (key === 'notifications' || key === 'students' || key === 'users') refreshUI();
  });

  // logout
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      DataAPI.logout();
      window.location.href = 'Login.html';
    });
  }

  refreshUI();
  window.TUUI = { refreshUI };
});