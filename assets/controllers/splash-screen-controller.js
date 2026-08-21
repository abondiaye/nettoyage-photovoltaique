// Splash Screen with Video Animation
(function() {
  'use strict';

  class SplashScreen {
    constructor() {
      this.splashScreen = document.querySelector('.splash-screen');
      if (!this.splashScreen) return;

      const video = document.getElementById('splash-video');
      if (!video) return;

      // Hide splash screen when video ends
      video.addEventListener('ended', () => this.hideSplash());

      // Fallback: hide after 10 seconds if video doesn't end
      setTimeout(() => this.hideSplash(), 10000);
    }

    hideSplash() {
      if (this.splashScreen && !this.splashScreen.classList.contains('hidden')) {
        this.splashScreen.classList.add('hidden');
        setTimeout(() => {
          if (this.splashScreen && this.splashScreen.parentElement) {
            this.splashScreen.remove();
          }
        }, 800);
      }
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      new SplashScreen();
    });
  } else {
    new SplashScreen();
  }
})();
