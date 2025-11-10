// Simple counter animation for the numbers (updated formatting + safer step)
document.addEventListener('DOMContentLoaded', function () {
  const counters = document.querySelectorAll('.stat-number');

  const animateCount = (el, target) => {
    const duration = 1200; // ms
    const startTime = performance.now();
    const run = (now) => {
      const elapsed = now - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const value = Math.floor(progress * target);
      el.textContent = value.toLocaleString('id-ID');
      if (progress < 1) {
        requestAnimationFrame(run);
      } else {
        el.textContent = target.toLocaleString('id-ID');
      }
    };
    requestAnimationFrame(run);
  };

  // IntersectionObserver to run when visible
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const target = parseInt(el.getAttribute('data-target') || "0", 10);
          if (!isNaN(target) && target > 0) animateCount(el, target);
          io.unobserve(el);
        }
      });
    }, { threshold: 0.4 });

    counters.forEach(c => io.observe(c));
  } else {
    // fallback: animate immediately
    counters.forEach(c => {
      const target = parseInt(c.getAttribute('data-target') || "0", 10);
      if (!isNaN(target)) animateCount(c, target);
    });
  }
});