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
    const ca = new THREE.Color(a);
    const cb = new THREE.Color(b);

    return ca.lerp(cb, t);
}

function ease(t) {
    return t * t * (3 - 2 * t);
}

function easeOutExpo(t) {
    return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
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
    bg.addColorStop(0, '#12151f');
    bg.addColorStop(0.45, '#0c0e14');
    bg.addColorStop(1, '#06070b');
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, 768, 1080);

    ctx.globalAlpha = 0.07;
    for (let y = 0; y < 1080; y += 28) {
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, y, 768, 1);
    }
    ctx.globalAlpha = 1;

    const glow = ctx.createRadialGradient(620, 180, 20, 620, 180, 320);
    glow.addColorStop(0, accent + '55');
    glow.addColorStop(1, 'transparent');
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, 768, 1080);

    ctx.fillStyle = accent;
    ctx.fillRect(0, 0, 6, 1080);
    ctx.fillRect(0, 0, 768, 4);

    ctx.fillStyle = '#f4f1ec';
    ctx.font = '700 52px Georgia, serif';
    ctx.fillText(title, 48, 148);
    ctx.fillStyle = accent;
    ctx.font = '600 20px sans-serif';
    ctx.fillText(subtitle || 'CAST · manuscript', 48, 188);

    const lineColors = ['#2a3040', '#242a38', '#1e2432'];
    for (let i = 0; i < 16; i += 1) {
        ctx.fillStyle = lineColors[(i + variant) % 3];
        ctx.fillRect(48, 240 + i * 38, 380 + ((i * 97 + variant * 41) % 220), 8);
    }

    if (variant % 2 === 0) {
        ctx.strokeStyle = accent + '66';
        ctx.lineWidth = 2;
        ctx.strokeRect(48, 720, 320, 140);
        ctx.fillStyle = accent + '18';
        ctx.fillRect(48, 720, 320, 140);
        ctx.fillStyle = '#c8cdd8';
        ctx.font = '500 18px sans-serif';
        ctx.fillText('Reference scan', 64, 758);
        ctx.fillStyle = '#6b7280';
        ctx.font = '400 15px sans-serif';
        ctx.fillText('3 unused · 1 missing', 64, 788);
    }

    ctx.fillStyle = statusBg;
    ctx.beginPath();
    ctx.roundRect(48, 980, 260, 52, 26);
    ctx.fill();
    ctx.fillStyle = statusFg;
    ctx.font = '700 20px sans-serif';
    ctx.fillText(status, 68, 1014);

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
    { fog: 0x05060a, key: 0xfff0e8, ember: 0xff5a3c, accent: 0xff5a3c, cyan: 0x38bdf8, grid: 0xff5a3c },
    { fog: 0x06050c, key: 0xf0ecff, ember: 0xa78bfa, accent: 0x7c3aed, cyan: 0xc4b5fd, grid: 0x7c3aed },
    { fog: 0x0a0605, key: 0xfff4eb, ember: 0xfb923c, accent: 0xf97316, cyan: 0xfdba74, grid: 0xfb923c },
    { fog: 0x040810, key: 0xe8f4ff, ember: 0x38bdf8, accent: 0x0ea5e9, cyan: 0x7dd3fc, grid: 0x38bdf8 },
    { fog: 0x040a08, key: 0xecfff6, ember: 0x34d399, accent: 0x10b981, cyan: 0x6ee7b7, grid: 0x34d399 },
];

const SCENE_POSES = [
    { cam: [1.2, 0.55, 8.8], look: [1.4, 0.15, 0], stage: [1.6, 0.08, 0], rotY: -0.42, hero: { x: 0.15, y: 0.2, z: 0.15, rotY: -0.52, scale: 1.22 } },
    { cam: [0.15, 0.1, 7.0], look: [1.1, 0, 0.1], stage: [1.1, -0.08, 0.35], rotY: 0.22, hero: { x: 0.55, y: -0.05, z: 0, rotY: 0.28, scale: 1.08 } },
    { cam: [2.0, 0.65, 8.2], look: [1.5, 0.3, -0.15], stage: [1.8, 0.18, -0.15], rotY: -0.62, hero: { x: -0.15, y: 0.32, z: 0.45, rotY: -0.72, scale: 1.28 } },
    { cam: [0.05, 0.35, 6.5], look: [0.85, 0.08, 0], stage: [0.95, 0.22, 0.55], rotY: 0.52, hero: { x: 0.85, y: -0.08, z: -0.25, rotY: 0.42, scale: 1.12 } },
    { cam: [1.5, 0.75, 9.6], look: [1.6, 0.4, 0], stage: [1.7, 0.35, 0], rotY: -0.18, hero: { x: 0.05, y: 0.45, z: 0.15, rotY: -0.32, scale: 1.32 } },
];

function glowSprite(color, size) {
    const c = document.createElement('canvas');
    c.width = 256;
    c.height = 256;
    const ctx = c.getContext('2d');
    if (! ctx) {
        return null;
    }
    const g = ctx.createRadialGradient(128, 128, 8, 128, 128, 128);
    g.addColorStop(0, color);
    g.addColorStop(0.45, color.replace(/[\d.]+\)$/, '0.25)'));
    g.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, 256, 256);
    const map = new THREE.CanvasTexture(c);
    const mat = new THREE.SpriteMaterial({
        map,
        transparent: true,
        blending: THREE.AdditiveBlending,
        depthWrite: false,
    });
    const sprite = new THREE.Sprite(mat);
    sprite.scale.set(size, size, 1);

    return sprite;
}

function makeHeroPaper(texture) {
    const geometry = new THREE.BoxGeometry(1.72, 2.42, 0.028);
    const front = new THREE.MeshPhysicalMaterial({
        map: texture,
        roughness: 0.18,
        metalness: 0.08,
        clearcoat: 0.65,
        clearcoatRoughness: 0.22,
        emissive: 0x111318,
        emissiveIntensity: 0.15,
    });
    const edge = new THREE.MeshPhysicalMaterial({
        color: 0x1a1d28,
        roughness: 0.35,
        metalness: 0.55,
        clearcoat: 0.4,
    });
    const mesh = new THREE.Mesh(geometry, [edge, edge, edge, edge, front, edge]);
    mesh.castShadow = true;
    mesh.receiveShadow = true;

    return { group: mesh, frontMaterial: front };
}

function makeGhostPaper(texture, scale = 0.55) {
    const geometry = new THREE.BoxGeometry(1.72 * scale, 2.42 * scale, 0.018);
    const mat = new THREE.MeshPhysicalMaterial({
        map: texture,
        roughness: 0.4,
        metalness: 0.1,
        transparent: true,
        opacity: 0.22,
        depthWrite: false,
    });
    const mesh = new THREE.Mesh(geometry, mat);

    return mesh;
}

function makeGlassPanel(w, h, color) {
    return new THREE.Mesh(
        new THREE.PlaneGeometry(w, h),
        new THREE.MeshPhysicalMaterial({
            color,
            transparent: true,
            opacity: 0.12,
            roughness: 0.05,
            metalness: 0.1,
            clearcoat: 1,
            side: THREE.DoubleSide,
            depthWrite: false,
        }),
    );
}

function makeGrid(size, divisions, color) {
    const step = size / divisions;
    const half = size / 2;
    const vertices = [];
    for (let i = 0; i <= divisions; i += 1) {
        const pos = -half + i * step;
        vertices.push(-half, 0, pos, half, 0, pos);
        vertices.push(pos, 0, -half, pos, 0, half);
    }
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));

    return new THREE.LineSegments(
        geometry,
        new THREE.LineBasicMaterial({ color, transparent: true, opacity: 0.35 }),
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
    renderer.toneMappingExposure = 1.35;
    renderer.shadowMap.enabled = true;

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(SCENE_PALETTES[0].fog, 0.042);

    const camera = new THREE.PerspectiveCamera(34, 1, 0.1, 50);
    const stage = new THREE.Group();
    scene.add(stage);

    const textures = SCENE_TEXTURES.map((def) => makePaperTexture(def));
    const heroes = textures.map((tex) => {
        const { group, frontMaterial } = makeHeroPaper(tex);
        group.visible = false;
        stage.add(group);

        return { mesh: group, frontMaterial };
    });
    heroes[0].mesh.visible = true;

    const ghosts = textures.map((tex, i) => {
        const ghost = makeGhostPaper(tex, 0.48 + (i % 3) * 0.06);
        ghost.visible = false;
        stage.add(ghost);

        return ghost;
    });

    const orbA = glowSprite('rgba(255,90,60,0.7)', 5.5);
    const orbB = glowSprite('rgba(56,189,248,0.55)', 4.8);
    const orbC = glowSprite('rgba(167,139,250,0.45)', 3.6);
    [orbA, orbB, orbC].forEach((orb) => {
        if (orb) {
            scene.add(orb);
        }
    });

    const emberGlow = glowSprite('rgba(255,90,60,0.65)', 2.8);
    if (emberGlow) {
        stage.add(emberGlow);
    }

    const badge = new THREE.Mesh(
        new THREE.SphereGeometry(0.2, 48, 48),
        new THREE.MeshPhysicalMaterial({
            color: 0xff5a3c,
            emissive: 0xff3b1a,
            emissiveIntensity: 1.2,
            roughness: 0.1,
            metalness: 0.55,
            clearcoat: 1,
        }),
    );
    stage.add(badge);

    const rings = [0.58, 0.72, 0.86].map((radius, i) => {
        const ring = new THREE.Mesh(
            new THREE.TorusGeometry(radius, 0.014, 12, 96),
            new THREE.MeshPhysicalMaterial({
                color: 0x7dd3fc,
                emissive: 0x38bdf8,
                emissiveIntensity: 0.85,
                roughness: 0.15,
                metalness: 0.75,
                transparent: true,
                opacity: 0.55 - i * 0.12,
            }),
        );
        ring.rotation.x = Math.PI / 2.1 + i * 0.08;
        stage.add(ring);

        return ring;
    });

    const glassPanels = [
        makeGlassPanel(1.4, 0.9, 0x7dd3fc),
        makeGlassPanel(0.9, 1.6, 0xff5a3c),
        makeGlassPanel(1.1, 1.1, 0xa78bfa),
    ];
    glassPanels.forEach((panel) => stage.add(panel));

    const grid = makeGrid(22, 44, 0xff5a3c);
    grid.position.set(1.4, -1.92, 0);
    scene.add(grid);

    const dust = new THREE.Points(
        new THREE.BufferGeometry().setAttribute(
            'position',
            new THREE.BufferAttribute(
                (() => {
                    const positions = new Float32Array(480 * 3);
                    for (let i = 0; i < 480; i += 1) {
                        positions[i * 3] = (Math.random() - 0.35) * 16;
                        positions[i * 3 + 1] = (Math.random() - 0.5) * 10;
                        positions[i * 3 + 2] = (Math.random() - 0.5) * 10;
                    }

                    return positions;
                })(),
                3,
            ),
        ),
        new THREE.PointsMaterial({ color: 0xffd0c0, size: 0.028, transparent: true, opacity: 0.45, depthWrite: false, blending: THREE.AdditiveBlending }),
    );
    scene.add(dust);

    const ambient = new THREE.AmbientLight(0x6b7280, 0.35);
    scene.add(ambient);
    const key = new THREE.DirectionalLight(0xfff3e8, 1.85);
    key.position.set(5, 8, 6);
    key.castShadow = true;
    scene.add(key);
    const emberLight = new THREE.PointLight(0xff5a3c, 32, 16, 2);
    emberLight.position.set(-2.8, 1.6, 2.6);
    scene.add(emberLight);
    const cyanLight = new THREE.PointLight(0x38bdf8, 22, 14, 2);
    cyanLight.position.set(3.8, -0.1, 2.0);
    scene.add(cyanLight);
    const rimLight = new THREE.PointLight(0xa78bfa, 14, 12, 2);
    rimLight.position.set(0.5, 2.5, -3);
    scene.add(rimLight);

    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(10, 64),
        new THREE.MeshStandardMaterial({ color: 0x06080e, roughness: 0.92, metalness: 0.35 }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.set(1.4, -1.93, 0);
    floor.receiveShadow = true;
    scene.add(floor);

    const composer = new EffectComposer(renderer);
    composer.addPass(new RenderPass(scene, camera));
    const bloom = new UnrealBloomPass(new THREE.Vector2(1, 1), 0.55, 0.42, 0.88);
    composer.addPass(bloom);

    const mouse = { x: 0.12, y: 0 };
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
            const index = Number(dot.dataset.scrollTo);
            const target = sections[index];
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    let sceneFloat = 0;

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

        const scrollProgress = readScrollProgress();
        const targetFloat = progressToSceneFloat(scrollProgress);
        sceneFloat += (targetFloat - sceneFloat) * 0.1;

        const indexA = Math.floor(sceneFloat);
        const indexB = Math.min(SCENE_COUNT - 1, indexA + 1);
        const blend = ease(sceneFloat - indexA);
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
        scene.fog.color.copy(palette.fog);
        emberLight.color.copy(palette.ember);
        cyanLight.color.copy(palette.cyan);
        rimLight.color.copy(palette.accent);
        grid.material.color.copy(palette.grid);
        dust.material.color.copy(palette.accent);

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
                if (child.material) {
                    const mats = Array.isArray(child.material) ? child.material : [child.material];
                    mats.forEach((mat) => {
                        mat.transparent = opacity < 1;
                        mat.opacity = opacity;
                    });
                }
            });
        });

        ghosts.forEach((ghost, i) => {
            const active = i === indexA || i === indexB;
            ghost.visible = active;
            let opacity = 0;
            if (i === indexA) {
                opacity = (1 - blend) * 0.28;
            }
            if (i === indexB) {
                opacity = blend * 0.28;
            }
            ghost.material.opacity = opacity;
            const orbit = t * 0.35 + i * 1.4;
            ghost.position.set(
                Math.sin(orbit) * 2.8 - 0.5,
                0.4 + Math.cos(orbit * 0.7) * 0.5,
                Math.cos(orbit) * 1.2 - 1.5,
            );
            ghost.rotation.set(-0.2, orbit * 0.4, 0.15);
        });

        const heroIndex = blend > 0.5 ? indexB : indexA;
        const hero = heroes[heroIndex].mesh;
        const heroPose = {
            x: lerp(poseA.hero.x, poseB.hero.x, blend),
            y: lerp(poseA.hero.y, poseB.hero.y, blend),
            z: lerp(poseA.hero.z, poseB.hero.z, blend),
            rotY: lerp(poseA.hero.rotY, poseB.hero.rotY, blend),
            scale: lerp(poseA.hero.scale, poseB.hero.scale, blend),
        };
        const floatY = Math.sin(t * 0.95) * 0.14;
        const scrollPulse = easeOutExpo(Math.abs(targetFloat - sceneFloat)) * 0.08;
        hero.position.set(heroPose.x, heroPose.y + floatY, heroPose.z);
        hero.rotation.set(-0.1, heroPose.rotY + Math.sin(t * 0.45) * 0.1, 0.05);
        hero.scale.setScalar(heroPose.scale + scrollPulse);

        const cam = lerp3(poseA.cam, poseB.cam, blend);
        const look = lerp3(poseA.look, poseB.look, blend);
        const stagePos = lerp3(poseA.stage, poseB.stage, blend);
        const stageRotY = lerp(poseA.rotY, poseB.rotY, blend);

        stage.position.set(stagePos[0], stagePos[1], stagePos[2]);
        stage.rotation.y += ((stageRotY + mouse.x * 0.32) - stage.rotation.y) * 0.07;
        stage.rotation.x += ((mouse.y * 0.14) - stage.rotation.x) * 0.07;

        camera.position.x += ((cam[0] + mouse.x * 0.42) - camera.position.x) * 0.07;
        camera.position.y += ((cam[1] + mouse.y * 0.24) - camera.position.y) * 0.07;
        camera.position.z += (cam[2] - camera.position.z) * 0.07;
        camera.lookAt(look[0], look[1], look[2]);

        badge.position.set(heroPose.x + 1.15, heroPose.y + 1.05 + Math.sin(t * 1.15) * 0.18, heroPose.z + 0.65);
        badge.material.emissive.copy(palette.ember);
        if (emberGlow) {
            emberGlow.position.copy(badge.position);
            emberGlow.material.color.copy(palette.ember);
        }

        rings.forEach((ring, i) => {
            ring.position.set(heroPose.x - 1.35 + i * 0.12, heroPose.y + 0.7, heroPose.z + 0.25);
            ring.rotation.z = t * (0.38 + i * 0.12);
            ring.material.emissive.copy(palette.cyan);
            ring.material.color.copy(palette.cyan);
        });

        glassPanels.forEach((panel, i) => {
            const angle = t * 0.25 + i * 2.1;
            panel.position.set(
                heroPose.x + Math.sin(angle) * (1.8 + i * 0.3),
                heroPose.y + 0.2 + i * 0.35,
                heroPose.z + Math.cos(angle) * 0.8,
            );
            panel.rotation.set(-0.4, angle, 0.2);
            panel.material.color.copy(i % 2 === 0 ? palette.accent : palette.cyan);
        });

        if (orbA) {
            orbA.position.set(-3.5 + mouse.x, 2.2 + mouse.y * 0.5, -4);
            orbA.material.color.copy(palette.ember);
        }
        if (orbB) {
            orbB.position.set(5 + mouse.x * 0.5, -0.5, -3);
            orbB.material.color.copy(palette.cyan);
        }
        if (orbC) {
            orbC.position.set(1.5, 3.5, -5);
            orbC.material.color.copy(palette.accent);
        }

        dust.rotation.y = t * 0.04;
        grid.rotation.y = t * 0.015;

        sections.forEach((section, i) => {
            const rect = section.getBoundingClientRect();
            const center = rect.top + rect.height / 2;
            const visible = Math.abs(center - window.innerHeight / 2) < window.innerHeight * 0.4;
            section.querySelector('.landing-scene-copy')?.classList.toggle('is-visible', visible);
            section.dataset.active = visible ? 'true' : 'false';
            if (visible && sceneCounter) {
                sceneCounter.textContent = String(i + 1).padStart(2, '0');
            }
        });

        document.body.dataset.landingScene = String(blend > 0.5 ? indexB : indexA);

        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === indexA || (i === indexB && blend > 0.5));
        });

        if (scrollHint) {
            scrollHint.style.opacity = window.scrollY > 80 ? '0' : '1';
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
