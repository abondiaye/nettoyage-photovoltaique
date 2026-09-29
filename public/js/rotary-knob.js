// "Rendement (%)" knob in the footer.
// Turn it with the mouse, a finger (Pointer Events, works on phones), the mouse wheel or the arrow keys.
// Its colour follows the site style: green for the neon style, blue for the "Pro" style.
document.addEventListener('DOMContentLoaded', function () {
  const knob = document.querySelector('.rotary-knob-input');
  const indicator = document.querySelector('.rotary-indicator');
  const dial = document.querySelector('.rotary-metal-container');
  if (!knob || !indicator || !dial) return;

  const max = parseInt(knob.getAttribute('max'), 10) || 360;
  const min = parseInt(knob.getAttribute('min'), 10) || 0;
  let value = parseInt(knob.value, 10) || 0;

  // The whole dial is the touch area; the page must not scroll while it is being turned.
  dial.style.touchAction = 'none';
  dial.style.zIndex = '5';            // above the frame picture, so it receives the touches
  knob.style.pointerEvents = 'none';
  dial.setAttribute('role', 'slider');
  dial.setAttribute('tabindex', '0');
  dial.setAttribute('aria-label', 'Rendement en pourcentage');
  dial.setAttribute('aria-valuemin', '0');
  dial.setAttribute('aria-valuemax', '100');

  const clamp = (v) => Math.max(min, Math.min(max, v));
  const isPro = () => document.documentElement.classList.contains('theme-pro');

  // Drag: the knob follows the angle of the finger / mouse around its centre.
  let dragging = false;
  let lastAngle = 0;
  function angleOf(e) {
    const r = dial.getBoundingClientRect();
    const cx = r.left + r.width / 2;
    const cy = r.top + r.height / 2;
    return Math.atan2(e.clientY - cy, e.clientX - cx) * 180 / Math.PI;
  }
  dial.addEventListener('pointerdown', function (e) {
    dragging = true;
    lastAngle = angleOf(e);
    dial.setPointerCapture(e.pointerId);
    e.preventDefault();
  });
  dial.addEventListener('pointermove', function (e) {
    if (!dragging) return;
    const a = angleOf(e);
    let d = a - lastAngle;
    if (d > 180) d -= 360;
    if (d < -180) d += 360;
    lastAngle = a;
    set(value + d);
  });
  const stop = function () { dragging = false; };
  dial.addEventListener('pointerup', stop);
  dial.addEventListener('pointercancel', stop);

  dial.addEventListener('wheel', function (e) {
    e.preventDefault();
    set(value + (e.deltaY > 0 ? -1 : 1) * 3.6);
  }, { passive: false });

  dial.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight' || e.key === 'ArrowUp') { set(value + 3.6); e.preventDefault(); }
    if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') { set(value - 3.6); e.preventDefault(); }
  });

  function set(v) {
    value = clamp(v);
    paint();
  }

  function paint() {
    knob.value = Math.round(value);
    dial.style.setProperty('--rotation', value + 'deg');
    const percent = Math.round(((value - min) / (max - min)) * 100);
    const t = percent / 100;
    indicator.textContent = percent;
    dial.setAttribute('aria-valuenow', String(percent));

    const pro = isPro();
    const hue = pro ? 221 : 150;                 // blue (Pro) or green (neon)
    const sat = pro ? 45 + t * 40 : 40 + t * 50;
    const light = pro ? 30 + t * 25 : 30 + t * 30;
    indicator.style.background = `linear-gradient(135deg, hsl(${hue}, ${sat}%, ${light}%) 0%, hsl(${hue}, ${sat}%, ${light + 10}%) 100%)`;
    indicator.style.borderColor = `hsl(${hue}, ${sat}%, ${light + 15}%)`;
    indicator.style.boxShadow = `0 0 ${6 + t * 16}px hsla(${hue}, 90%, 60%, ${0.25 + t * 0.5})`;

    // The footer warms up with the value, in the colour of the current style.
    const footer = document.querySelector('footer');
    if (footer) {
      const fs = pro ? 45 + t * 25 : 10 + t * 30;
      const fl = pro ? 11 + t * 12 : 12 + t * 15;
      footer.style.background = `linear-gradient(135deg, hsl(${hue}, ${fs}%, ${fl}%) 0%, hsl(${hue}, ${fs}%, ${Math.max(6, fl - 5)}%) 100%)`;
    }
  }

  // Repaint when the visitor switches between the green and the blue style.
  new MutationObserver(paint).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
  paint();
});
