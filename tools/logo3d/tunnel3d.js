// Sirius-Solar neon tunnel: glowing rings (green and yellow) flying towards the viewer,
// with floating sparks. Background of the login and sign-up pages.
// Source of public/js/sirius-tunnel3d.js (bundled with esbuild, three.js included).
import {
  WebGLRenderer, Scene, PerspectiveCamera, Group, Mesh, Color, Fog,
  TorusGeometry, MeshBasicMaterial, BufferGeometry, Float32BufferAttribute, Points, PointsMaterial,
  CanvasTexture, AdditiveBlending, SRGBColorSpace,
} from 'three';

const GREEN = new Color('#2dff4f');
const YELLOW = new Color('#ffd21f');

function dotTexture() {
  const cv = document.createElement('canvas');
  cv.width = cv.height = 64;
  const ctx = cv.getContext('2d');
  const g = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
  g.addColorStop(0, 'rgba(255,255,255,1)');
  g.addColorStop(0.3, 'rgba(255,255,255,0.6)');
  g.addColorStop(1, 'rgba(255,255,255,0)');
  ctx.fillStyle = g;
  ctx.fillRect(0, 0, 64, 64);
  const t = new CanvasTexture(cv);
  t.colorSpace = SRGBColorSpace;
  return t;
}

export function mountTunnel(canvas) {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let renderer;
  try {
    renderer = new WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'low-power' });
  } catch (e) {
    return;
  }
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.75));
  renderer.outputColorSpace = SRGBColorSpace;
  renderer.setClearColor(0x000000, 0);

  const scene = new Scene();
  scene.fog = new Fog(0x020a07, 30, 150);
  const camera = new PerspectiveCamera(70, 1, 0.1, 400);
  camera.position.z = 25;

  // Rings: a bright core tube plus a wider, faint halo tube around it.
  const DEPTH = 160;
  const COUNT = 16;
  const core = new TorusGeometry(15, 0.28, 12, 160);
  const halo = new TorusGeometry(15, 1.1, 12, 160);
  const rings = [];
  for (let i = 0; i < COUNT; i++) {
    const color = i % 3 === 2 ? YELLOW : GREEN;
    const g = new Group();
    g.add(new Mesh(core, new MeshBasicMaterial({ color, transparent: true })));
    g.add(new Mesh(halo, new MeshBasicMaterial({ color, transparent: true, opacity: 0.12, blending: AdditiveBlending, depthWrite: false })));
    g.position.z = 20 - (i / COUNT) * DEPTH;
    g.userData = { phase: i * 0.7, scale: 0.9 + (i % 4) * 0.08 };
    g.scale.setScalar(g.userData.scale);
    scene.add(g);
    rings.push(g);
  }

  // Sparks floating inside the tunnel.
  const N = 260;
  const pos = new Float32Array(N * 3);
  const col = new Float32Array(N * 3);
  for (let i = 0; i < N; i++) {
    const a = Math.random() * Math.PI * 2;
    const r = 6 + Math.random() * 12;
    pos[i * 3] = Math.cos(a) * r;
    pos[i * 3 + 1] = Math.sin(a) * r;
    pos[i * 3 + 2] = 20 - Math.random() * DEPTH;
    const c = Math.random() < 0.3 ? YELLOW : GREEN;
    col.set([c.r, c.g, c.b], i * 3);
  }
  const pg = new BufferGeometry();
  pg.setAttribute('position', new Float32BufferAttribute(pos, 3));
  pg.setAttribute('color', new Float32BufferAttribute(col, 3));
  const sparks = new Points(pg, new PointsMaterial({
    size: 0.9, map: dotTexture(), vertexColors: true, transparent: true,
    blending: AdditiveBlending, depthWrite: false,
  }));
  scene.add(sparks);

  const pointer = { x: 0, y: 0 };
  window.addEventListener('pointermove', (e) => {
    pointer.x = (e.clientX / window.innerWidth) * 2 - 1;
    pointer.y = (e.clientY / window.innerHeight) * 2 - 1;
  }, { passive: true });

  const resize = () => {
    const w = canvas.clientWidth; const h = canvas.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
  };
  new ResizeObserver(resize).observe(canvas);
  resize();

  let last = performance.now();
  let visible = !document.hidden;
  document.addEventListener('visibilitychange', () => { visible = !document.hidden; if (visible) { last = performance.now(); requestAnimationFrame(loop); } });

  function draw(now, dt) {
    const t = now / 1000;
    const speed = reduced ? 0 : 9; // units per second towards the camera
    rings.forEach((g) => {
      g.position.z += speed * dt;
      if (g.position.z > 24) g.position.z -= DEPTH;
      const p = g.userData.phase;
      g.rotation.x = Math.sin(t * 0.5 + p) * 0.18;
      g.rotation.y = Math.cos(t * 0.4 + p) * 0.18;
      // Fade in from the depth, fade out just before reaching the camera.
      const z = g.position.z;
      const fade = Math.min(1, (z + DEPTH - 20) / 30) * Math.min(1, (24 - z) / 10);
      g.children[0].material.opacity = Math.max(0, fade);
      g.children[1].material.opacity = Math.max(0, fade) * 0.14;
    });
    const arr = pg.attributes.position.array;
    for (let i = 0; i < N; i++) {
      arr[i * 3 + 2] += speed * 1.4 * dt;
      if (arr[i * 3 + 2] > 24) arr[i * 3 + 2] -= DEPTH;
    }
    pg.attributes.position.needsUpdate = true;
    camera.position.x += (pointer.x * 2.5 - camera.position.x) * 0.05;
    camera.position.y += (-pointer.y * 2 - camera.position.y) * 0.05;
    camera.lookAt(0, 0, -40);
    renderer.render(scene, camera);
  }

  function loop(now) {
    if (!visible) return;
    const dt = Math.min(0.05, (now - last) / 1000);
    last = now;
    draw(now, dt);
    if (!reduced) requestAnimationFrame(loop);
  }
  requestAnimationFrame(loop);
}

document.querySelectorAll('canvas[data-sirius-tunnel]').forEach((c) => mountTunnel(c));
