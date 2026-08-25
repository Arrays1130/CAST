import * as THREE from 'three';

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function makePaperTexture() {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 720;
    const ctx = canvas.getContext('2d');
    if (! ctx) {
        return null;
    }

    ctx.fillStyle = '#f6f1e8';
    ctx.fillRect(0, 0, 512, 720);
    ctx.fillStyle = '#efe6d8';
    ctx.fillRect(0, 0, 512, 64);
    ctx.fillStyle = '#e24b32';
    ctx.fillRect(36, 28, 72, 10);
    ctx.fillStyle = '#1a1c22';
    ctx.font = 'bold 36px Georgia, serif';
    ctx.fillText('Chapter 1', 36, 118);
    ctx.fillStyle = '#8a8175';
    ctx.font = '18px Outfit, sans-serif';
    for (let i = 0; i < 14; i += 1) {
        const w = 280 + ((i * 47) % 140);
        ctx.fillRect(36, 160 + i * 32, w, 8);
    }
    ctx.fillStyle = '#d7ecf6';
    ctx.fillRect(36, 636, 118, 32);
    ctx.fillStyle = '#16384a';
    ctx.font = 'bold 14px Outfit, sans-serif';
    ctx.fillText('For review', 48, 659);

    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.anisotropy = 8;

    return texture;
}

function makePaper(frontMap) {
    const group = new THREE.Group();
    const geometry = new THREE.BoxGeometry(1.46, 2.05, 0.03);
    const front = new THREE.MeshStandardMaterial({
        map: frontMap,
        roughness: 0.42,
        metalness: 0.04,
    });
    const cream = new THREE.MeshStandardMaterial({
        color: 0xe8dfd0,
        roughness: 0.55,
        metalness: 0.02,
    });
    const mesh = new THREE.Mesh(geometry, [cream, cream, cream, cream, front, cream]);
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    group.add(mesh);

    return group;
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
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.15;
    renderer.shadowMap.enabled = true;

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x07080c, 0.045);

    const camera = new THREE.PerspectiveCamera(38, 1, 0.1, 40);
    camera.position.set(0, 0.2, 7.4);

    const stage = new THREE.Group();
    scene.add(stage);

    const frontMap = makePaperTexture();
    const sheets = [
        { x: -0.15, y: 0.1, z: 0.05, rotY: -0.42, rotX: -0.18, rotZ: 0.08 },
        { x: 0.35, y: -0.05, z: -0.35, rotY: 0.18, rotX: -0.08, rotZ: -0.04 },
        { x: -0.85, y: -0.35, z: -0.7, rotY: -0.72, rotX: 0.12, rotZ: 0.12 },
    ].map((pose, index) => {
        const paper = makePaper(frontMap);
        paper.position.set(pose.x, pose.y, pose.z);
        paper.rotation.set(pose.rotX, pose.rotY, pose.rotZ);
        paper.userData.base = { ...pose, phase: index * 1.3 };
        stage.add(paper);

        return paper;
    });

    const badge = new THREE.Mesh(
        new THREE.SphereGeometry(0.22, 32, 32),
        new THREE.MeshStandardMaterial({
            color: 0xff5a3c,
            emissive: 0xe24b32,
            emissiveIntensity: 0.55,
            roughness: 0.2,
            metalness: 0.35,
        }),
    );
    badge.position.set(1.15, 0.85, 0.6);
    stage.add(badge);

    const ring = new THREE.Mesh(
        new THREE.TorusGeometry(0.38, 0.018, 16, 64),
        new THREE.MeshStandardMaterial({
            color: 0x7dd3fc,
            emissive: 0x38bdf8,
            emissiveIntensity: 0.4,
            roughness: 0.25,
            metalness: 0.6,
        }),
    );
    ring.position.set(-1.35, 0.95, 0.2);
    ring.rotation.x = Math.PI / 2.4;
    stage.add(ring);

    const dust = new THREE.Points(
        (() => {
            const count = 180;
            const positions = new Float32Array(count * 3);
            for (let i = 0; i < count; i += 1) {
                positions[i * 3] = (Math.random() - 0.5) * 10;
                positions[i * 3 + 1] = (Math.random() - 0.5) * 6;
                positions[i * 3 + 2] = (Math.random() - 0.5) * 6;
            }
            const geometry = new THREE.BufferGeometry();
            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

            return geometry;
        })(),
        new THREE.PointsMaterial({
            color: 0xffc4b0,
            size: 0.018,
            transparent: true,
            opacity: 0.45,
        }),
    );
    scene.add(dust);

    scene.add(new THREE.AmbientLight(0x9aa3b5, 0.55));
    const key = new THREE.DirectionalLight(0xffece3, 1.35);
    key.position.set(4, 6, 5);
    key.castShadow = true;
    scene.add(key);
    const rim = new THREE.PointLight(0xff5a3c, 18, 12);
    rim.position.set(-2.4, 1.2, 2.2);
    scene.add(rim);
    const fill = new THREE.PointLight(0x38bdf8, 10, 10);
    fill.position.set(3.2, -0.6, 1.5);
    scene.add(fill);

    const floor = new THREE.Mesh(
        new THREE.CircleGeometry(6.5, 48),
        new THREE.MeshStandardMaterial({
            color: 0x12141a,
            roughness: 0.9,
            metalness: 0.2,
        }),
    );
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = -1.7;
    floor.receiveShadow = true;
    scene.add(floor);

    const mouse = { x: 0, y: 0 };
    const onMove = (event) => {
        const rect = canvas.getBoundingClientRect();
        mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -(((event.clientY - rect.top) / rect.height) * 2 - 1);
    };
    window.addEventListener('pointermove', onMove, { passive: true });

    const resize = () => {
        const { clientWidth, clientHeight } = canvas;
        const width = Math.max(clientWidth, 1);
        const height = Math.max(clientHeight, 1);
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
        sheets.forEach((paper) => {
            const base = paper.userData.base;
            paper.position.y = base.y + Math.sin(t * 0.9 + base.phase) * 0.12;
            paper.rotation.y = base.rotY + Math.sin(t * 0.45 + base.phase) * 0.08;
            paper.rotation.x = base.rotX + Math.cos(t * 0.5 + base.phase) * 0.04;
        });
        badge.position.y = 0.85 + Math.sin(t * 1.2) * 0.16;
        ring.rotation.z = t * 0.35;
        dust.rotation.y = t * 0.04;
        stage.rotation.y += ((mouse.x * 0.35) - stage.rotation.y) * 0.06;
        stage.rotation.x += ((mouse.y * 0.18) - stage.rotation.x) * 0.06;
        camera.position.x += ((mouse.x * 0.45) - camera.position.x) * 0.04;
        camera.lookAt(0, 0.1, 0);
        renderer.render(scene, camera);
    };
    frame = requestAnimationFrame(tick);

    return () => {
        cancelAnimationFrame(frame);
        observer.disconnect();
        window.removeEventListener('pointermove', onMove);
        renderer.dispose();
        frontMap?.dispose();
    };
}

const canvas = document.getElementById('cast-3d');
if (canvas) {
    mountLandingScene(canvas);
}
