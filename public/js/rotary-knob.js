document.addEventListener('DOMContentLoaded', function() {
  const knob = document.querySelector('.rotary-knob-input');
  const indicator = document.querySelector('.rotary-indicator');
  const metalContainer = document.querySelector('.rotary-metal-container');

  if (!knob || !indicator || !metalContainer) return;

  let isDragging = false;
  let knobVal = 0;
  const max = parseInt(knob.getAttribute('max')) || 360;
  const min = parseInt(knob.getAttribute('min')) || 0;

  // Mouse wheel support
  knob.addEventListener('wheel', function(event) {
    event.preventDefault();
    const delta = event.deltaY > 0 ? -1 : 1;
    let newVal = knobVal + delta * 3.6;

    if (newVal <= max && newVal >= min) {
      knobVal = newVal;
      updateKnob();
    }
  });

  // Mouse drag support
  knob.addEventListener('mousedown', function(event) {
    isDragging = true;
    let startX = event.pageX;
    let lastX = startX;

    function onMouseMove(moveEvent) {
      const currentX = moveEvent.pageX;
      const diff = currentX - lastX;

      let newVal = knobVal + Math.floor(diff / 3);
      if (newVal > max) newVal = max;
      if (newVal < min) newVal = min;

      knobVal = newVal;
      lastX = currentX;
      updateKnob();
    }

    function onMouseUp() {
      isDragging = false;
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', onMouseUp);
    }

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
  });

  // Touch support
  knob.addEventListener('touchstart', function(event) {
    isDragging = true;
    let startX = event.touches[0].pageX;
    let lastX = startX;

    function onTouchMove(moveEvent) {
      const currentX = moveEvent.touches[0].pageX;
      const diff = currentX - lastX;

      let newVal = knobVal + Math.floor(diff / 3);
      if (newVal > max) newVal = max;
      if (newVal < min) newVal = min;

      knobVal = newVal;
      lastX = currentX;
      updateKnob();
    }

    function onTouchEnd() {
      isDragging = false;
      document.removeEventListener('touchmove', onTouchMove);
      document.removeEventListener('touchend', onTouchEnd);
    }

    document.addEventListener('touchmove', onTouchMove);
    document.addEventListener('touchend', onTouchEnd);
  });

  function updateKnob() {
    knob.value = knobVal;
    metalContainer.style.setProperty('--rotation', `${knobVal}deg`);

    // Update color based on value (green theme)
    const percent = Math.round((knobVal / max) * 100);
    const intensity = (knobVal / max) * 100; // 0-100

    // Gradient from dark green to light green
    const hue = 160; // Green hue
    const saturation = 40 + (intensity * 0.5); // 40% to 90%
    const lightness = 30 + (intensity * 0.3); // 30% to 60%

    const color = `hsla(${hue}, ${saturation}%, ${lightness}%, 0.9)`;

    // Update indicator background
    indicator.style.background = `linear-gradient(135deg, ${color} 0%, hsla(${hue}, ${saturation}%, ${lightness + 10}%, 0.9) 100%)`;
    indicator.textContent = percent;

    // Update footer background color
    const footer = document.querySelector('footer');
    if (footer) {
      const footerHue = 160; // Green hue
      const footerSaturation = 10 + (intensity * 0.3); // 10% to 40%
      const footerLightness = 12 + (intensity * 0.15); // 12% to 27% (dark theme)
      footer.style.background = `linear-gradient(135deg, hsla(${footerHue}, ${footerSaturation}%, ${footerLightness}%, 1) 0%, hsl(${footerHue}, ${footerSaturation}%, ${footerLightness - 5}%) 100%)`;
    }
  }

  // Initialize
  updateKnob();
});
