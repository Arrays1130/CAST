import * as THREE from 'three';
import { EffectComposer } from 'three/examples/jsm/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/examples/jsm/postprocessing/RenderPass.js';
import { UnrealBloomPass } from 'three/examples/jsm/postprocessing/UnrealBloomPass.js';

const SCENE_COUNT = 5;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function lerp(a, b, t) {
    return a + (b - a) * t;
}

function lerp3(a, b, t) {
    return [lerp(a[0], b[0], t), lerp(a[1], b[1], t), lerp(a[2], b[2], t)];
}

function lerpColor(a, b, t) {
    return new THREE.Color(a).lerp(new THREE.Color(b), t);
}

function easeInOutCubic(t) {
    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

function easeOutExpo(t) {
    return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
}

function damp(current, target, lambda, dt) {
    return lerp(current, target, 1 - Math.exp(-lambda * dt));
}

function makePaperTexture({ title, status, statusBg, statusFg, accent, subtitle, variant = 0 }) {
    const canvas = document.createElement('canvas');
    canvas.width = 768;
    canvas.height = 1080;
    const ctx = canvas.getContext('2d');
    if (! ctx) {
        return null;
    }

    const bg = ctx.createLinearGradient(0, 0, 768, 1080);
    bg.addColorStop(0, '#fffaf2');
    bg.addColorStop(0.45, '#f6f1e8');
    bg.addColorStop(1, '#efe8db');
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, 768, 1080);

    ctx.globalAlpha = 0.18;
    for (let y = 248; y < 920; y += 36) {
        ctx.fillStyle = '#c9bfb0';
        ctx.fillRect(48, y, 672, 1);
    }
    ctx.globalAlpha = 1;

    ctx.fillStyle = 'rgba(255, 90, 60, 0.08)';
    ctx.fillRect(0, 0, 36, 1080);

    const glow = ctx.createRadialGradient(640, 160, 10, 640, 160, 360);
    glow.addColorStop(0, `${accent}33`);
    glow.addColorStop(1, 'transparent');
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, 768, 1080);

    ctx.fillStyle = accent;
    ctx.fillRect(0, 0, 5, 1080);
    ctx.fillRect(0, 0, 768, 3);

    ctx.fillStyle = '#12141a';
    ctx.font = '700 56px "Syne", Georgia, serif';
    ctx.fillText(title, 48, 150);
    ctx.fillStyle = accent;
    ctx.font = '600 18px "Outfit", sans-serif';
    ctx.fillText(subtitle || 'CAST · manuscript', 48, 192);

    for (let i = 0; i < 15; i += 1) {
        ctx.fillStyle = i % 3 === 0 ? 'rgba(18, 20, 26, 0.22)' : 'rgba(18, 20, 26, 0.12)';
        ctx.fillRect(48, 248 + i * 40, 360 + ((i * 89 + variant * 37) % 240), 7);
    }

    if (variant % 2 === 0) {
        ctx.fillStyle = `${accent}14`;
        ctx.strokeStyle = `${accent}55`;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.roundRect(48, 730, 300, 120, 16);
        ctx.fill();
        ctx.stroke();
        ctx.fillStyle = '#12141a';
        ctx.font = '600 17px "Outfit", sans-serif';
        ctx.fillText('Reference scan', 68, 770);
        ctx.fillStyle = 'rgba(22, 20, 28, 0.55)';
        ctx.font = '400 14px "Outfit", sans-serif';
        ctx.fillText('3 unused · 1 missing', 68, 800);
    }

    ctx.fillStyle = statusBg;
    ctx.beginPath();
    ctx.roundRect(48, 980, 250, 50, 25);
    ctx.fill();
    ctx.fillStyle = statusFg;
    ctx.font = '700 18px "Outfit", sans-serif';
    ctx.fillText(status, 70, 1012);

    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.anisotropy = 8;

    return texture;
}

const SCENE_TEXTURES = [
    { title: 'Chapter 1', status: 'For review', statusBg: '#d7ecf6', statusFg: '#16384a', accent: '#ff5a3c', subtitle: 'Introduction', variant: 0 },
    { title: 'Proposal', status: 'Submitted', statusBg: '#ece7de', statusFg: '#3b372f', accent: '#7c3aed', subtitle: 'Upload · Drive', variant: 1 },
    { title: 'Full draft', status: 'Needs revision', statusBg: '#ffe1cc', statusFg: '#6a3212', accent: '#ea580c', subtitle: 'Adviser notes', variant: 2 },
    { title: 'References', status: 'Scanning…', statusBg: '#dbeafe', statusFg: '#1e3a5f', accent: '#0284c7', subtitle: 'Reference Detective', variant: 3 },
    { title: 'Defense', status: 'Approved', statusBg: '#d8f0d8', statusFg: '#1b3d24', accent: '#059669', subtitle: 'Ready to ship', variant: 4 },
];

const SCENE_PALETTES = [
    { fog: 0xf6f1e8, ember: 0xff5a3c, accent: 0xff5a3c, cyan: 0x0284c7, grid: 0xff5a3c, bloom: 0.12 },
    { fog: 0xf6f1e8, ember: 0x7c3aed, accent: 0x7c3aed, cyan: 0xa78bfa, grid: 0x7c3aed, bloom: 0.14 },
    { fog: 0xf6f1e8, ember: 0xea580c, accent: 0xea580c, cyan: 0xfb923c, grid: 0xea580c, bloom: 0.13 },
    { fog: 0xf6f1e8, ember: 0x0284c7, accent: 0x0284c7, cyan: 0x38bdf8, grid: 0x0284c7, bloom: 0.15 },
    { fog: 0xf6f1e8, ember: 0x059669, accent: 0x059669, cyan: 0x34d399, grid: 0x059669, bloom: 0.12 },
];

const SCENE_POSES = [
    { cam: [1.55, 0.55, 7.4], look: [1.7, 0.25, 0], stage: [1.85, 0.05, 0], rotY: -0.42, fov: 34, hero: { x: 0.15, y: 0.18, z: 0.15, rotX: -0.18, rotY: -0.62, rotZ: 0.08, scale: 1.62 } },
    { cam: [0.05, 0.12, 5.5], look: [1.15, 0.05, 0.1], stage: [1.1, -0.08, 0.35], rotY: 0.4, fov: 40, hero: { x: 0.55, y: -0.02, z: 0.1, rotX: -0.1, rotY: 0.48, rotZ: -0.06, scale: 1.42 } },
    { cam: [2.5, 0.9, 6.8], look: [1.65, 0.4, -0.15], stage: [2.0, 0.2, -0.15], rotY: -0.78, fov: 32, hero: { x: -0.15, y: 0.32, z: 0.4, rotX: -0.22, rotY: -0.9, rotZ: 0.1, scale: 1.72 } },
    { cam: [0.25, 0.48, 4.9], look: [1.0, 0.18, 0], stage: [1.0, 0.22, 0.5], rotY: 0.68, fov: 42, hero: { x: 0.85, y: -0.05, z: -0.2, rotX: 0.04, rotY: 0.62, rotZ: -0.08, scale: 1.48 } },
    { cam: [1.8, 0.85, 8.2], look: [1.85, 0.4, 0], stage: [1.9, 0.35, 0], rotY: -0.28, fov: 30, hero: { x: 0.05, y: 0.42, z: 0.12, rotX: -0.2, rotY: -0.45, rotZ: 0.05, scale: 1.78 } },
];

function glowSprite(color, size) {
    const c = document.createElement('canvas');
    c.width = 256;
    c.height = 256;
    const ctx = c.getContext('2d');
    if (! ctx) {
        return null;
    }
    const g = ctx.createRadialGradient(128, 128, 6, 128, 128, 128);
    g.addColorStop(0, color);
    g.addColorStop(0.4, color.replace(/[\d.]+\)$/, '0.22)'));
    g.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, 256, 256);
    const map = new THREE.CanvasTexture(c);
    const mat = new THREE.SpriteMaterial({ map, transparent: true, depthWrite: false, opacity: 0.7 });
    const sprite = new THREE.Sprite(mat);
    sprite.scale.set(size, size, 1);

    return sprite;
}

function easeOutBack(t) {
    const c1 = 1.70158;
    const c3 = c1 + 1;

    return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
}

function makePageEdgeTexture() {
    const canvas = document.createElement('canvas');
    canvas.width = 64;
    canvas.height = 256;
    const ctx = canvas.getContext('2d');
    if (! ctx) {
        return null;
    }
    ctx.fillStyle = '#f3eadc';
    ctx.fillRect(0, 0, 64, 256);
    for (let y = 0; y < 256; y += 3) {
        ctx.fillStyle = y % 9 === 0 ? '#d9cfbf' : '#ebe3d4';
        ctx.fillRect(0, y, 64, 2);
    }
    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(1, 4);

    return texture;
}

function makeHeroPaper(texture, accent) {
    const edgeMap = makePageEdgeTexture();
    const group = new THREE.Group();

    const coverMat = new THREE.MeshPhysicalMaterial({
        color: 0x2a2733,
        roughness: 0.55,
        metalness: 0.08,
        clearcoat: 0.35,
    });
    const accentStrip = new THREE.MeshPhysicalMaterial({
        color: new THREE.Color(accent),
        roughness: 0.4,
        metalness: 0.12,
        emissive: new THREE.Color(accent),
        emissiveIntensity: 0.12,
    });
    const pageEdge = new THREE.MeshPhysicalMaterial({
        map: edgeMap || undefined,
        color: 0xf0e6d6,
        roughness: 0.85,
        metalness: 0.02,
    });
    const pageFace = new THREE.MeshPhysicalMaterial({
        map: texture,
        roughness: 0.38,
        metalness: 0.02,
        clearcoat: 0.28,
        clearcoatRoughness: 0.45,
        emissive: 0xf6f1e8,
        emissiveIntensity: 0.035,
    });
    const pageBack = new THREE.MeshPhysicalMaterial({
        color: 0xf7f1e6,
        roughness: 0.55,
        metalness: 0.02,
    });

    const block = new THREE.Mesh(
        new THREE.BoxGeometry(2.05, 2.78, 0.28),
        [pageEdge, accentStrip, pageEdge, pageEdge, pageFace, pageBack],
    );
    block.castShadow = true;
    block.receiveShadow = true;
    group.add(block);

    const cover = new THREE.Mesh(
        new THREE.BoxGeometry(2.12, 2.86, 0.045),
        coverMat,
    );
    cover.position.z = -0.155;
    cover.castShadow = true;
    group.add(cover);

    const ribbon = new THREE.Mesh(
        new THREE.BoxGeometry(0.07, 1.1, 0.02),
        accentStrip,
    );
    ribbon.position.set(-0.92, -0.7, 0.16);
    group.add(ribbon);

    const sheets = [0, 1, 2].map((i) => {
        const sheet = new THREE.Mesh(
            new THREE.BoxGeometry(1.98, 2.7, 0.018),
            [
                pageEdge,
                pageEdge,
                pageEdge,
                pageEdge,
                pageFace.clone(),
                pageBack,
            ],
        );
        sheet.position.set(0.02 * i, 0.01 * i, 0.08 - i * 0.045);
        sheet.castShadow = true;
        group.add(sheet);

        return sheet;
    });

    const outline = new THREE.LineSegments(
        new THREE.EdgesGeometry(new THREE.BoxGeometry(2.12, 2.86, 0.32), 18),
        new THREE.LineBasicMaterial({ color: accent, transparent: true, opacity: 0.28 }),
    );
    group.add(outline);

    return { group, sheets, frontMaterial: pageFace };
}

function makeGhostPaper(texture, scale = 0.5) {
    return new THREE.Mesh(
        new THREE.BoxGeometry(2.05 * scale, 2.78 * scale, 0.08 * scale),
        new THREE.MeshPhysicalMaterial({
            map: texture,
            roughness: 0.55,
            metalness: 0.02,
            transparent: true,
            opacity: 0.22,
            depthWrite: false,
        }),
    );
}

function makeCrystal(color) {
    return new THREE.Mesh(
        new THREE.IcosahedronGeometry(0.28, 0),
        new THREE.MeshPhysicalMaterial({
            color,
            emissive: color,
            emissiveIntensity: 0.22,
            roughness: 0.2,
            metalness: 0.35,
            clearcoat: 0.8,
            transparent: true,
            opacity: 0.78,
        }),
    );
}

function makeGrid(size, divisions, color) {
    const step = size / divisions;
    const half = size / 2;
    const vertices = [];
    for (let i = 0; i <= divisions; i += 1) {
        const pos = -half + i * step;
        vertices.push(-half, 0, pos, half, 0, pos, pos, 0, -half, pos, 0, half);
    }
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));

    return new THREE.LineSegments(
        geometry,
        new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.08 }),
    );
}

function readScrollProgress() {
    const max = document.documentElement.scrollHeight - window.innerHeight;
    if (max <= 0) {
        return 0;
    }

    return Math.min(1, Math.max(0, window.scrollY / max));
}

function progressToSceneFloat(progress) {
    return Math.min(SCENE_COUNT - 0.001, progress * SCENE_COUNT);
}

export function mountLandingScene(canvas) {
    if (! canvas || prefersReducedMotion()) {
        document.querySelectorAll('.landing-scene-copy').forEach((el) => el.classList.add('is-visible'));

        return () => {};
    }

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0xf6f1e8, 0);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(SCENE_PALETTES[0].fog, 0.022);

    const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 60);
    const stage = new THREE.Group();
    scene.add(stage);

    const textures = SCENE_TEXTURES.map((def) => makePaperTexture(def));
    const heroes = textures.map((tex, i) => {
        const { group, sheets } = makeHeroPaper(tex, SCENE_TEXTURES[i].accent);
        group.visible = false;
        stage.add(group);

        return { mesh: group, sheets };
    });
    heroes[0].mesh.visible = true;

    const ghosts = textures.map((tex, i) => {
        const ghost = makeGhostPaper(tex, 0.4 + (i % 2) * 0.05);
        ghost.visible = false;
        stage.add(ghost);

        return ghost;
    });

    const crystal = makeCrystal(0xff5a3c);
    stage.add(crystal);

    const orbA = glowSprite('rgba(255,90,60,0.28)', 5.4);
    const orbB = glowSprite('rgba(2,132,199,0.22)', 4.4);
    const orbC = glowSprite('rgba(124,58,237,0.18)', 3.6);
    [orbA, orbB, orbC].forEach((orb) => orb && scene.add(orb));

    const emberGlow = glowSprite('rgba(255,90,60,0.32)', 2.4);
    if (emberGlow) {
        stage.add(emberGlow);
    }

    const badge = new THREE.Mesh(
        new THREE.SphereGeometry(0.18, 48, 48),
        new THREE.MeshPhysicalMaterial({
            color: 0xff5a3c,
            emissive: 0xff5a3c,
            emissiveIntensity: 0.35,
            roughness: 0.22,
            metalness: 0.25,
            clearcoat: 0.7,
        }),
    );
    stage.add(badge);

    const rings = [0.7, 0.95].map((radius, i) => {
        const ring = new THREE.Mesh(
            new THREE.TorusGeometry(radius, 0.012, 12, 100),
            new THREE.MeshPhysicalMaterial({
                color: 0x0284c7,
                emissive: 0x0284c7,
                emissiveIntensity: 0.25,
                roughness: 0.28,
                metalness: 0.35,
                transparent: true,
                opacity: 0.28 - i * 0.08,
            }),
        );
        ring.rotation.x = Math.PI / 2.15 + i * 0.1;
        stage.add(ring);

        return ring;
    });

    const glassA = new THREE.Mesh(
        new THREE.PlaneGeometry(1.35, 0.78),
        new THREE.MeshPhysicalMaterial({
            color: 0x0284c7,
            transparent: true,
            opacity: 0.06,
            roughness: 0.12,
            metalness: 0.08,
            clearcoat: 0.6,
            side: THREE.DoubleSide,
            depthWrite: false,
        }),
    );
    const glassB = new THREE.Mesh(
        new THREE.PlaneGeometry(0.7, 1.35),
        new THREE.MeshPhysicalMaterial({
            color: 0xff5a3c,
            transparent: true,
            opacity: 0.06,
            roughness: 0.12,
            metalness: 0.08,
            clearcoat: 0.6,
            side: THREE.DoubleSide,
            depthWrite: false,
        }),
    );
    stage.add(glassA, glassB);

    const grid = makeGrid(24, 48, 0xff5a3c);
    grid.position.set(1.5, -1.95, 0);
    scene.add(grid);

    const dustCount = 280;
    const dustPositions = new Float32Array(dustCount * 3);
    for (let i = 0; i < dustCount; i += 1) {
        dustPositions[i * 3] = (Math.random() - 0.3) * 18;
        dustPositions[i * 3 + 1] = (Math.random() - 0.5) * 11;
        dustPositions[i * 3 + 2] = (Math.random() - 0.5) * 12;
    }
    const dust = new THREE.Points(
        new THREE.BufferGeometry().setAttribute('position', new THREE.BufferAttribute(dustPositions, 3)),
        new THREE.PointsMaterial({
            color: 0xc4a484,
            size: 0.02,
            transparent: true,
            opacity: 0.22,
            depthWrite: false,
        }),
    );
    scene.add(dust);

    scene.add(new THREE.AmbientLight(0xf6f1e8, 0.78));
    const key = new THREE.DirectionalLight(0xfff8ef, 1.15);
    key.position.set(5.5, 8.5, 6);
    key.castShadow = true;
    scene.add(key);
    const emberLight = new THREE.PointLight(0xff5a3c, 8, 18, 2);
    emberLight.position.set(-3, 1.8, 2.8);
    scene.add(emberLight);
    const cyanLight = new THREE.PointLight(0x0284c7, 6, 15, 2);
    cyanLight.position.set(4, 0, 2.2);
    scene.add(cyanLight);
    const rimLight = new THREE.PointLight(0x7c3aed, 4, 13, 2);
    rimLight.position.set(0.4, 2.8, -3.2);
    scene.add(rimLight);

    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(11, 64),
        new THREE.MeshStandardMaterial({ color: 0xefe8db, roughness: 0.92, metalness: 0.02 }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.set(1.5, -1.96, 0);
    floor.receiveShadow = true;
    scene.add(floor);

    const composer = new EffectComposer(renderer);
    composer.addPass(new RenderPass(scene, camera));
    const bloom = new UnrealBloomPass(new THREE.Vector2(1, 1), 0.12, 0.55, 0.92);
    composer.addPass(bloom);

    const mouse = { x: 0.1, y: 0 };
    const smoothMouse = { x: 0.1, y: 0 };
    const onMove = (event) => {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -((event.clientY / window.innerHeight) * 2 - 1);
    };
    window.addEventListener('pointermove', onMove, { passive: true });

    const sections = [...document.querySelectorAll('[data-landing-scene]')];
    const dots = [...document.querySelectorAll('.landing-dot')];
    const scrollHint = document.querySelector('.landing-scroll-hint');
    const progressFill = document.querySelector('.landing-progress-fill');
    const sceneCounter = document.querySelector('.landing-scene-counter');

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            const target = sections[Number(dot.dataset.scrollTo)];
            target?.scrollIntoView({ behavior: 'smooth' });
        });
    });

    let sceneFloat = 0;
    let lastScroll = window.scrollY;
    let scrollVelocity = 0;
    let lastTime = performance.now();

    const resize = () => {
        const width = Math.max(canvas.clientWidth, 1);
        const height = Math.max(canvas.clientHeight, 1);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height, false);
        composer.setSize(width, height);
        bloom.resolution.set(width, height);
    };
    resize();
    const observer = new ResizeObserver(resize);
    observer.observe(canvas);

    let frame = 0;
    const tick = (time) => {
        frame = requestAnimationFrame(tick);
        const t = time * 0.001;
        const dt = Math.min(0.05, (time - lastTime) / 1000);
        lastTime = time;

        const scrollY = window.scrollY;
        scrollVelocity = damp(scrollVelocity, (scrollY - lastScroll) * 0.055, 10, dt);
        lastScroll = scrollY;

        smoothMouse.x = damp(smoothMouse.x, mouse.x, 4.2, dt);
        smoothMouse.y = damp(smoothMouse.y, mouse.y, 4.2, dt);

        const scrollProgress = readScrollProgress();
        const targetFloat = progressToSceneFloat(scrollProgress);
        sceneFloat = damp(sceneFloat, targetFloat, 5.2, dt);

        const indexA = Math.floor(sceneFloat);
        const indexB = Math.min(SCENE_COUNT - 1, indexA + 1);
        const rawBlend = sceneFloat - indexA;
        const blend = easeInOutCubic(rawBlend);
        const poseA = SCENE_POSES[indexA];
        const poseB = SCENE_POSES[indexB];
        const palA = SCENE_PALETTES[indexA];
        const palB = SCENE_PALETTES[indexB];

        const palette = {
            fog: lerpColor(palA.fog, palB.fog, blend),
            ember: lerpColor(palA.ember, palB.ember, blend),
            accent: lerpColor(palA.accent, palB.accent, blend),
            cyan: lerpColor(palA.cyan, palB.cyan, blend),
            grid: lerpColor(palA.grid, palB.grid, blend),
        };
        bloom.strength = lerp(palA.bloom, palB.bloom, blend);
        scene.fog.color.copy(palette.fog);
        emberLight.color.copy(palette.ember);
        cyanLight.color.copy(palette.cyan);
        rimLight.color.copy(palette.accent);
        grid.material.color.copy(palette.grid);
        dust.material.color.copy(palette.accent);
        crystal.material.color.copy(palette.accent);
        crystal.material.emissive.copy(palette.accent);

        heroes.forEach((h, i) => {
            h.mesh.visible = true;
            let opacity = 0;
            if (i === indexA) {
                opacity = 1 - blend;
            }
            if (i === indexB) {
                opacity = blend;
            }
            h.mesh.traverse((child) => {
                if (! child.material) {
                    return;
                }
                const mats = Array.isArray(child.material) ? child.material : [child.material];
                mats.forEach((mat) => {
                    mat.transparent = opacity < 0.98;
                    mat.opacity = opacity;
                    mat.depthWrite = opacity > 0.35;
                });
            });
        });

        ghosts.forEach((ghost, i) => {
            const active = i === indexA || i === indexB;
            ghost.visible = active;
            let opacity = 0;
            if (i === indexA) {
                opacity = (1 - blend) * 0.18;
            }
            if (i === indexB) {
                opacity = blend * 0.18;
            }
            ghost.material.opacity = opacity;
            const orbit = t * 0.28 + i * 1.35;
            ghost.position.set(
                Math.sin(orbit) * 4.1 - 0.2,
                0.55 + Math.cos(orbit * 0.8) * 0.75,
                Math.cos(orbit) * 2.1 - 2.6,
            );
            ghost.rotation.set(-0.22 + Math.sin(t * 0.5 + i) * 0.1, orbit * 0.4, 0.12);
        });

        const heroPose = {
            x: lerp(poseA.hero.x, poseB.hero.x, blend),
            y: lerp(poseA.hero.y, poseB.hero.y, blend),
            z: lerp(poseA.hero.z, poseB.hero.z, blend),
            rotX: lerp(poseA.hero.rotX, poseB.hero.rotX, blend),
            rotY: lerp(poseA.hero.rotY, poseB.hero.rotY, blend),
            rotZ: lerp(poseA.hero.rotZ, poseB.hero.rotZ, blend),
            scale: lerp(poseA.hero.scale, poseB.hero.scale, blend),
        };
        const floatY = Math.sin(t * 0.95) * 0.16 + Math.sin(t * 0.37) * 0.05;
        const floatX = Math.sin(t * 0.48) * 0.08;
        const idleYaw = Math.sin(t * 0.42) * 0.14;
        const idlePitch = Math.cos(t * 0.55) * 0.07;
        const idleRoll = Math.sin(t * 0.33) * 0.04;
        const scrollAmt = Math.min(1, Math.abs(scrollVelocity) * 2.4);
        const scrollPulse = easeOutExpo(scrollAmt) * 0.1;
        const scrollKick = easeOutBack(scrollAmt) * 0.18;

        heroes.forEach((hero, i) => {
            if (i !== indexA && i !== indexB) {
                return;
            }
            const transitionOffset = i === indexA ? -blend : 1 - blend;
            const turn = Math.sin(Math.abs(transitionOffset) * Math.PI);
            const pageFlip = turn * (i === indexA ? -1.35 : 1.35);
            const lift = turn * 0.55;

            hero.mesh.position.set(
                heroPose.x + floatX + transitionOffset * 0.85 + smoothMouse.x * 0.12,
                heroPose.y + floatY + Math.abs(transitionOffset) * 0.35 + lift * 0.15,
                heroPose.z - Math.abs(transitionOffset) * 0.7 - scrollKick * 0.25,
            );
            hero.mesh.rotation.set(
                heroPose.rotX + idlePitch + pageFlip * 0.22 + smoothMouse.y * 0.1 + scrollVelocity * 0.35,
                heroPose.rotY + idleYaw + pageFlip * 0.95 + transitionOffset * 0.35 + smoothMouse.x * 0.22,
                heroPose.rotZ + idleRoll + transitionOffset * 0.12 + scrollVelocity * 0.15,
            );
            hero.mesh.scale.setScalar(heroPose.scale + scrollPulse - Math.abs(transitionOffset) * 0.12);

            (hero.sheets || []).forEach((sheet, sheetIndex) => {
                const peel = Math.max(0, turn - sheetIndex * 0.18);
                const hinge = peel * peel * (i === indexA ? -1.55 : 1.55);
                sheet.rotation.y = hinge;
                sheet.position.x = 0.02 * sheetIndex + Math.sin(Math.abs(hinge) * 0.55) * 0.28;
                sheet.position.z = 0.08 - sheetIndex * 0.045 + peel * 0.12;
                sheet.rotation.x = -peel * 0.08;
            });
        });

        const cam = lerp3(poseA.cam, poseB.cam, blend);
        const look = lerp3(poseA.look, poseB.look, blend);
        const stagePos = lerp3(poseA.stage, poseB.stage, blend);
        const stageRotY = lerp(poseA.rotY, poseB.rotY, blend);
        const targetFov = lerp(poseA.fov, poseB.fov, blend) + Math.abs(scrollVelocity) * 7 + Math.sin(blend * Math.PI) * 3.5;

        stage.position.set(stagePos[0], stagePos[1] + Math.sin(t * 0.6) * 0.03, stagePos[2]);
        stage.rotation.y = damp(stage.rotation.y, stageRotY + smoothMouse.x * 0.55, 6.5, dt);
        stage.rotation.x = damp(stage.rotation.x, smoothMouse.y * 0.22 + scrollVelocity * 0.14, 6.5, dt);
        stage.rotation.z = damp(stage.rotation.z, smoothMouse.x * -0.04, 5, dt);

        camera.position.x = damp(camera.position.x, cam[0] + smoothMouse.x * 0.75, 5.6, dt);
        camera.position.y = damp(camera.position.y, cam[1] + smoothMouse.y * 0.4, 5.6, dt);
        camera.position.z = damp(camera.position.z, cam[2] - scrollKick * 0.9, 5.6, dt);
        camera.fov = damp(camera.fov, targetFov, 5.2, dt);
        camera.updateProjectionMatrix();
        camera.lookAt(
            look[0] + smoothMouse.x * 0.15,
            look[1] + smoothMouse.y * 0.1,
            look[2],
        );

        badge.position.set(
            heroPose.x + 1.45,
            heroPose.y + 1.25 + Math.sin(t * 1.25) * 0.28,
            heroPose.z + 0.85,
        );
        badge.material.emissive.copy(palette.ember);
        if (emberGlow) {
            emberGlow.position.copy(badge.position);
            emberGlow.material.color.copy(palette.ember);
        }

        crystal.position.set(
            heroPose.x - 1.85,
            heroPose.y + 1.05 + Math.sin(t * 1.05) * 0.28,
            heroPose.z + 0.45,
        );
        crystal.rotation.set(t * 0.75, t * 0.95, t * 0.35);

        rings.forEach((ring, i) => {
            ring.position.set(heroPose.x - 1.65 + i * 0.12, heroPose.y + 0.75, heroPose.z + 0.25);
            ring.rotation.z = t * (0.48 + i * 0.22);
            ring.rotation.x = Math.PI / 2.15 + i * 0.1 + Math.sin(t * 0.4) * 0.08;
            ring.material.emissive.copy(palette.cyan);
            ring.material.color.copy(palette.cyan);
        });

        const angleA = t * 0.22;
        glassA.position.set(heroPose.x + Math.sin(angleA) * 2.45, heroPose.y + 0.25, heroPose.z + Math.cos(angleA) * 1.2 - 0.35);
        glassA.rotation.set(-0.32, angleA * 1.2, 0.12);
        glassA.material.color.copy(palette.cyan);

        const angleB = t * 0.22 + Math.PI;
        glassB.position.set(heroPose.x + Math.sin(angleB) * 2.6, heroPose.y + 0.85, heroPose.z + Math.cos(angleB) * 1.25 - 0.35);
        glassB.rotation.set(-0.26, angleB * 1.15, 0.1);
        glassB.material.color.copy(palette.accent);

        if (orbA) {
            orbA.position.set(-3.8 + smoothMouse.x * 0.7, 2.4 + smoothMouse.y * 0.55, -4.2);
            orbA.material.color.copy(palette.ember);
        }
        if (orbB) {
            orbB.position.set(5.2 + smoothMouse.x * 0.5, -0.6 + Math.sin(t * 0.5) * 0.3, -3.2);
            orbB.material.color.copy(palette.cyan);
        }
        if (orbC) {
            orbC.position.set(1.2, 3.8 + Math.cos(t * 0.4) * 0.25, -5.2);
            orbC.material.color.copy(palette.accent);
        }

        dust.rotation.y = t * 0.055;
        dust.rotation.x = Math.sin(t * 0.2) * 0.04;
        grid.rotation.y = t * 0.02;

        const activeIndex = blend > 0.5 ? indexB : indexA;
        sections.forEach((section, i) => {
            const rect = section.getBoundingClientRect();
            const center = rect.top + rect.height / 2;
            const dist = Math.abs(center - window.innerHeight / 2) / window.innerHeight;
            const visible = dist < 0.38;
            section.querySelector('.landing-scene-copy')?.classList.toggle('is-visible', visible);
            section.dataset.active = visible ? 'true' : 'false';
            if (visible && sceneCounter) {
                sceneCounter.textContent = String(i + 1).padStart(2, '0');
            }
        });

        document.body.dataset.landingScene = String(activeIndex);
        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === activeIndex);
        });

        if (scrollHint) {
            scrollHint.style.opacity = scrollY > 70 ? '0' : '1';
        }
        if (progressFill) {
            progressFill.style.transform = `scaleX(${scrollProgress})`;
        }

        composer.render();
    };
    frame = requestAnimationFrame(tick);

    return () => {
        cancelAnimationFrame(frame);
        observer.disconnect();
        window.removeEventListener('pointermove', onMove);
        composer.dispose();
        renderer.dispose();
        textures.forEach((map) => map?.dispose());
    };
}

const canvas = document.getElementById('cast-3d');
if (canvas) {
    mountLandingScene(canvas);
}
