// Dark Mode Toggle Controller
(function() {
  'use strict';

  const THEME_KEY = 'syriuspro-theme';
  const DARK_MODE = 'dark';
  const LIGHT_MODE = 'light';

  class DarkModeController {
    constructor() {
      this.root = document.documentElement;
      this.toggle = document.querySelector('.theme-toggle');
      this.init();
    }

    init() {
      // Check for saved theme preference or use system preference
      const savedTheme = localStorage.getItem(THEME_KEY);
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

      const theme = savedTheme || (prefersDark ? DARK_MODE : LIGHT_MODE);

      // Set initial theme
      this.setTheme(theme, false);

      // Add event listeners
      if (this.toggle) {
        this.toggle.addEventListener('click', (e) => this.toggleTheme(e));
        this.toggle.addEventListener('keypress', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.toggleTheme(e);
          }
        });
      }

      // Listen to system theme changes
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem(THEME_KEY)) {
          this.setTheme(e.matches ? DARK_MODE : LIGHT_MODE);
        }
      });
    }

    toggleTheme(e) {
      if (e) {
        e.preventDefault();
        e.stopPropagation();
      }

      const currentTheme = this.root.getAttribute('data-theme') ||
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? DARK_MODE : LIGHT_MODE);
      const newTheme = currentTheme === DARK_MODE ? LIGHT_MODE : DARK_MODE;

      this.setTheme(newTheme, true);
    }

    setTheme(theme, animate = false) {
      // Add animation class
      if (animate) {
        this.root.classList.add('theme-changing');
      }

      // Set the theme attribute
      this.root.setAttribute('data-theme', theme);

      // Save to localStorage
      localStorage.setItem(THEME_KEY, theme);

      // Update toggle button icon and label
      if (this.toggle) {
        this.updateToggleButton(theme);
      }

      // Dispatch custom event for other components
      window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));

      // Remove animation class after transition
      if (animate) {
        setTimeout(() => {
          this.root.classList.remove('theme-changing');
        }, 300);
      }
    }

    updateToggleButton(theme) {
      const icon = this.toggle.querySelector('svg') || this.toggle;

      // Update icon (sun/moon emoji)
      this.toggle.textContent = theme === DARK_MODE ? '☀️' : '🌙';
      this.toggle.setAttribute('aria-label',
        theme === DARK_MODE ? 'Switch to light mode' : 'Switch to dark mode');

      // Add animation
      this.toggle.style.animation = 'none';
      setTimeout(() => {
        this.toggle.style.animation = '';
      }, 10);
    }

    // Get current theme
    getCurrentTheme() {
      return this.root.getAttribute('data-theme') || LIGHT_MODE;
    }

    // Force a specific theme
    forceTheme(theme) {
      this.setTheme(theme, true);
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      new DarkModeController();
    });
  } else {
    new DarkModeController();
  }

  // Export for use in other scripts if needed
  window.DarkModeController = DarkModeController;
})();

// Back to top button functionality
(function() {
  'use strict';

  const backToTopBtn = document.querySelector('.back-to-top');

  if (!backToTopBtn) return;

  // Show/hide button based on scroll position
  window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
      backToTopBtn.classList.add('visible');
    } else {
      backToTopBtn.classList.remove('visible');
    }
  });

  // Scroll to top on click
  backToTopBtn.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  // Keyboard support
  backToTopBtn.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    }
  });
})();

// Navbar scroll effect
(function() {
  'use strict';

  const header = document.querySelector('header.site');

  if (!header) return;

  window.addEventListener('scroll', () => {
    if (window.pageYOffset > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }, false);
})();

// Newsletter subscription
(function() {
  'use strict';

  const newsletter = document.querySelector('.newsletter-form');

  if (!newsletter) return;

  newsletter.addEventListener('submit', (e) => {
    e.preventDefault();

    const input = newsletter.querySelector('input');
    const button = newsletter.querySelector('button');

    if (!input.value) {
      input.focus();
      return;
    }

    // Simulate subscription (replace with actual API call)
    const originalText = button.textContent;
    button.textContent = '✓ Subscribed!';
    button.disabled = true;

    setTimeout(() => {
      input.value = '';
      button.textContent = originalText;
      button.disabled = false;
    }, 2000);
  });
})();
