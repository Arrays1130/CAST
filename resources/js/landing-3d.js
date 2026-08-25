import * as THREE from 'three';

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function makePaperTexture({ title, status, statusBg, statusFg, accent }) {
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
    ctx.fillText('CAST  ·  manuscript', 56, 196);

    ctx.fillStyle = '#cfc3b4';
    for (let i = 0; i < 18; i += 1) {
        const w = 420 + ((i * 73) % 180);
        ctx.fillRect(56, 250 + i * 36, w, 10);
    }

    ctx.fillStyle = statusBg;
    ctx.fillRect(56, 960, 210, 52);
    ctx.fillStyle = statusFg;
    ctx.font = '700 22px sans-serif';
    ctx.fillText(status, 74, 994);

    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.anisotropy = 8;

    return texture;
}

function makePaper(frontMap) {
    const group = new THREE.Group();
    const geometry = new THREE.BoxGeometry(1.72, 2.42, 0.028);
    const front = new THREE.MeshPhysicalMaterial({
        map: frontMap,
        roughness: 0.28,
        metalness: 0.02,
        clearcoat: 0.35,
        clearcoatRoughness: 0.4,
    });
    const cream = new THREE.MeshStandardMaterial({
        color: 0xe7dccb,
        roughness: 0.5,
        metalness: 0.04,
    });
    const mesh = new THREE.Mesh(geometry, [cream, cream, cream, cream, front, cream]);
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    group.add(mesh);

    return group;
}

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

export function mountLandingScene(canvas) {
    if (! canvas || prefersReducedMotion()) {
        return () => {};
    }

    const renderer = new THREE.WebGLRenderer({
        canvas,
        antialias: true,
        alpha: true,
        powerPreference: 'high-performance',
    });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.28;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x05060a, 0.038);

    const camera = new THREE.PerspectiveCamera(36, 1, 0.1, 50);
    camera.position.set(1.15, 0.35, 8.2);

    const stage = new THREE.Group();
    stage.position.set(1.55, 0.05, 0);
    scene.add(stage);

    const maps = [
        makePaperTexture({ title: 'Chapter 1', status: 'For review', statusBg: '#d7ecf6', statusFg: '#16384a', accent: '#e24b32' }),
        makePaperTexture({ title: 'Full draft', status: 'Submitted', statusBg: '#ece7de', statusFg: '#3b372f', accent: '#7c3aed' }),
        makePaperTexture({ title: 'Defense', status: 'Approved', statusBg: '#d8f0d8', statusFg: '#1b3d24', accent: '#38bdf8' }),
        makePaperTexture({ title: 'Chapter 3', status: 'Revise', statusBg: '#ffe1cc', statusFg: '#6a3212', accent: '#ff7a55' }),
    ];

    const poses = [
        { x: 0.15, y: 0.18, z: 0.35, rotY: -0.52, rotX: -0.16, rotZ: 0.06, scale: 1.08 },
        { x: 1.05, y: -0.12, z: -0.55, rotY: 0.22, rotX: -0.08, rotZ: -0.05, scale: 0.92 },
        { x: -0.95, y: -0.42, z: -0.95, rotY: -0.88, rotX: 0.1, rotZ: 0.1, scale: 0.86 },
        { x: 1.55, y: 0.55, z: -1.35, rotY: 0.55, rotX: 0.12, rotZ: -0.08, scale: 0.72 },
    ];

    const sheets = poses.map((pose, index) => {
        const paper = makePaper(maps[index]);
        paper.scale.setScalar(pose.scale);
        paper.position.set(pose.x, pose.y - 2.4, pose.z);
        paper.rotation.set(pose.rotX, pose.rotY, pose.rotZ);
        paper.userData.base = { ...pose, phase: index * 1.15 };
        stage.add(paper);

        return paper;
    });

    const emberGlow = glowSprite('rgba(255,90,60,0.55)', 2.4);
    if (emberGlow) {
        emberGlow.position.set(1.35, 1.15, 0.8);
        stage.add(emberGlow);
    }
    const cyanGlow = glowSprite('rgba(56,189,248,0.4)', 2.1);
    if (cyanGlow) {
        cyanGlow.position.set(-1.2, 0.85, -0.2);
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
    badge.position.set(1.35, 1.15, 0.85);
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
    ring.position.set(-1.15, 0.9, 0.15);
    ring.rotation.x = Math.PI / 2.3;
    stage.add(ring);

    const dustGeo = new THREE.BufferGeometry();
    const count = 320;
    const positions = new Float32Array(count * 3);
    for (let i = 0; i < count; i += 1) {
        positions[i * 3] = (Math.random() - 0.35) * 14;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 8;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 8;
    }
    dustGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const dust = new THREE.Points(
        dustGeo,
        new THREE.PointsMaterial({
            color: 0xffd0c0,
            size: 0.022,
            transparent: true,
            opacity: 0.38,
            depthWrite: false,
        }),
    );
    scene.add(dust);

    scene.add(new THREE.AmbientLight(0x8b93a7, 0.42));
    const key = new THREE.DirectionalLight(0xfff3e8, 1.7);
    key.position.set(5, 8, 6);
    key.castShadow = true;
    key.shadow.mapSize.set(1024, 1024);
    scene.add(key);
    scene.add(new THREE.PointLight(0xff5a3c, 28, 14, 2).translateX(-2.6).translateY(1.4).translateZ(2.4));
    scene.add(new THREE.PointLight(0x38bdf8, 16, 12, 2).translateX(3.6).translateY(-0.2).translateZ(1.8));

    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(9, 64),
        new THREE.MeshStandardMaterial({
            color: 0x0b0d12,
            roughness: 0.85,
            metalness: 0.25,
        }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.set(1.4, -1.85, 0);
    floor.receiveShadow = true;
    scene.add(floor);

    const mouse = { x: 0.2, y: 0 };
    const onMove = (event) => {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -((event.clientY / window.innerHeight) * 2 - 1);
    };
    window.addEventListener('pointermove', onMove, { passive: true });

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
        const intro = Math.min(1, t / 1.35);
        const ease = 1 - (1 - intro) ** 3;

        sheets.forEach((paper) => {
            const base = paper.userData.base;
            paper.position.y = (base.y - 2.4 * (1 - ease)) + Math.sin(t * 0.85 + base.phase) * 0.14;
            paper.rotation.y = base.rotY + Math.sin(t * 0.4 + base.phase) * 0.1;
            paper.rotation.x = base.rotX + Math.cos(t * 0.48 + base.phase) * 0.05;
        });
        badge.position.y = 1.15 + Math.sin(t * 1.15) * 0.18;
        if (emberGlow) {
            emberGlow.position.y = badge.position.y;
            emberGlow.material.opacity = 0.55 + Math.sin(t * 2) * 0.12;
        }
        ring.rotation.z = t * 0.42;
        dust.rotation.y = t * 0.03;
        stage.rotation.y += ((mouse.x * 0.42) - stage.rotation.y) * 0.045;
        stage.rotation.x += ((mouse.y * 0.16) - stage.rotation.x) * 0.045;
        camera.position.x += ((1.15 + mouse.x * 0.55) - camera.position.x) * 0.035;
        camera.position.y += ((0.35 + mouse.y * 0.25) - camera.position.y) * 0.035;
        camera.lookAt(1.2, 0.15, 0);
        renderer.render(scene, camera);
    };
    frame = requestAnimationFrame(tick);

    return () => {
        cancelAnimationFrame(frame);
        observer.disconnect();
        window.removeEventListener('pointermove', onMove);
        renderer.dispose();
        maps.forEach((map) => map?.dispose());
    };
}

const canvas = document.getElementById('cast-3d');
if (canvas) {
    mountLandingScene(canvas);
}
