// auth helper - protect pages and redirect based on role.
// Usage:
//  - include data-api.js then include auts.js
//  - call Auth.requireRole(['tu']) on TU page, requireRole(['mahasiswa']) on student page, etc.

(function (global) {
  function redirectToLogin() {
    // adjust path to your login page
    window.location.href = 'Login.html';
  }

  // Call on protected pages.
  // roles: array of allowed roles e.g. ['tu'], ['dosen'], ['mahasiswa'], or ['tu','dosen']
  function requireRole(roles) {
    const user = (typeof DataAPI !== 'undefined' && DataAPI.getCurrentUser) ? DataAPI.getCurrentUser() : null;
    if (!user) {
      redirectToLogin();
      return false;
    }
    if (Array.isArray(roles) && roles.length && roles.indexOf(user.role) === -1) {
      // redirect ke home sesuai role
      const roleHome = user.role === 'tu' ? 'tu_ui.html' : 'ms_ui.html';
      // avoid redirect loop by only changing location if different
      const currentPath = window.location.pathname.split('/').pop();
      if (currentPath !== roleHome) {
        window.location.href = roleHome;
      }
      return false;
    }
    return true;
  }

  // Improved: only redirect if not already on the target page
  function goToRoleHome(user) {
    if (!user) return redirectToLogin();
    let target;
    if (user.role === 'tu') target = 'tu_ui.html';
    else if (user.role === 'mahasiswa') target = 'ms_ui.html';
    else return redirectToLogin();

    // get current filename portion and compare (case-insensitive)
    const currentPath = (window.location.pathname.split('/').pop() || '').toLowerCase();
    if (currentPath === String(target).toLowerCase()) {
      // already on target page — do nothing to avoid reload loop
      return;
    }

    window.location.href = target;
  }

  global.Auth = {
    requireRole,
    goToRoleHome,
    redirectToLogin
  };
})(window);