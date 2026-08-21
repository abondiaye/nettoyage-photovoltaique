import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['menu', 'toggle'];

  connect() {
    // Fermer le menu au redimensionnement de la fenêtre
    this.resizeListener = () => {
      if (window.innerWidth > 768) {
        this.close();
      }
    };
    window.addEventListener('resize', this.resizeListener);
  }

  disconnect() {
    window.removeEventListener('resize', this.resizeListener);
  }

  toggle() {
    if (this.menuTarget.classList.contains('active')) {
      this.close();
    } else {
      this.open();
    }
  }

  open() {
    this.menuTarget.classList.add('active');
    this.toggleTarget.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  close() {
    this.menuTarget.classList.remove('active');
    this.toggleTarget.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  // Fermer le menu quand on clique sur un lien
  closeOnLinkClick(event) {
    if (event.target.tagName === 'A') {
      this.close();
    }
  }
}
