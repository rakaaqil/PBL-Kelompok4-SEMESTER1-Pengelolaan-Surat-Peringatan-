// data-api.js
// Shared localStorage helper and simple auth for demo purposes.
// - students, notifications, users, session are stored in localStorage
// - createUser(opts) creates a user { username, password, role, name, nim }
// - authenticateUser(username,password) checks username/password plaintext (demo)
// - addNotification(opts) updates students[].status when level+n nim provided

(function (global) {
  const LS_STUDENTS = 'students';
  const LS_NOTIFS = 'notifications';
  const LS_USERS = 'users';
  const LS_SESSION = 'session';

  function _read(key) {
    try {
      const raw = localStorage.getItem(key);
      return raw ? JSON.parse(raw) : null;
    } catch (e) {
      console.error('DataAPI: parse error for', key, e);
      return null;
    }
  }

  function _write(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      // notify same-tab listeners
      try {
        window.dispatchEvent(new CustomEvent('dataapi-update', { detail: { key, value } }));
      } catch (err) {}
    } catch (e) {
      console.error('DataAPI: write error for', key, e);
    }
  }

  // Initialize sample data only if not present
  function ensureSampleData() {
    if (!_read(LS_STUDENTS)) {
      const sampleStudents = [
        { name: 'M.Rakha Aqil Syafiq', nim: '3312501102', kelas: 'IF 1D PAGI', status: 'SP1', phone: '081266971280', email: 'rakha0456@gmail.com', alamat: 'Jl. Kolam Air, No.81, Tanjung Batu' },
        { name: 'Nicolas Situmorang', nim: '3312501103', kelas: 'IF 1D PAGI', status: 'SP2', phone: '', email: '', alamat: '' },
        { name: 'Dzakwan Fahrezi', nim: '3312501101', kelas: 'IF 1D PAGI', status: 'SP3', phone: '', email: '', alamat: '' },
        { name: 'Joshua Sibuea', nim: '3312501104', kelas: 'IF 1D PAGI', status: 'DROP OUT', phone: '', email: '', alamat: '' },
        { name: 'Shyakina Ariniputri', nim: '3312502201', kelas: 'IF 1C PAGI', status: 'AKTIF', phone: '', email: '', alamat: '' },
        { name: 'Windah Brando Franko', nim: '3312501105', kelas: 'IF 1A MALAM', status: 'AKTIF', phone: '', email: '', alamat: '' },
        { name: 'Risky Saputra', nim: '3312501108', kelas: 'IF 1A PAGI', status: 'AKTIF', phone: '', email: '', alamat: '' },
        { name: 'Arman Kurnia', nim: '3312501107', kelas: 'IF 1B PAGI', status: 'AKTIF', phone: '', email: '', alamat: '' },
        { name: 'Rahmadan Sahroni', nim: '3312501106', kelas: 'IF 1B PAGI', status: 'AKTIF', phone: '', email: '', alamat: '' },
        { name: 'Rasya Aprilio Rosidy', nim: '3312501108', kelas: 'IF 1C PAGI', status: 'AKTIF', phone: '', email: '', alamat: '' },
        { name: 'Natasya Sefiarama', nim: '3312501109', kelas: 'IF 1A PAGI', status: 'AKTIF', phone: '', email: '', alamat: '' },
        { name: 'Kevin Aditiya', nim: '3312501111', kelas: 'IF 1A MALAM', status: 'AKTIF', phone: '', email: '', alamat: '' }
      ];
      _write(LS_STUDENTS, sampleStudents);
    }
    if (!_read(LS_NOTIFS)) {
      _write(LS_NOTIFS, []);
    }
    if (!_read(LS_USERS)) {
    const students = _read(LS_STUDENTS) || [];
    const users = [];

    users.push({ username: 'user_tu_0456', password: '1234', role: 'tu', name: 'Tata Usaha Demo' });

    students.forEach(s => {
      users.push({
        username: s.name,
        password: s.nim,
        role: 'mahasiswa',
        name: s.name,
        nim: s.nim
      });
    });

    _write(LS_USERS, users);
  }
  }

  // Students
  function getStudents() {
    ensureSampleData();
    return _read(LS_STUDENTS) || [];
  }
  function saveStudents(arr) {
    if (!Array.isArray(arr)) throw new Error('saveStudents expects an array');
    _write(LS_STUDENTS, arr);
  }

  // Notifications
  function getNotifications() {
    ensureSampleData();
    return _read(LS_NOTIFS) || [];
  }

  function addNotification(opts) {
    ensureSampleData();
    opts = opts || {};
    const notifs = getNotifications();
    const id = 'n_' + Date.now() + "_" + Math.floor(Math.random() * 90000);
    const ts = new Date().toISOString();
    const notif = {
      id,
      to: opts.to || (opts.nim ? opts.nim : 'all'),
      nim: opts.nim || null,
      message: opts.message || (opts.level ? `Anda mendapatkan ${opts.level}` : ''),
      level: opts.level || null,
      actor: opts.actor || 'system',
      note: opts.note || '',
      ts,
      read: false
    };
    notifs.push(notif);
    _write(LS_NOTIFS, notifs);

    // If level + nim provided -> update or create student status
    if (opts.level && opts.nim) {
      const students = getStudents();
      const idx = students.findIndex(s => s.nim === opts.nim);
      if (idx !== -1) {
        students[idx].status = opts.level;
        if (opts.name) students[idx].name = opts.name;
        if (opts.kelas) students[idx].kelas = opts.kelas;
        _write(LS_STUDENTS, students);
      } else {
        const newStudent = {
          name: opts.name || ('Mahasiswa ' + opts.nim),
          nim: opts.nim,
          kelas: opts.kelas || '',
          status: opts.level,
          phone: opts.phone || '',
          email: opts.email || '',
          alamat: opts.alamat || ''
        };
        const newStudents = getStudents();
        newStudents.unshift(newStudent);
        _write(LS_STUDENTS, newStudents);
      }
    }

    return notif;
  }

  function markNotificationRead(id) {
    const notifs = getNotifications();
    const idx = notifs.findIndex(n => n.id === id);
    if (idx !== -1) {
      notifs[idx].read = true;
      _write(LS_NOTIFS, notifs);
      return true;
    }
    return false;
  }

  // Users & Auth
  function getUsers() {
    ensureSampleData();
    return _read(LS_USERS) || [];
  }

  // createUser: opts = { username, password, role, name?, nim? }
  function createUser(opts) {
    ensureSampleData();
    if (!opts || !opts.username || !opts.password || !opts.role) {
      throw new Error('createUser: username, password, role required');
    }
    const users = getUsers();
    const exists = users.find(u => u.username === opts.username);
    if (exists) {
      throw new Error('username_exists');
    }
    const user = {
      username: opts.username,
      password: opts.password,
      role: opts.role,
      name: opts.name || opts.username
    };
    if (opts.nim) user.nim = opts.nim;
    users.push(user);
    _write(LS_USERS, users);
    return user;
  }

  // authenticateUser: Promise to simulate async
  function authenticateUser(username, password) {
    ensureSampleData();
    return new Promise((resolve) => {
      setTimeout(() => {
        const users = getUsers();
        const found = users.find(u => u.username === username && u.password === password);
        if (!found) {
          resolve(null);
          return;
        }
        const session = {
          username: found.username,
          role: found.role || 'mahasiswa',
          name: found.name || found.username,
          nim: found.nim || null,
          ts: new Date().toISOString()
        };
        _write(LS_SESSION, session);
        resolve(session);
      }, 200);
    });
  }

  function getCurrentUser() {
    ensureSampleData();
    return _read(LS_SESSION) || null;
  }
  function logout() {
    localStorage.removeItem(LS_SESSION);
    try { window.dispatchEvent(new CustomEvent('dataapi-update', { detail: { key: LS_SESSION, value: null } })); } catch(e){}
  }

  // Dev helper: clear everything
  function _clearAll() {
    localStorage.removeItem(LS_STUDENTS);
    localStorage.removeItem(LS_NOTIFS);
    localStorage.removeItem(LS_USERS);
    localStorage.removeItem(LS_SESSION);
    ensureSampleData();
  }

  global.DataAPI = {
    // students & notifications
    getStudents,
    saveStudents,
    getNotifications,
    addNotification,
    markNotificationRead,
    // users & auth
    getUsers,
    createUser,
    authenticateUser,
    getCurrentUser,
    logout,
    // dev
    _clearAll
  };

  ensureSampleData();
  console.info('DataAPI initialized (demo).');
})(window);