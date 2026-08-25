import * as THREE from 'three';

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

function ease(t) {
    return t * t * (3 - 2 * t);
}

function makePaperTexture({ title, status, statusBg, statusFg, accent, subtitle }) {
    const canvas = document.createElement('canvas');
    canvas.width = 768;
    canvas.height = 1080;
    const ctx = canvas.getContext('2d');
    if (! ctx) {
        return null;
    }

    const paper = ctx.createLinearGradient(0, 0, 0, 1080);
    paper.addColorStop(0, '#fffaf3');
    paper.addColorStop(1, '#efe4d4');
    ctx.fillStyle = paper;
    ctx.fillRect(0, 0, 768, 1080);

    ctx.fillStyle = accent;
    ctx.fillRect(0, 0, 18, 1080);
    ctx.fillStyle = '#1a1c22';
    ctx.font = '700 54px Georgia, serif';
    ctx.fillText(title, 56, 150);
    ctx.fillStyle = '#9a8f82';
    ctx.font = '22px sans-serif';
    ctx.fillText(subtitle || 'CAST  ·  manuscript', 56, 196);

    ctx.fillStyle = '#cfc3b4';
    for (let i = 0; i < 18; i += 1) {
        ctx.fillRect(56, 250 + i * 36, 420 + ((i * 73) % 180), 10);
    }

    ctx.fillStyle = statusBg;
    ctx.fillRect(56, 960, 240, 52);
    ctx.fillStyle = statusFg;
    ctx.font = '700 22px sans-serif';
    ctx.fillText(status, 74, 994);

    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.anisotropy = 8;

    return texture;
}

const SCENE_TEXTURES = [
    { title: 'Chapter 1', status: 'For review', statusBg: '#d7ecf6', statusFg: '#16384a', accent: '#e24b32', subtitle: 'Introduction' },
    { title: 'Proposal', status: 'Submitted', statusBg: '#ece7de', statusFg: '#3b372f', accent: '#7c3aed', subtitle: 'Upload · Drive' },
    { title: 'Full draft', status: 'Needs revision', statusBg: '#ffe1cc', statusFg: '#6a3212', accent: '#ff7a55', subtitle: 'Adviser notes' },
    { title: 'References', status: 'Scanning…', statusBg: '#efe6f6', statusFg: '#5b21b6', accent: '#7c3aed', subtitle: 'Reference Detective' },
    { title: 'Defense', status: 'Approved', statusBg: '#d8f0d8', statusFg: '#1b3d24', accent: '#38bdf8', subtitle: 'Ready to ship' },
];

const SCENE_POSES = [
    { cam: [1.1, 0.4, 8.4], look: [1.3, 0.2, 0], stage: [1.5, 0.05, 0], rotY: -0.35, hero: { x: 0.2, y: 0.15, z: 0.2, rotY: -0.45, scale: 1.15 } },
    { cam: [0.4, 0.05, 7.2], look: [1.0, 0, 0], stage: [1.2, -0.1, 0.3], rotY: 0.15, hero: { x: 0.5, y: 0, z: 0, rotY: 0.2, scale: 1.05 } },
    { cam: [1.8, 0.55, 7.8], look: [1.4, 0.25, -0.2], stage: [1.7, 0.15, -0.2], rotY: -0.55, hero: { x: -0.1, y: 0.25, z: 0.4, rotY: -0.65, scale: 1.2 } },
    { cam: [0.2, 0.3, 6.8], look: [0.8, 0.1, 0], stage: [1.0, 0.2, 0.5], rotY: 0.45, hero: { x: 0.8, y: -0.05, z: -0.3, rotY: 0.35, scale: 1.08 } },
    { cam: [1.4, 0.65, 9.2], look: [1.5, 0.35, 0], stage: [1.6, 0.3, 0], rotY: -0.2, hero: { x: 0, y: 0.4, z: 0.1, rotY: -0.25, scale: 1.25 } },
];

function glowSprite(color, size) {
    const c = document.createElement('canvas');
    c.width = 128;
    c.height = 128;
    const ctx = c.getContext('2d');
    if (! ctx) {
        return null;
    }
    const g = ctx.createRadialGradient(64, 64, 4, 64, 64, 64);
    g.addColorStop(0, color);
    g.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, 128, 128);
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
        roughness: 0.28,
        metalness: 0.02,
        clearcoat: 0.35,
        clearcoatRoughness: 0.4,
    });
    const cream = new THREE.MeshStandardMaterial({ color: 0xe7dccb, roughness: 0.5, metalness: 0.04 });
    const mesh = new THREE.Mesh(geometry, [cream, cream, cream, cream, front, cream]);
    mesh.castShadow = true;
    mesh.receiveShadow = true;

    return { group: mesh, frontMaterial: front };
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
    renderer.toneMappingExposure = 1.28;
    renderer.shadowMap.enabled = true;

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x05060a, 0.038);

    const camera = new THREE.PerspectiveCamera(36, 1, 0.1, 50);
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

    const emberGlow = glowSprite('rgba(255,90,60,0.55)', 2.4);
    if (emberGlow) {
        stage.add(emberGlow);
    }
    const cyanGlow = glowSprite('rgba(56,189,248,0.4)', 2.1);
    if (cyanGlow) {
        stage.add(cyanGlow);
    }

    const badge = new THREE.Mesh(
        new THREE.SphereGeometry(0.18, 48, 48),
        new THREE.MeshPhysicalMaterial({
            color: 0xff5a3c,
            emissive: 0xff3b1a,
            emissiveIntensity: 0.9,
            roughness: 0.15,
            metalness: 0.45,
            clearcoat: 1,
        }),
    );
    stage.add(badge);

    const ring = new THREE.Mesh(
        new THREE.TorusGeometry(0.52, 0.016, 16, 80),
        new THREE.MeshPhysicalMaterial({
            color: 0x7dd3fc,
            emissive: 0x38bdf8,
            emissiveIntensity: 0.7,
            roughness: 0.2,
            metalness: 0.7,
        }),
    );
    ring.rotation.x = Math.PI / 2.3;
    stage.add(ring);

    const dust = new THREE.Points(
        new THREE.BufferGeometry().setAttribute(
            'position',
            new THREE.BufferAttribute(
                (() => {
                    const positions = new Float32Array(320 * 3);
                    for (let i = 0; i < 320; i += 1) {
                        positions[i * 3] = (Math.random() - 0.35) * 14;
                        positions[i * 3 + 1] = (Math.random() - 0.5) * 8;
                        positions[i * 3 + 2] = (Math.random() - 0.5) * 8;
                    }

                    return positions;
                })(),
                3,
            ),
        ),
        new THREE.PointsMaterial({ color: 0xffd0c0, size: 0.022, transparent: true, opacity: 0.38, depthWrite: false }),
    );
    scene.add(dust);

    scene.add(new THREE.AmbientLight(0x8b93a7, 0.42));
    const key = new THREE.DirectionalLight(0xfff3e8, 1.7);
    key.position.set(5, 8, 6);
    key.castShadow = true;
    scene.add(key);
    scene.add(new THREE.PointLight(0xff5a3c, 28, 14, 2).translateX(-2.6).translateY(1.4).translateZ(2.4));
    scene.add(new THREE.PointLight(0x38bdf8, 16, 12, 2).translateX(3.6).translateY(-0.2).translateZ(1.8));

    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(9, 64),
        new THREE.MeshStandardMaterial({ color: 0x0b0d12, roughness: 0.85, metalness: 0.25 }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.set(1.4, -1.85, 0);
    floor.receiveShadow = true;
    scene.add(floor);

    const mouse = { x: 0.15, y: 0 };
    const onMove = (event) => {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -((event.clientY / window.innerHeight) * 2 - 1);
    };
    window.addEventListener('pointermove', onMove, { passive: true });

    const sections = [...document.querySelectorAll('[data-landing-scene]')];
    const dots = [...document.querySelectorAll('.landing-dot')];
    const scrollHint = document.querySelector('.landing-scroll-hint');

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
    };
    resize();
    const observer = new ResizeObserver(resize);
    observer.observe(canvas);

    let frame = 0;
    const tick = (time) => {
        frame = requestAnimationFrame(tick);
        const t = time * 0.001;

        const targetFloat = progressToSceneFloat(readScrollProgress());
        sceneFloat += (targetFloat - sceneFloat) * 0.08;

        const indexA = Math.floor(sceneFloat);
        const indexB = Math.min(SCENE_COUNT - 1, indexA + 1);
        const blend = ease(sceneFloat - indexA);
        const poseA = SCENE_POSES[indexA];
        const poseB = SCENE_POSES[indexB];

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

        const heroIndex = blend > 0.5 ? indexB : indexA;
        const hero = heroes[heroIndex].mesh;
        const heroPose = {
            x: lerp(poseA.hero.x, poseB.hero.x, blend),
            y: lerp(poseA.hero.y, poseB.hero.y, blend),
            z: lerp(poseA.hero.z, poseB.hero.z, blend),
            rotY: lerp(poseA.hero.rotY, poseB.hero.rotY, blend),
            scale: lerp(poseA.hero.scale, poseB.hero.scale, blend),
        };
        hero.position.set(heroPose.x, heroPose.y + Math.sin(t * 0.9) * 0.12, heroPose.z);
        hero.rotation.set(-0.12, heroPose.rotY + Math.sin(t * 0.4) * 0.08, 0.04);
        hero.scale.setScalar(heroPose.scale);

        const cam = lerp3(poseA.cam, poseB.cam, blend);
        const look = lerp3(poseA.look, poseB.look, blend);
        const stagePos = lerp3(poseA.stage, poseB.stage, blend);
        const stageRotY = lerp(poseA.rotY, poseB.rotY, blend);

        stage.position.set(stagePos[0], stagePos[1], stagePos[2]);
        stage.rotation.y += ((stageRotY + mouse.x * 0.25) - stage.rotation.y) * 0.06;
        stage.rotation.x += ((mouse.y * 0.12) - stage.rotation.x) * 0.06;

        camera.position.x += ((cam[0] + mouse.x * 0.35) - camera.position.x) * 0.06;
        camera.position.y += ((cam[1] + mouse.y * 0.2) - camera.position.y) * 0.06;
        camera.position.z += (cam[2] - camera.position.z) * 0.06;
        camera.lookAt(look[0], look[1], look[2]);

        badge.position.set(heroPose.x + 1.1, heroPose.y + 1.0 + Math.sin(t * 1.1) * 0.15, heroPose.z + 0.6);
        if (emberGlow) {
            emberGlow.position.copy(badge.position);
        }
        ring.position.set(heroPose.x - 1.3, heroPose.y + 0.75, heroPose.z + 0.2);
        ring.rotation.z = t * 0.42;
        dust.rotation.y = t * 0.03;

        sections.forEach((section, i) => {
            const rect = section.getBoundingClientRect();
            const center = rect.top + rect.height / 2;
            const visible = Math.abs(center - window.innerHeight / 2) < window.innerHeight * 0.42;
            section.querySelector('.landing-scene-copy')?.classList.toggle('is-visible', visible);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === indexA || (i === indexB && blend > 0.5));
        });

        if (scrollHint) {
            scrollHint.style.opacity = window.scrollY > 80 ? '0' : '1';
        }

        renderer.render(scene, camera);
    };
    frame = requestAnimationFrame(tick);

    return () => {
        cancelAnimationFrame(frame);
        observer.disconnect();
        window.removeEventListener('pointermove', onMove);
        renderer.dispose();
        textures.forEach((map) => map?.dispose());
    };
}

const canvas = document.getElementById('cast-3d');
if (canvas) {
    mountLandingScene(canvas);
}
