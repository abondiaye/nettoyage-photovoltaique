// Splash Screen with WebGL Particle Animation
(function() {
  'use strict';

  class ParticleAnimation {
    constructor(canvasId) {
      this.canvas = document.getElementById(canvasId);
      if (!this.canvas) return;

      try {
        this.gl = this.canvas.getContext('webgl', { alpha: true });
        if (!this.gl) {
          this.gl = this.canvas.getContext('experimental-webgl', { alpha: true });
        }
        if (!this.gl) {
          this.useFallback();
          return;
        }
      } catch (e) {
        this.useFallback();
        return;
      }

      this.setupCanvas();
      this.initShaders();
      this.initBuffers();
      this.createParticles();
      this.animate();
    }

    setupCanvas() {
      this.canvas.width = window.innerWidth;
      this.canvas.height = window.innerHeight;
      this.gl.viewport(0, 0, this.canvas.width, this.canvas.height);
      this.gl.clearColor(0.0, 0.0, 0.0, 0.0);
      this.gl.enable(this.gl.BLEND);
      this.gl.blendFunc(this.gl.SRC_ALPHA, this.gl.ONE);

      window.addEventListener('resize', () => this.onWindowResize());
    }

    onWindowResize() {
      this.canvas.width = window.innerWidth;
      this.canvas.height = window.innerHeight;
      this.gl.viewport(0, 0, this.canvas.width, this.canvas.height);
    }

    initShaders() {
      const vertexShaderSource = `
        attribute vec2 position;
        attribute vec3 color;
        attribute float size;
        varying vec3 vColor;

        void main() {
          gl_PointSize = size;
          gl_Position = vec4(position, 0.0, 1.0);
          vColor = color;
        }
      `;

      const fragmentShaderSource = `
        precision mediump float;
        varying vec3 vColor;

        void main() {
          float dist = distance(gl_PointCoord, vec2(0.5, 0.5));
          if (dist > 0.5) discard;

          float alpha = 1.0 - (dist * 2.0);
          gl_FragColor = vec4(vColor, alpha * 0.8);
        }
      `;

      const vertexShader = this.compileShader(vertexShaderSource, this.gl.VERTEX_SHADER);
      const fragmentShader = this.compileShader(fragmentShaderSource, this.gl.FRAGMENT_SHADER);

      this.program = this.gl.createProgram();
      this.gl.attachShader(this.program, vertexShader);
      this.gl.attachShader(this.program, fragmentShader);
      this.gl.linkProgram(this.program);
      this.gl.useProgram(this.program);

      this.positionLocation = this.gl.getAttribLocation(this.program, 'position');
      this.colorLocation = this.gl.getAttribLocation(this.program, 'color');
      this.sizeLocation = this.gl.getAttribLocation(this.program, 'size');
    }

    compileShader(source, type) {
      const shader = this.gl.createShader(type);
      this.gl.shaderSource(shader, source);
      this.gl.compileShader(shader);
      return shader;
    }

    initBuffers() {
      this.positionBuffer = this.gl.createBuffer();
      this.colorBuffer = this.gl.createBuffer();
      this.sizeBuffer = this.gl.createBuffer();
    }

    createParticles() {
      this.particles = [];
      const particleCount = 150;

      for (let i = 0; i < particleCount; i++) {
        this.particles.push({
          x: (Math.random() - 0.5) * 2,
          y: (Math.random() - 0.5) * 2,
          vx: (Math.random() - 0.5) * 0.01,
          vy: (Math.random() - 0.5) * 0.015 - 0.005,
          life: Math.random() * 0.5 + 0.5,
          maxLife: Math.random() * 0.5 + 0.5,
          size: Math.random() * 2 + 2,
          // Vert + Or (Sirius-Solar colors)
          r: Math.random() > 0.5 ? 0.27 : 1.0,
          g: Math.random() > 0.5 ? 0.48 : 0.84,
          b: Math.random() > 0.5 ? 0.38 : 0.0
        });
      }
    }

    animate() {
      this.gl.clear(this.gl.COLOR_BUFFER_BIT);

      // Update particles
      this.particles.forEach(p => {
        p.x += p.vx;
        p.y += p.vy;
        p.life -= 0.01;
        p.vy -= 0.0005; // gravity
      });

      // Remove dead particles
      this.particles = this.particles.filter(p => p.life > 0);

      // Add new particles
      if (this.particles.length < 150) {
        for (let i = 0; i < 3; i++) {
          this.particles.push({
            x: (Math.random() - 0.5) * 2,
            y: -1,
            vx: (Math.random() - 0.5) * 0.01,
            vy: (Math.random() - 0.5) * 0.015 - 0.005,
            life: 1,
            maxLife: Math.random() * 0.5 + 0.5,
            size: Math.random() * 2 + 2,
            r: Math.random() > 0.5 ? 0.27 : 1.0,
            g: Math.random() > 0.5 ? 0.48 : 0.84,
            b: Math.random() > 0.5 ? 0.38 : 0.0
          });
        }
      }

      // Render particles
      if (this.particles.length > 0) {
        const positions = new Float32Array(this.particles.flatMap(p => [p.x, p.y]));
        const colors = new Float32Array(this.particles.flatMap(p => [p.r, p.g, p.b]));
        const sizes = new Float32Array(this.particles.map(p => p.size * (p.life / p.maxLife)));

        this.gl.bindBuffer(this.gl.ARRAY_BUFFER, this.positionBuffer);
        this.gl.bufferData(this.gl.ARRAY_BUFFER, positions, this.gl.DYNAMIC_DRAW);
        this.gl.vertexAttribPointer(this.positionLocation, 2, this.gl.FLOAT, false, 0, 0);
        this.gl.enableVertexAttribArray(this.positionLocation);

        this.gl.bindBuffer(this.gl.ARRAY_BUFFER, this.colorBuffer);
        this.gl.bufferData(this.gl.ARRAY_BUFFER, colors, this.gl.DYNAMIC_DRAW);
        this.gl.vertexAttribPointer(this.colorLocation, 3, this.gl.FLOAT, false, 0, 0);
        this.gl.enableVertexAttribArray(this.colorLocation);

        this.gl.bindBuffer(this.gl.ARRAY_BUFFER, this.sizeBuffer);
        this.gl.bufferData(this.gl.ARRAY_BUFFER, sizes, this.gl.DYNAMIC_DRAW);
        this.gl.vertexAttribPointer(this.sizeLocation, 1, this.gl.FLOAT, false, 0, 0);
        this.gl.enableVertexAttribArray(this.sizeLocation);

        this.gl.drawArrays(this.gl.POINTS, 0, this.particles.length);
      }

      requestAnimationFrame(() => this.animate());
    }

    useFallback() {
      // CSS-only particle fallback
      const container = this.canvas.parentElement;
      for (let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        const tx = (Math.random() - 0.5) * 100;
        const duration = Math.random() * 3 + 2;
        particle.style.cssText = `
          left: ${Math.random() * 100}%;
          top: ${Math.random() * 100}%;
          --tx: ${tx}px;
          animation-duration: ${duration}s;
          animation-delay: ${Math.random() * 1}s;
          background: ${Math.random() > 0.5 ? '#D4A842' : '#1B4D3E'};
        `;
        container.appendChild(particle);
      }
    }
  }

  // Initialize on load
  class SplashScreen {
    constructor() {
      this.splashScreen = document.querySelector('.splash-screen');
      if (!this.splashScreen) return;

      // Start animation
      new ParticleAnimation('particle-canvas');

      // Hide splash screen after 4 seconds
      setTimeout(() => this.hideSplash(), 4000);
    }

    hideSplash() {
      if (this.splashScreen) {
        this.splashScreen.classList.add('hidden');
        // Remove from DOM after animation
        setTimeout(() => {
          this.splashScreen.remove();
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
