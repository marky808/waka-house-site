/* ============================================
   株式会社WAKA - いえサポ
   Main JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

  // --- Header scroll effect ---
  const header = document.querySelector('.header');
  if (header) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 60) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  }

  // --- Hamburger menu ---
  const hamburger = document.querySelector('.hamburger');
  const navMobile = document.querySelector('.nav-mobile');
  const navOverlay = document.querySelector('.nav-overlay');

  function closeMenu() {
    hamburger.classList.remove('active');
    navMobile.classList.remove('open');
    navOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (hamburger && navMobile) {
    hamburger.addEventListener('click', function () {
      const isOpen = navMobile.classList.contains('open');
      if (isOpen) {
        closeMenu();
      } else {
        hamburger.classList.add('active');
        navMobile.classList.add('open');
        navOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });

    if (navOverlay) {
      navOverlay.addEventListener('click', closeMenu);
    }

    navMobile.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', closeMenu);
    });
  }

  // --- Fade-in on scroll (Intersection Observer) ---
  var fadeElements = document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right, .fade-in-stagger');
  if (fadeElements.length > 0 && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    fadeElements.forEach(function (el) {
      observer.observe(el);
    });
  } else {
    fadeElements.forEach(function (el) {
      el.classList.add('visible');
    });
  }

  // --- Smooth scroll for anchor links ---
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var targetId = this.getAttribute('href');
      if (targetId === '#') return;
      var target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        var headerHeight = header ? header.offsetHeight : 0;
        var top = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
        window.scrollTo({ top: top, behavior: 'smooth' });
      }
    });
  });

  // --- Form validation (contact page) ---
  var contactForm = document.querySelector('.contact-form form');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      var name = contactForm.querySelector('[name="name"]');
      var email = contactForm.querySelector('[name="email"]');
      var message = contactForm.querySelector('[name="message"]');
      var errors = [];

      if (name && !name.value.trim()) errors.push('お名前を入力してください。');
      if (email && !email.value.trim()) {
        errors.push('メールアドレスを入力してください。');
      } else if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        errors.push('正しいメールアドレスを入力してください。');
      }
      if (message && !message.value.trim()) errors.push('お問合せ内容を入力してください。');

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
      }
    });
  }

});
