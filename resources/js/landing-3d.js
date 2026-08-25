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
    bg.addColorStop(0, '#141821');
    bg.addColorStop(0.5, '#0b0d13');
    bg.addColorStop(1, '#05060a');
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, 768, 1080);

    ctx.globalAlpha = 0.06;
    for (let y = 0; y < 1080; y += 24) {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, y, 768, 1);
    }
    ctx.globalAlpha = 1;

    const glow = ctx.createRadialGradient(640, 160, 10, 640, 160, 360);
    glow.addColorStop(0, `${accent}66`);
    glow.addColorStop(1, 'transparent');
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, 768, 1080);

    ctx.fillStyle = accent;
    ctx.fillRect(0, 0, 5, 1080);
    ctx.fillRect(0, 0, 768, 3);

    ctx.fillStyle = '#f7f4ef';
    ctx.font = '700 56px "Syne", Georgia, serif';
    ctx.fillText(title, 48, 150);
    ctx.fillStyle = accent;
    ctx.font = '600 18px "Outfit", sans-serif';
    ctx.fillText(subtitle || 'CAST · manuscript', 48, 192);

    for (let i = 0; i < 15; i += 1) {
        ctx.fillStyle = i % 3 === 0 ? '#2c3344' : '#232938';
        ctx.fillRect(48, 248 + i * 40, 360 + ((i * 89 + variant * 37) % 240), 7);
    }

    if (variant % 2 === 0) {
        ctx.fillStyle = `${accent}18`;
        ctx.strokeStyle = `${accent}55`;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.roundRect(48, 730, 300, 120, 16);
        ctx.fill();
        ctx.stroke();
        ctx.fillStyle = '#d6dbe6';
        ctx.font = '600 17px "Outfit", sans-serif';
        ctx.fillText('Reference scan', 68, 770);
        ctx.fillStyle = '#7b8496';
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
    { title: 'Chapter 1', status: 'For review', statusBg: '#1e3a4f', statusFg: '#7dd3fc', accent: '#ff5a3c', subtitle: 'Introduction', variant: 0 },
    { title: 'Proposal', status: 'Submitted', statusBg: '#2e1f4a', statusFg: '#c4b5fd', accent: '#a78bfa', subtitle: 'Upload · Drive', variant: 1 },
    { title: 'Full draft', status: 'Needs revision', statusBg: '#4a2818', statusFg: '#fdba74', accent: '#fb923c', subtitle: 'Adviser notes', variant: 2 },
    { title: 'References', status: 'Scanning…', statusBg: '#1a2e4a', statusFg: '#93c5fd', accent: '#38bdf8', subtitle: 'Reference Detective', variant: 3 },
    { title: 'Defense', status: 'Approved', statusBg: '#14332a', statusFg: '#6ee7b7', accent: '#34d399', subtitle: 'Ready to ship', variant: 4 },
];

const SCENE_PALETTES = [
    { fog: 0x05060a, ember: 0xff5a3c, accent: 0xff5a3c, cyan: 0x38bdf8, grid: 0xff5a3c, bloom: 0.48 },
    { fog: 0x07060f, ember: 0xa78bfa, accent: 0x8b5cf6, cyan: 0xc4b5fd, grid: 0x7c3aed, bloom: 0.52 },
    { fog: 0x0a0705, ember: 0xfb923c, accent: 0xf97316, cyan: 0xfdba74, grid: 0xfb923c, bloom: 0.5 },
    { fog: 0x040910, ember: 0x38bdf8, accent: 0x0ea5e9, cyan: 0x7dd3fc, grid: 0x38bdf8, bloom: 0.55 },
    { fog: 0x040a08, ember: 0x34d399, accent: 0x10b981, cyan: 0x6ee7b7, grid: 0x34d399, bloom: 0.46 },
];

const SCENE_POSES = [
    { cam: [1.35, 0.62, 9.2], look: [1.55, 0.2, 0], stage: [1.7, 0.1, 0], rotY: -0.48, fov: 32, hero: { x: 0.2, y: 0.22, z: 0.2, rotX: -0.12, rotY: -0.55, rotZ: 0.05, scale: 1.28 } },
    { cam: [-0.2, 0.05, 6.6], look: [1.0, -0.05, 0.15], stage: [1.0, -0.12, 0.4], rotY: 0.35, fov: 38, hero: { x: 0.65, y: -0.08, z: 0.05, rotX: -0.05, rotY: 0.42, rotZ: -0.04, scale: 1.05 } },
    { cam: [2.35, 0.85, 8.4], look: [1.55, 0.35, -0.2], stage: [1.9, 0.22, -0.2], rotY: -0.72, fov: 30, hero: { x: -0.2, y: 0.38, z: 0.5, rotX: -0.18, rotY: -0.82, rotZ: 0.08, scale: 1.34 } },
    { cam: [0.1, 0.42, 5.9], look: [0.9, 0.12, 0], stage: [0.9, 0.28, 0.6], rotY: 0.62, fov: 40, hero: { x: 0.95, y: -0.1, z: -0.3, rotX: 0.02, rotY: 0.55, rotZ: -0.06, scale: 1.12 } },
    { cam: [1.65, 0.95, 10.1], look: [1.7, 0.45, 0], stage: [1.8, 0.4, 0], rotY: -0.22, fov: 28, hero: { x: 0.08, y: 0.52, z: 0.18, rotX: -0.16, rotY: -0.38, rotZ: 0.03, scale: 1.4 } },
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
    const mat = new THREE.SpriteMaterial({ map, transparent: true, blending: THREE.AdditiveBlending, depthWrite: false });
    const sprite = new THREE.Sprite(mat);
    sprite.scale.set(size, size, 1);

    return sprite;
}

function makeHeroPaper(texture, accent) {
    const geometry = new THREE.BoxGeometry(1.72, 2.42, 0.028);
    const front = new THREE.MeshPhysicalMaterial({
        map: texture,
        roughness: 0.16,
        metalness: 0.1,
        clearcoat: 0.8,
        clearcoatRoughness: 0.18,
        emissive: 0x10121a,
        emissiveIntensity: 0.18,
    });
    const edge = new THREE.MeshPhysicalMaterial({ color: 0x171a24, roughness: 0.3, metalness: 0.6, clearcoat: 0.5 });
    const mesh = new THREE.Mesh(geometry, [edge, edge, edge, edge, front, edge]);
    mesh.castShadow = true;
    mesh.receiveShadow = true;

    const outline = new THREE.LineSegments(
        new THREE.EdgesGeometry(geometry, 24),
        new THREE.LineBasicMaterial({ color: accent, transparent: true, opacity: 0.55, blending: THREE.AdditiveBlending }),
    );
    outline.scale.setScalar(1.005);

    const group = new THREE.Group();
    group.add(mesh, outline);

    return { group, frontMaterial: front };
}

function makeGhostPaper(texture, scale = 0.5) {
    return new THREE.Mesh(
        new THREE.BoxGeometry(1.72 * scale, 2.42 * scale, 0.016),
        new THREE.MeshPhysicalMaterial({
            map: texture,
            roughness: 0.45,
            metalness: 0.08,
            transparent: true,
            opacity: 0.16,
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
            emissiveIntensity: 0.55,
            roughness: 0.08,
            metalness: 0.7,
            clearcoat: 1,
            transparent: true,
            opacity: 0.9,
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
        new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.14 }),
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
    renderer.setClearColor(0x000000, 0);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.32;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(SCENE_PALETTES[0].fog, 0.038);

    const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 60);
    const stage = new THREE.Group();
    scene.add(stage);

    const textures = SCENE_TEXTURES.map((def) => makePaperTexture(def));
    const heroes = textures.map((tex, i) => {
        const { group } = makeHeroPaper(tex, SCENE_TEXTURES[i].accent);
        group.visible = false;
        stage.add(group);

        return { mesh: group };
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

    const orbA = glowSprite('rgba(255,90,60,0.65)', 6.2);
    const orbB = glowSprite('rgba(56,189,248,0.5)', 5.0);
    const orbC = glowSprite('rgba(167,139,250,0.4)', 4.0);
    [orbA, orbB, orbC].forEach((orb) => orb && scene.add(orb));

    const emberGlow = glowSprite('rgba(255,90,60,0.7)', 3.0);
    if (emberGlow) {
        stage.add(emberGlow);
    }

    const badge = new THREE.Mesh(
        new THREE.SphereGeometry(0.18, 48, 48),
        new THREE.MeshPhysicalMaterial({
            color: 0xff5a3c,
            emissive: 0xff3b1a,
            emissiveIntensity: 1.35,
            roughness: 0.08,
            metalness: 0.6,
            clearcoat: 1,
        }),
    );
    stage.add(badge);

    const rings = [0.7, 0.95].map((radius, i) => {
        const ring = new THREE.Mesh(
            new THREE.TorusGeometry(radius, 0.012, 12, 100),
            new THREE.MeshPhysicalMaterial({
                color: 0x7dd3fc,
                emissive: 0x38bdf8,
                emissiveIntensity: 0.9,
                roughness: 0.12,
                metalness: 0.8,
                transparent: true,
                opacity: 0.42 - i * 0.12,
            }),
        );
        ring.rotation.x = Math.PI / 2.15 + i * 0.1;
        stage.add(ring);

        return ring;
    });

    const glassA = new THREE.Mesh(
        new THREE.PlaneGeometry(1.35, 0.78),
        new THREE.MeshPhysicalMaterial({
            color: 0x7dd3fc,
            transparent: true,
            opacity: 0.1,
            roughness: 0.04,
            metalness: 0.15,
            clearcoat: 1,
            side: THREE.DoubleSide,
            depthWrite: false,
        }),
    );
    const glassB = new THREE.Mesh(
        new THREE.PlaneGeometry(0.7, 1.35),
        new THREE.MeshPhysicalMaterial({
            color: 0xff5a3c,
            transparent: true,
            opacity: 0.1,
            roughness: 0.04,
            metalness: 0.15,
            clearcoat: 1,
            side: THREE.DoubleSide,
            depthWrite: false,
        }),
    );
    stage.add(glassA, glassB);

    const grid = makeGrid(24, 48, 0xff5a3c);
    grid.position.set(1.5, -1.95, 0);
    scene.add(grid);

    const dustCount = 420;
    const dustPositions = new Float32Array(dustCount * 3);
    for (let i = 0; i < dustCount; i += 1) {
        dustPositions[i * 3] = (Math.random() - 0.3) * 18;
        dustPositions[i * 3 + 1] = (Math.random() - 0.5) * 11;
        dustPositions[i * 3 + 2] = (Math.random() - 0.5) * 12;
    }
    const dust = new THREE.Points(
        new THREE.BufferGeometry().setAttribute('position', new THREE.BufferAttribute(dustPositions, 3)),
        new THREE.PointsMaterial({
            color: 0xffd0c0,
            size: 0.024,
            transparent: true,
            opacity: 0.3,
            depthWrite: false,
            blending: THREE.AdditiveBlending,
        }),
    );
    scene.add(dust);

    scene.add(new THREE.AmbientLight(0x6b7280, 0.32));
    const key = new THREE.DirectionalLight(0xfff1e5, 1.9);
    key.position.set(5.5, 8.5, 6);
    key.castShadow = true;
    scene.add(key);
    const emberLight = new THREE.PointLight(0xff5a3c, 34, 18, 2);
    emberLight.position.set(-3, 1.8, 2.8);
    scene.add(emberLight);
    const cyanLight = new THREE.PointLight(0x38bdf8, 24, 15, 2);
    cyanLight.position.set(4, 0, 2.2);
    scene.add(cyanLight);
    const rimLight = new THREE.PointLight(0xa78bfa, 16, 13, 2);
    rimLight.position.set(0.4, 2.8, -3.2);
    scene.add(rimLight);

    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(11, 64),
        new THREE.MeshStandardMaterial({ color: 0x06080e, roughness: 0.9, metalness: 0.4 }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.set(1.5, -1.96, 0);
    floor.receiveShadow = true;
    scene.add(floor);

    const composer = new EffectComposer(renderer);
    composer.addPass(new RenderPass(scene, camera));
    const bloom = new UnrealBloomPass(new THREE.Vector2(1, 1), 0.48, 0.4, 0.86);
    composer.addPass(bloom);

    const mouse = { x: 0.1, y: 0 };
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
        scrollVelocity = damp(scrollVelocity, (scrollY - lastScroll) * 0.04, 8, dt);
        lastScroll = scrollY;

        const scrollProgress = readScrollProgress();
        const targetFloat = progressToSceneFloat(scrollProgress);
        sceneFloat = damp(sceneFloat, targetFloat, 4.2, dt);

        const indexA = Math.floor(sceneFloat);
        const indexB = Math.min(SCENE_COUNT - 1, indexA + 1);
        const blend = easeInOutCubic(sceneFloat - indexA);
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
                    mat.transparent = opacity < 1;
                    mat.opacity = opacity;
                });
            });
        });

        ghosts.forEach((ghost, i) => {
            const active = i === indexA || i === indexB;
            ghost.visible = active;
            let opacity = 0;
            if (i === indexA) {
                opacity = (1 - blend) * 0.12;
            }
            if (i === indexB) {
                opacity = blend * 0.12;
            }
            ghost.material.opacity = opacity;
            const orbit = t * 0.18 + i * 1.35;
            ghost.position.set(
                Math.sin(orbit) * 3.6 - 0.15,
                0.5 + Math.cos(orbit * 0.65) * 0.55,
                Math.cos(orbit) * 1.7 - 2.4,
            );
            ghost.rotation.set(-0.18, orbit * 0.28, 0.08);
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
        const floatY = Math.sin(t * 0.85) * 0.12;
        const scrollPulse = easeOutExpo(Math.min(1, Math.abs(scrollVelocity) * 2)) * 0.06;

        heroes.forEach((hero, i) => {
            if (i !== indexA && i !== indexB) {
                return;
            }
            const transitionOffset = i === indexA ? -blend : 1 - blend;
            const flip = Math.sin(transitionOffset * Math.PI) * 0.55;
            hero.mesh.position.set(
                heroPose.x + transitionOffset * 0.55,
                heroPose.y + floatY + Math.abs(transitionOffset) * 0.15,
                heroPose.z - Math.abs(transitionOffset) * 0.45,
            );
            hero.mesh.rotation.set(
                heroPose.rotX + flip * 0.35 + Math.sin(t * 0.4) * 0.04,
                heroPose.rotY + Math.sin(t * 0.35) * 0.07 + transitionOffset * 0.55,
                heroPose.rotZ + transitionOffset * 0.08,
            );
            hero.mesh.scale.setScalar(heroPose.scale + scrollPulse - Math.abs(transitionOffset) * 0.08);
        });

        const cam = lerp3(poseA.cam, poseB.cam, blend);
        const look = lerp3(poseA.look, poseB.look, blend);
        const stagePos = lerp3(poseA.stage, poseB.stage, blend);
        const stageRotY = lerp(poseA.rotY, poseB.rotY, blend);
        const targetFov = lerp(poseA.fov, poseB.fov, blend) + Math.abs(scrollVelocity) * 4;

        stage.position.set(stagePos[0], stagePos[1], stagePos[2]);
        stage.rotation.y = damp(stage.rotation.y, stageRotY + mouse.x * 0.38, 5.5, dt);
        stage.rotation.x = damp(stage.rotation.x, mouse.y * 0.16 + scrollVelocity * 0.08, 5.5, dt);

        camera.position.x = damp(camera.position.x, cam[0] + mouse.x * 0.5, 4.8, dt);
        camera.position.y = damp(camera.position.y, cam[1] + mouse.y * 0.28, 4.8, dt);
        camera.position.z = damp(camera.position.z, cam[2], 4.8, dt);
        camera.fov = damp(camera.fov, targetFov, 4.2, dt);
        camera.updateProjectionMatrix();
        camera.lookAt(look[0], look[1], look[2]);

        badge.position.set(heroPose.x + 1.2, heroPose.y + 1.1 + Math.sin(t * 1.1) * 0.2, heroPose.z + 0.7);
        badge.material.emissive.copy(palette.ember);
        if (emberGlow) {
            emberGlow.position.copy(badge.position);
            emberGlow.material.color.copy(palette.ember);
        }

        crystal.position.set(heroPose.x - 1.55, heroPose.y + 0.9 + Math.sin(t * 0.9) * 0.2, heroPose.z + 0.35);
        crystal.rotation.set(t * 0.55, t * 0.72, t * 0.25);

        rings.forEach((ring, i) => {
            ring.position.set(heroPose.x - 1.4 + i * 0.1, heroPose.y + 0.65, heroPose.z + 0.2);
            ring.rotation.z = t * (0.32 + i * 0.14);
            ring.material.emissive.copy(palette.cyan);
            ring.material.color.copy(palette.cyan);
        });

        const angleA = t * 0.14;
        glassA.position.set(heroPose.x + Math.sin(angleA) * 2.15, heroPose.y + 0.2, heroPose.z + Math.cos(angleA) * 1.0 - 0.4);
        glassA.rotation.set(-0.28, angleA, 0.1);
        glassA.material.color.copy(palette.cyan);

        const angleB = t * 0.14 + Math.PI;
        glassB.position.set(heroPose.x + Math.sin(angleB) * 2.3, heroPose.y + 0.7, heroPose.z + Math.cos(angleB) * 1.05 - 0.4);
        glassB.rotation.set(-0.22, angleB, 0.08);
        glassB.material.color.copy(palette.accent);

        if (orbA) {
            orbA.position.set(-3.8 + mouse.x * 0.4, 2.4 + mouse.y * 0.4, -4.2);
            orbA.material.color.copy(palette.ember);
        }
        if (orbB) {
            orbB.position.set(5.2 + mouse.x * 0.3, -0.6, -3.2);
            orbB.material.color.copy(palette.cyan);
        }
        if (orbC) {
            orbC.position.set(1.2, 3.8, -5.2);
            orbC.material.color.copy(palette.accent);
        }

        dust.rotation.y = t * 0.035;
        grid.rotation.y = t * 0.012;

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
