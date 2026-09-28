// Sirius-Solar 3D logo: the two-roller cleaning brush, "SIRIUS" in mint and "-SOLAR" in gold neon.
// Transparent background, the brushes spin, the logo sways and makes a full turn now and then.
// Source of public/js/sirius-logo3d.js (bundled with esbuild, three.js included).
import {
  WebGLRenderer, Scene, PerspectiveCamera, Group, Mesh, Color, Vector2,
  MeshPhysicalMaterial, MeshStandardMaterial, MeshBasicMaterial,
  CylinderGeometry, BoxGeometry, TorusGeometry, CircleGeometry, TubeGeometry, ConeGeometry,
  ExtrudeGeometry, PlaneGeometry, Shape, CatmullRomCurve3, Vector3, CanvasTexture,
  PMREMGenerator, AdditiveBlending, SRGBColorSpace, NoToneMapping, Sprite, SpriteMaterial, DirectionalLight, AmbientLight,
} from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { FontLoader } from 'three/examples/jsm/loaders/FontLoader.js';
import { TextGeometry } from 'three/examples/jsm/geometries/TextGeometry.js';
import fontData from './exo2.json';

const MINT = new Color('#00a86b');
const MINT_DARK = new Color('#0a3a16');
const GOLD = new Color('#f2b233');

const font = new FontLoader().parse(fontData);

function text(str, size, depth, material) {
  const geo = new TextGeometry(str, {
    font, size, depth, curveSegments: 6,
    bevelEnabled: true, bevelThickness: depth * 0.25, bevelSize: size * 0.022, bevelSegments: 3,
  });
  geo.computeBoundingBox();
  const mesh = new Mesh(geo, material);
  return { mesh, box: geo.boundingBox };
}

// A soft neon halo: the word's outlines drawn on a canvas with a blur, laid behind it additively.
function neonHalo(str, size, color) {
  const shapes = font.generateShapes(str, size);
  let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
  const polys = shapes.map((s) => {
    const pts = s.getPoints(8);
    pts.forEach((p) => { minX = Math.min(minX, p.x); maxX = Math.max(maxX, p.x); minY = Math.min(minY, p.y); maxY = Math.max(maxY, p.y); });
    return [pts, s.holes.map((h) => h.getPoints(8))];
  });
  const pad = size * 0.9;
  const w = maxX - minX + pad * 2;
  const h = maxY - minY + pad * 2;
  const k = 512 / w;
  const cv = document.createElement('canvas');
  cv.width = 512; cv.height = Math.ceil(h * k);
  const ctx = cv.getContext('2d');
  const toC = (p) => [(p.x - minX + pad) * k, cv.height - (p.y - minY + pad) * k];
  const path = new Path2D();
  const trace = (pts) => pts.forEach((p, i) => (i ? path.lineTo(...toC(p)) : path.moveTo(...toC(p))));
  polys.forEach(([outer, holes]) => { trace(outer); holes.forEach(trace); });
  ctx.fillStyle = `#${color.getHexString()}`;
  ctx.shadowColor = ctx.fillStyle;
  for (const blur of [34, 18, 8]) { ctx.shadowBlur = blur; ctx.fill(path, 'evenodd'); }
  const tex = new CanvasTexture(cv);
  tex.colorSpace = SRGBColorSpace;
  const mat = new MeshBasicMaterial({ map: tex, transparent: true, blending: AdditiveBlending, depthWrite: false, opacity: 0.85 });
  const plane = new Mesh(new PlaneGeometry(w, h), mat);
  plane.position.set(minX - pad + w / 2, minY - pad + h / 2, -0.05);
  return plane;
}

// A round glow (with an optional four-point flare) that always faces the camera.
function glowSprite(color, size, flare = false) {
  const cv = document.createElement('canvas');
  cv.width = cv.height = 256;
  const ctx = cv.getContext('2d');
  const c = `#${color.getHexString()}`;
  const grad = ctx.createRadialGradient(128, 128, 0, 128, 128, 128);
  grad.addColorStop(0, 'rgba(255,255,255,1)');
  grad.addColorStop(0.12, c);
  grad.addColorStop(0.45, c + '55');
  grad.addColorStop(1, c + '00');
  ctx.fillStyle = grad;
  ctx.fillRect(0, 0, 256, 256);
  if (flare) {
    ctx.globalCompositeOperation = 'lighter';
    for (const [w, h] of [[256, 6], [6, 256]]) {
      const g = ctx.createRadialGradient(128, 128, 0, 128, 128, 128);
      g.addColorStop(0, 'rgba(255,255,255,0.95)');
      g.addColorStop(1, 'rgba(255,255,255,0)');
      ctx.fillStyle = g;
      ctx.fillRect(128 - w / 2, 128 - h / 2, w, h);
    }
  }
  const tex = new CanvasTexture(cv);
  tex.colorSpace = SRGBColorSpace;
  const sp = new Sprite(new SpriteMaterial({ map: tex, blending: AdditiveBlending, depthWrite: false, transparent: true }));
  sp.scale.set(size, size, 1);
  return sp;
}

function star4(outer, inner) {
  const s = new Shape();
  for (let i = 0; i < 8; i++) {
    const r = i % 2 ? inner : outer;
    const a = Math.PI / 2 + (i * Math.PI) / 4;
    const x = Math.cos(a) * r; const y = Math.sin(a) * r;
    i ? s.lineTo(x, y) : s.moveTo(x, y);
  }
  return s;
}

function buildRoller(mats, face) {
  const g = new Group();
  const spin = new Group();
  g.add(spin);
  const R = 0.82; const L = 0.62;
  const core = new Mesh(new CylinderGeometry(R * 0.93, R * 0.93, L, 48), mats.dark);
  core.rotation.x = Math.PI / 2;
  spin.add(core);
  // Bristle tufts around the drum.
  const tuft = new BoxGeometry(0.07, 0.14, L * 0.96);
  for (let i = 0; i < 44; i++) {
    const a = (i / 44) * Math.PI * 2;
    const m = new Mesh(tuft, i % 4 === 0 ? mats.white : mats.mint); // a few light tufts make the spin visible
    m.position.set(Math.cos(a) * R, Math.sin(a) * R, 0);
    m.rotation.z = a + Math.PI / 2;
    spin.add(m);
  }
  // Front face: rim ring, then the star (top brush) or the hub (bottom brush).
  const rim = new Mesh(new TorusGeometry(R * 0.72, 0.035, 10, 64), mats.mint);
  rim.position.z = L / 2 + 0.01;
  spin.add(rim);
  const disc = new Mesh(new CircleGeometry(R * 0.93, 48), mats.dark);
  disc.position.z = L / 2 + 0.002;
  spin.add(disc);
  if (face === 'star') {
    const st = new Mesh(new ExtrudeGeometry(star4(0.34, 0.07), { depth: 0.06, bevelEnabled: false }), mats.white);
    st.position.z = L / 2 + 0.01;
    spin.add(st); // the star turns with the brush
  } else {
    const hub = new Mesh(new TorusGeometry(0.17, 0.06, 12, 40), mats.white);
    hub.position.z = L / 2 + 0.04;
    spin.add(hub);
    const dot = new Mesh(new CylinderGeometry(0.06, 0.06, 0.08, 20), mats.dark);
    dot.rotation.x = Math.PI / 2; dot.position.z = L / 2 + 0.05;
    spin.add(dot);
  }
  // Notches on the rim so the rotation reads clearly.
  for (let i = 0; i < 6; i++) {
    const a = (i / 6) * Math.PI * 2;
    const n = new Mesh(new BoxGeometry(0.16, 0.06, 0.04), mats.white);
    n.position.set(Math.cos(a) * R * 0.72, Math.sin(a) * R * 0.72, L / 2 + 0.04);
    n.rotation.z = a;
    spin.add(n);
  }
  g.userData.spin = spin;
  return g;
}

export function mountSiriusLogo(el) {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let renderer;
  try {
    renderer = new WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'low-power' });
  } catch (e) {
    el.classList.add('is-static');
    return;
  }
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
  renderer.outputColorSpace = SRGBColorSpace;
  renderer.toneMapping = NoToneMapping; // keep the neon colours saturated
  renderer.setClearColor(0x000000, 0);
  el.appendChild(renderer.domElement);

  const scene = new Scene();
  const pmrem = new PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
  const key = new DirectionalLight(0xffffff, 1.4);
  key.position.set(3, 5, 6);
  scene.add(key, new AmbientLight(0xffffff, 0.25));

  const camera = new PerspectiveCamera(30, 2.4, 0.1, 100);
  camera.position.set(0, 0, 21);

  const mats = {
    // Neon: the colour comes from the emission, the tubes read as lit glass.
    mint: new MeshStandardMaterial({ color: new Color('#22c43f'), emissive: new Color('#2dff4f'), emissiveIntensity: 0.7, roughness: 0.3, metalness: 0, envMapIntensity: 0.35 }),
    dark: new MeshPhysicalMaterial({ color: MINT_DARK, metalness: 0.4, roughness: 0.45, clearcoat: 0.6, envMapIntensity: 0.5 }),
    white: new MeshStandardMaterial({ color: 0xb8ffc4, emissive: 0xd6ffdc, emissiveIntensity: 0.7, roughness: 0.3 }),
    gold: new MeshStandardMaterial({ color: new Color('#d9b400'), emissive: new Color('#ffd21f'), emissiveIntensity: 0.75, roughness: 0.3, metalness: 0, envMapIntensity: 0.35 }),
  };

  const logo = new Group();
  scene.add(logo);

  // Brushes on the left, one above the other, spinning in opposite directions.
  const top = buildRoller(mats, 'star');
  top.position.set(-3.55, 0.88, 0);
  const bottom = buildRoller(mats, 'hub');
  bottom.position.set(-3.55, -0.88, 0);
  logo.add(top, bottom);

  // Speed lines trailing behind the brushes.
  const cone = new ConeGeometry(0.045, 1, 8);
  [[0.88, 1], [-0.88, -1]].forEach(([y]) => {
    [-0.36, -0.12, 0.12, 0.36].forEach((dy, i) => {
      const m = new Mesh(cone, mats.mint);
      const len = 0.9 - Math.abs(dy) * 0.9;
      m.scale.set(1, len, 1);
      m.rotation.z = Math.PI / 2;
      m.position.set(-4.55 - len / 2 - 0.05 * i, y + dy, 0);
      logo.add(m);
    });
  });

  // The telescopic pole with its collars and the hook at the end.
  const pole = new Mesh(new CylinderGeometry(0.06, 0.06, 8.2, 20), mats.mint);
  pole.rotation.z = Math.PI / 2;
  pole.position.set(0.55, 0, 0);
  logo.add(pole);
  [-1.2, 1.2, 3.1].forEach((x) => {
    const c = new Mesh(new CylinderGeometry(0.1, 0.1, 0.14, 20), mats.dark);
    c.rotation.z = Math.PI / 2; c.position.set(x, 0, 0);
    logo.add(c);
  });
  const grip = new Mesh(new CylinderGeometry(0.1, 0.1, 0.5, 20), mats.white);
  grip.rotation.z = Math.PI / 2; grip.position.set(4.55, 0, 0);
  logo.add(grip);
  const hook = new Mesh(new TubeGeometry(new CatmullRomCurve3([
    new Vector3(4.75, 0, 0), new Vector3(5.1, 0.18, 0), new Vector3(5.35, 0.1, 0),
    new Vector3(5.42, -0.2, 0), new Vector3(5.35, -0.55, 0),
  ]), 40, 0.06, 12), mats.mint);
  logo.add(hook);

  // SIRIUS above the pole, -SOLAR below it, both left-aligned after the brushes.
  const left = -2.2;
  const sirius = text('SIRIUS', 1.5, 0.42, mats.mint);
  const siriusGroup = new Group();
  sirius.mesh.position.z = -0.21;
  siriusGroup.add(sirius.mesh, neonHalo('SIRIUS', 1.5, new Color('#2bff4d')));
  siriusGroup.position.set(left - sirius.box.min.x, 0.26 - sirius.box.min.y, 0);
  logo.add(siriusGroup);

  const solarGroup = new Group();
  const solar = text('-SOLAR', 1.5, 0.3, mats.gold);
  solar.mesh.position.z = -0.15;
  solarGroup.add(solar.mesh, neonHalo('-SOLAR', 1.5, new Color('#ffcf1a')));
  solarGroup.position.set(left - solar.box.min.x + 0.05, -0.26 - solar.box.max.y, 0);
  logo.add(solarGroup);

  // Gold sparkle above the I of SIRIUS.
  const g = fontData.glyphs;
  const scale = 1.5 / fontData.resolution;
  const iX = left - sirius.box.min.x + (g.S.ha + (g.I.x_min + g.I.x_max) / 2) * scale;
  const sparkle = new Mesh(new ExtrudeGeometry(star4(0.42, 0.08), { depth: 0.08, bevelEnabled: false }), mats.gold);
  sparkle.position.set(iX + 0.32, 0.26 + 1.5 * 0.72 + 0.42, 0);
  logo.add(sparkle);
  const starGlow = glowSprite(new Color('#ffd21f'), 2.6, true);
  starGlow.position.copy(sparkle.position).add(new Vector3(0, 0, 0.1));
  logo.add(starGlow);
  // A mint glow behind each brush.
  const brushGlows = [top, bottom].map((r) => {
    const gl = glowSprite(new Color('#2bff4d'), 2.9);
    gl.material.opacity = 0.45;
    gl.position.copy(r.position).add(new Vector3(0, 0, -0.5));
    logo.add(gl);
    return gl;
  });

  // Centre the whole logo.
  logo.children.forEach((c) => { c.position.x -= 0.3; });

  // --- Animation ---------------------------------------------------------------------------
  let visible = true;
  let frame = null;
  const pointer = new Vector2();
  const start = performance.now();

  const resize = () => {
    const w = el.clientWidth; const h = el.clientHeight;
    if (!w || !h) return;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    // Fit the logo's width (about 11 units) whatever the box proportions.
    const fitW = 12.2 / (2 * Math.tan((camera.fov * Math.PI) / 360) * camera.aspect);
    const fitH = 4.5 / (2 * Math.tan((camera.fov * Math.PI) / 360));
    camera.position.z = Math.max(fitW, fitH);
    camera.updateProjectionMatrix();
    draw(performance.now());
  };

  const TURN_EVERY = 9; // seconds between two full turns
  const TURN_TIME = 1.8;
  const easeInOut = (x) => (x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2);

  function draw(now) {
    const t = (now - start) / 1000;
    if (!reduced) {
      const cycle = t % TURN_EVERY;
      const turn = cycle > TURN_EVERY - TURN_TIME ? easeInOut((cycle - (TURN_EVERY - TURN_TIME)) / TURN_TIME) : 0;
      logo.rotation.y = Math.sin(t * 0.7) * 0.32 + turn * Math.PI * 2 + pointer.x * 0.25;
      logo.rotation.x = Math.sin(t * 0.5) * 0.06 - pointer.y * 0.15;
      logo.position.y = Math.sin(t * 1.3) * 0.08;
      top.userData.spin.rotation.z = -t * 3.2;
      bottom.userData.spin.rotation.z = t * 3.2;
      // The star twinkles: slow turn, pulsing glow and a sharp sparkle every few seconds.
      sparkle.rotation.z = t * 0.8;
      const tw = Math.pow(Math.max(0, Math.sin(t * 2.1)), 12);
      const pulse = 0.75 + Math.sin(t * 4.3) * 0.15 + tw * 0.9;
      starGlow.material.opacity = Math.min(1, pulse);
      starGlow.scale.setScalar(2.2 + pulse * 1.1);
      starGlow.material.rotation = t * 0.3;
      sparkle.scale.setScalar(1 + tw * 0.25);
      // Neon flicker: steady glow with a brief stutter now and then (not at the same time).
      const f = t % 6 > 5.65 ? (Math.sin(t * 90) > 0 ? 0.35 : 1) : 1;
      const f2 = (t + 2.7) % 7 > 6.75 ? (Math.sin(t * 70) > 0 ? 0.4 : 1) : 1;
      mats.gold.emissiveIntensity = (0.75 + Math.sin(t * 3) * 0.08) * f;
      solarGroup.children[1].material.opacity = (0.8 + Math.sin(t * 3) * 0.1) * f;
      mats.mint.emissiveIntensity = (0.7 + Math.sin(t * 2.4) * 0.07) * f2;
      siriusGroup.children[1].material.opacity = (0.7 + Math.sin(t * 2.4) * 0.1) * f2;
      brushGlows.forEach((gl) => { gl.material.opacity = 0.4 * f2; });
    } else {
      logo.rotation.y = -0.18;
    }
    renderer.render(scene, camera);
  }

  const loop = (now) => {
    frame = null;
    draw(now);
    if (visible && !reduced) frame = requestAnimationFrame(loop);
  };
  const play = () => { if (!frame && visible && !reduced) frame = requestAnimationFrame(loop); };

  new ResizeObserver(resize).observe(el);
  new IntersectionObserver(([e]) => { visible = e.isIntersecting; play(); }).observe(el);
  document.addEventListener('visibilitychange', () => { visible = !document.hidden; play(); });
  window.addEventListener('pointermove', (e) => {
    pointer.set((e.clientX / window.innerWidth) * 2 - 1, (e.clientY / window.innerHeight) * 2 - 1);
  }, { passive: true });

  resize();
  el.classList.add('is-ready');
  play();
}

document.querySelectorAll('[data-sirius-logo3d]').forEach((el) => mountSiriusLogo(el));
