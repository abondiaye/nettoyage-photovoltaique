document.addEventListener('DOMContentLoaded', function() {
  const navbar = document.querySelector('.gooey-navbar');
  if (!navbar) return;

  let lastScrollTop = 0;
  let scrollTimeout;

  // Add transition class
  navbar.style.transition = 'opacity 0.3s ease-in-out, visibility 0.3s ease-in-out';
  navbar.style.opacity = '1';
  navbar.style.visibility = 'visible';

  window.addEventListener('scroll', function() {
    clearTimeout(scrollTimeout);

    const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

    // Si on scroll vers le bas et qu'on a scrollé plus de 100px
    if (currentScroll > lastScrollTop && currentScroll > 100) {
      // Scroll DOWN - hide navbar
      navbar.style.opacity = '0';
      navbar.style.visibility = 'hidden';
    } else {
      // Scroll UP - show navbar
      navbar.style.opacity = '1';
      navbar.style.visibility = 'visible';
    }

    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
  }, false);
});
