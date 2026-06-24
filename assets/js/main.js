  // ── CAROUSEL ──
  const slides = document.querySelectorAll('.slide');
  const dotsContainer = document.getElementById('dots');
  let current = 0;
  let timer;

  slides.forEach((_, i) => {
    const dot = document.createElement('div');
    dot.className = 'dot' + (i === 0 ? ' active' : '');
    dot.onclick = () => goTo(i);
    dotsContainer.appendChild(dot);
  });

  function goTo(n) {
    slides[current].classList.remove('active');
    dotsContainer.children[current].classList.remove('active');
    current = (n + slides.length) % slides.length;
    slides[current].classList.add('active');
    dotsContainer.children[current].classList.add('active');
    document.getElementById('carousel').style.transform = `translateX(-${current * 100}%)`;
    resetTimer();
  }

  function shiftSlide(dir) { goTo(current + dir); }

  function resetTimer() {
    clearInterval(timer);
    timer = setInterval(() => shiftSlide(1), 5000);
  }

  resetTimer();

  // ── SCROLL REVEAL ──
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  // ── COUNTERS ──
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      const target = parseInt(el.dataset.target);
      const duration = 2000;
      const step = target / (duration / 16);
      let current = 0;
      const interval = setInterval(() => {
        current = Math.min(current + step, target);
        if (target >= 1000000) {
          el.textContent = (current / 1000000).toFixed(1) + 'M+';
        } else if (target >= 1000) {
          el.textContent = Math.floor(current / 1000) + 'K+';
        } else if (el.dataset.target === '99') {
          el.textContent = Math.floor(current) + '%';
        } else {
          el.textContent = Math.floor(current) + '+';
        }
        if (current >= target) clearInterval(interval);
      }, 16);
      counterObserver.unobserve(el);
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('[data-target]').forEach(el => counterObserver.observe(el));

  // ── KEYBOARD CAROUSEL ──
  document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') shiftSlide(-1);
    if (e.key === 'ArrowRight') shiftSlide(1);
  });
