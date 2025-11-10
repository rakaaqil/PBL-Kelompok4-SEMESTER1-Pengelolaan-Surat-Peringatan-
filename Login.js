// Login.js - merged role-toggle + authentication (uses DataAPI + Auth)
// Fixed: if user cancels after successful auth (role mismatch), session is cleared (DataAPI.logout()).
// Added defensive checks (DataAPI/Auth existence) and safer redirect fallback.
document.addEventListener('DOMContentLoaded', () => {
  const roleBtns = document.querySelectorAll('.role-btn');
  const roleInput = document.getElementById('roleInput');
  const form = document.getElementById('loginForm');
  const loginButton = form ? form.querySelector('.btn-login') : null;

  // initialize role toggle (if present)
  if (roleBtns && roleBtns.length) {
    roleBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        roleBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const role = btn.getAttribute('data-role') || 'mahasiswa';
        if (roleInput) roleInput.value = role;
        // update aria-pressed
        roleBtns.forEach(b => b.setAttribute('aria-pressed', 'false'));
        btn.setAttribute('aria-pressed', 'true');
      });
    });
    // ensure there's an active one (if none active, activate MAHASISWA)
    const anyActive = Array.from(roleBtns).some(b => b.classList.contains('active'));
    if (!anyActive) {
      const m = document.getElementById('role-mhs');
      if (m) m.classList.add('active');
      if (roleInput) roleInput.value = 'mahasiswa';
    }
  }

  if (!form) return; // nothing to do

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (typeof DataAPI === 'undefined') {
      console.error('DataAPI is not available. Make sure data-api.js is included before Login.js');
      alert('Terjadi kesalahan internal (DataAPI tidak tersedia).');
      return;
    }

    const originalText = loginButton ? loginButton.textContent : 'LOGIN';
    try {
      const rawUsername = (form.username.value || '');
      const username = rawUsername.trim();
      const password = (form.password.value || '').toString();
      const selectedRole = (roleInput && roleInput.value) ? roleInput.value : null;

      if (!username || !password) {
        alert('Mohon isi username dan password.');
        return;
      }

      if (loginButton) {
        loginButton.disabled = true;
        loginButton.textContent = 'Memproses...';
      }

      // Authenticate via DataAPI (async)
      // DataAPI.authenticateUser should write session on success.
      const session = await DataAPI.authenticateUser(username, password);
      console.debug('Auth attempt', { usernameAttempt: username, sessionResult: session ? { username: session.username, role: session.role } : null });

      if (!session) {
        alert('Login gagal: username atau password salah.');
        return;
      }

      // If user selected a role in the toggle but it differs from account role, warn user
      if (selectedRole && selectedRole !== session.role) {
        const proceed = confirm(`Akun "${username}" terdaftar sebagai "${session.role}", bukan "${selectedRole}". Lanjut login sebagai "${session.role}"?`);
        if (!proceed) {
          // user cancelled -> clear session so user is not considered logged in
          try {
            if (typeof DataAPI.logout === 'function') DataAPI.logout();
          } catch (errLogout) {
            console.warn('Error during logout after user cancelled:', errLogout);
            localStorage.removeItem && localStorage.removeItem('session');
          }
          return;
        }
      }

      // Redirect to role home (uses Auth.goToRoleHome which redirects)
      try {
        if (typeof Auth !== 'undefined' && typeof Auth.goToRoleHome === 'function') {
          Auth.goToRoleHome(session);
        } else {
          // fallback redirect if Auth helper not present
          const role = (session.role || '').toLowerCase();
          if (role === 'tu' || role === 'dosen') window.location.href = 'tu_ui.html';
          else if (role === 'mahasiswa') window.location.href = 'ms_ui.html';
          else window.location.href = 'Dasboard.html';
        }
      } catch (errRedirect) {
        console.error('Redirect error', errRedirect);
        alert('Login sukses tetapi terjadi kesalahan saat redirect. Cek console untuk detail.');
      }

    } catch (err) {
      console.error('Login error', err);
      alert('Terjadi kesalahan saat login. Cek console untuk detail.');
    } finally {
      if (loginButton) {
        loginButton.disabled = false;
        loginButton.textContent = originalText;
      }
    }
  });
});