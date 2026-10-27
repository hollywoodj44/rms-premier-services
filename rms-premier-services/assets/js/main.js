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

  // ── 5. Web3Forms Submission ─────────────────────────
  const form = document.getElementById('form') || document.getElementById('contactForm');

  if (form) {
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(form);
      formData.set("access_key", "e25a9b5c-b5e5-435f-83a1-a9292dd1819b");

      const firstName = formData.get("first_name") || "";
      const lastName  = formData.get("last_name") || "";
      if (!formData.get("name") && (firstName || lastName)) {
        formData.set("name", `${firstName} ${lastName}`.trim());
      }

      // Convert to clean JSON object to guarantee single-string fields
      const object = Object.fromEntries(formData);
      const json = JSON.stringify(object);

      const originalText = submitBtn.textContent;

      submitBtn.textContent = "Sending...";
      submitBtn.disabled = true;

      const formMsg = document.getElementById('formMsg');
      if (formMsg) formMsg.style.display = 'none';

      try {
        const response = await fetch("https://api.web3forms.com/submit", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
          },
          body: json
        });

        const data = await response.json();

        if (response.ok && data.success) {
          alert("Success! Your message has been sent.");
          if (formMsg) {
            formMsg.className = 'form-msg-success';
            formMsg.textContent = '✓ Success! Your message has been sent.';
            formMsg.style.display = 'block';
          }
          form.reset();
        } else {
          alert("Error: " + (data.message || "Failed to send message."));
          if (formMsg) {
            formMsg.className = 'form-msg-error';
            formMsg.textContent = 'Error: ' + (data.message || "Failed to send message.");
            formMsg.style.display = 'block';
          }
        }

      } catch (error) {
        alert("Something went wrong. Please try again.");
        if (formMsg) {
          formMsg.className = 'form-msg-error';
          formMsg.textContent = 'Something went wrong. Please try again.';
          formMsg.style.display = 'block';
        }
      } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });
  }

});
