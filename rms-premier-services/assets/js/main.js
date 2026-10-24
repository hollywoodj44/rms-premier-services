/**
 * RMS Premier Services — Theme Interactive Logic & AJAX Form Handling
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── 1. Navbar Scroll Effect ───────────────────────────
  const navbar = document.getElementById('navbar');
  if (navbar) {
    const handleScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 40);
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // ── 2. Mobile Menu ────────────────────────────────────
  const hamburger   = document.getElementById('hamburger');
  const mobileMenu  = document.getElementById('mobileMenu');
  const mobileClose = document.getElementById('mobileClose');

  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', () => {
      mobileMenu.classList.add('open');
      document.body.style.overflow = 'hidden';
    });

    const closeMobile = () => {
      mobileMenu.classList.remove('open');
      document.body.style.overflow = '';
    };

    if (mobileClose) {
      mobileClose.addEventListener('click', closeMobile);
    }

    mobileMenu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeMobile);
    });
  }

  // ── 3. Scroll Reveal Animation ────────────────────────
  const revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length > 0 && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -40px 0px'
    });

    revealEls.forEach(el => observer.observe(el));
  } else {
    // Fallback if IntersectionObserver not supported
    revealEls.forEach(el => el.classList.add('visible'));
  }

  // ── 4. Services Filter Tabs ───────────────────────────
  const tabButtons = document.querySelectorAll('.tab-btn');
  const serviceCards = document.querySelectorAll('.service-card');

  if (tabButtons.length > 0 && serviceCards.length > 0) {
    window.filterServices = function(cat) {
      tabButtons.forEach(b => b.classList.remove('active'));
      const activeBtn = document.getElementById('tab-' + cat);
      if (activeBtn) activeBtn.classList.add('active');

      serviceCards.forEach(card => {
        const cats = card.getAttribute('data-cat') || '';
        card.style.display = (cat === 'all' || cats.includes(cat)) ? '' : 'none';
      });
    };
  }

  // ── 5. AJAX Consultation Form Submission ──────────────
  const contactForm = document.getElementById('contactForm');
  const submitBtn   = document.getElementById('submitBtn');
  const formMsg     = document.getElementById('formMsg');

  if (contactForm && submitBtn && formMsg) {
    contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      // Basic client-side validation
      const firstName = document.getElementById('firstName')?.value.trim();
      const lastName  = document.getElementById('lastName')?.value.trim();
      const email     = document.getElementById('email')?.value.trim();
      const message   = document.getElementById('message')?.value.trim();

      if (!firstName || !lastName || !email || !message) {
        formMsg.className = 'form-msg-error';
        formMsg.textContent = 'Please fill out all required fields (Name, Email, and Project Description).';
        formMsg.style.display = 'block';
        return;
      }

      // Enter loading state
      submitBtn.disabled = true;
      const originalBtnText = submitBtn.innerHTML;
      submitBtn.innerHTML = 'Sending Consultation Request…';
      formMsg.style.display = 'none';

      // Prepare form data for WordPress admin-ajax.php
      const formData = new FormData(contactForm);
      formData.append('action', 'rms_submit_consultation');

      // Use localized nonce from WordPress if available
      if (typeof rmsData !== 'undefined' && rmsData.nonce) {
        formData.append('nonce', rmsData.nonce);
      }

      const ajaxEndpoint = (typeof rmsData !== 'undefined' && rmsData.ajaxUrl)
        ? rmsData.ajaxUrl
        : '/wp-admin/admin-ajax.php';

      try {
        const response = await fetch(ajaxEndpoint, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const result = await response.json();

        if (result && result.success) {
          formMsg.className = 'form-msg-success';
          formMsg.textContent = result.data?.message || '✓ Thank you! Your consultation request has been sent. We\'ll be in touch within 24 business hours.';
          formMsg.style.display = 'block';
          contactForm.reset();
        } else {
          const errMsg = result.data?.message || 'Something went wrong. Please email us directly at sthomas@rmspremierservices.com.';
          formMsg.className = 'form-msg-error';
          formMsg.textContent = errMsg;
          formMsg.style.display = 'block';
        }
      } catch (err) {
        formMsg.className = 'form-msg-error';
        formMsg.textContent = 'Network or server error. Please email us directly at sthomas@rmspremierservices.com.';
        formMsg.style.display = 'block';
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
    });
  }

});
