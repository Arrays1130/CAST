import { Suspense, useRef, useMemo } from 'react';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import { EffectComposer, Bloom } from '@react-three/postprocessing';
import * as THREE from 'three';
import { useScrollState } from '../context/ScrollContext.jsx';
import {
    SCENE_PALETTES,
    SCENE_POSES_3D,
} from '../lib/constants.js';
import {
    easeInOutCubic,
    lerp,
    lerp3,
    lerpColor,
    damp,
} from '../lib/math.js';
import SceneProps from './SceneProps.jsx';

function glowTexture(color) {
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

    return new THREE.CanvasTexture(c);
}

function makeGrid(size, divisions) {
    const step = size / divisions;
    const half = size / 2;
    const vertices = [];
    for (let i = 0; i <= divisions; i += 1) {
        const pos = -half + i * step;
        vertices.push(-half, 0, pos, half, 0, pos, pos, 0, -half, pos, 0, half);
    }
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));

    return geometry;
}

function Environment({ heroPoseRef }) {
    const { scene } = useThree();
    const stageRef = useRef();
    const crystalRef = useRef();
    const gridRef = useRef();
    const dustRef = useRef();
    const emberLightRef = useRef();
    const cyanLightRef = useRef();
    const rimLightRef = useRef();
    const orbARef = useRef();
    const orbBRef = useRef();
    const orbCRef = useRef();
    const ringsRef = useRef([]);

    const mouse = useRef({ x: 0.1, y: 0 });
    const smoothMouse = useRef({ x: 0.1, y: 0 });
    const scrollVelocity = useRef(0);
    const lastScroll = useRef(typeof window !== 'undefined' ? window.scrollY : 0);

    const { sceneFloat } = useScrollState();

    const orbTextures = useMemo(() => ([
        glowTexture('rgba(255,90,60,0.28)'),
        glowTexture('rgba(2,132,199,0.22)'),
        glowTexture('rgba(124,58,237,0.18)'),
    ]), []);

    const dustPositions = useMemo(() => {
        const positions = new Float32Array(180 * 3);
        for (let i = 0; i < 180; i += 1) {
            positions[i * 3] = (Math.random() - 0.3) * 18;
            positions[i * 3 + 1] = (Math.random() - 0.5) * 11;
            positions[i * 3 + 2] = (Math.random() - 0.5) * 12;
        }

        return positions;
    }, []);

    useFrame(({ clock, camera }, dt) => {
        const t = clock.getElapsedTime();
        const scrollY = typeof window !== 'undefined' ? window.scrollY : 0;
        scrollVelocity.current = damp(scrollVelocity.current, (scrollY - lastScroll.current) * 0.055, 10, dt);
        lastScroll.current = scrollY;
        smoothMouse.current.x = damp(smoothMouse.current.x, mouse.current.x, 4.2, dt);
        smoothMouse.current.y = damp(smoothMouse.current.y, mouse.current.y, 4.2, dt);

        const indexA = Math.floor(sceneFloat);
        const indexB = Math.min(SCENE_PALETTES.length - 1, indexA + 1);
        const blend = easeInOutCubic(sceneFloat - indexA);
        const poseA = SCENE_POSES_3D[indexA];
        const poseB = SCENE_POSES_3D[indexB];
        const palA = SCENE_PALETTES[indexA];
        const palB = SCENE_PALETTES[indexB];

        const palette = {
            fog: lerpColor(palA.fog, palB.fog, blend),
            ember: lerpColor(palA.ember, palB.ember, blend),
            accent: lerpColor(palA.accent, palB.accent, blend),
            cyan: lerpColor(palA.cyan, palB.cyan, blend),
            grid: lerpColor(palA.grid, palB.grid, blend),
        };

        if (scene.fog) {
            scene.fog.color.copy(palette.fog);
        }

        if (emberLightRef.current) {
            emberLightRef.current.color.copy(palette.ember);
        }
        if (cyanLightRef.current) {
            cyanLightRef.current.color.copy(palette.cyan);
        }
        if (rimLightRef.current) {
            rimLightRef.current.color.copy(palette.accent);
        }
        if (gridRef.current) {
            gridRef.current.material.color.copy(palette.grid);
        }
        if (dustRef.current) {
            dustRef.current.material.color.copy(palette.accent);
        }
        if (crystalRef.current) {
            crystalRef.current.material.color.copy(palette.accent);
            crystalRef.current.material.emissive.copy(palette.accent);
        }

        const heroPose = {
            x: lerp(poseA.hero.x, poseB.hero.x, blend),
            y: lerp(poseA.hero.y, poseB.hero.y, blend),
            z: lerp(poseA.hero.z, poseB.hero.z, blend),
            rotX: lerp(poseA.hero.rotX, poseB.hero.rotX, blend),
            rotY: lerp(poseA.hero.rotY, poseB.hero.rotY, blend),
            rotZ: lerp(poseA.hero.rotZ, poseB.hero.rotZ, blend),
            scale: lerp(poseA.hero.scale, poseB.hero.scale, blend),
        };
        heroPoseRef.current = heroPose;

        const camPos = lerp3(poseA.cam, poseB.cam, blend);
        const lookAt = lerp3(poseA.look, poseB.look, blend);
        const stagePos = lerp3(poseA.stage, poseB.stage, blend);
        const rotY = lerp(poseA.rotY, poseB.rotY, blend);
        const fov = lerp(poseA.fov, poseB.fov, blend);

        camera.position.set(camPos[0], camPos[1], camPos[2]);
        camera.fov = fov;
        camera.updateProjectionMatrix();
        camera.lookAt(lookAt[0], lookAt[1], lookAt[2]);

        if (stageRef.current) {
            stageRef.current.position.set(stagePos[0], stagePos[1], stagePos[2]);
            stageRef.current.rotation.y = rotY;
        }

        // Keep crystal away from left hero copy; fade on scene 0
        const crystalFade = Math.min(1, sceneFloat * 1.4);
        if (crystalRef.current) {
            crystalRef.current.visible = crystalFade > 0.08;
            crystalRef.current.material.opacity = 0.28 * Math.max(0.25, crystalFade);
            crystalRef.current.position.set(
                heroPose.x - 2.85,
                heroPose.y + 0.55 + Math.sin(t * 1.05) * 0.18,
                heroPose.z + 0.65,
            );
            crystalRef.current.rotation.set(t * 0.55, t * 0.7, t * 0.25);
            crystalRef.current.scale.setScalar(0.72);
        }

        ringsRef.current.forEach((ring, i) => {
            if (ring) {
                ring.visible = crystalFade > 0.08;
                ring.material.opacity = (0.1 - i * 0.03) * Math.max(0.25, crystalFade);
                ring.position.set(heroPose.x - 2.65 + i * 0.1, heroPose.y + 0.35, heroPose.z + 0.45);
                ring.rotation.z = t * (0.35 + i * 0.18);
            }
        });

        if (orbARef.current) {
            orbARef.current.position.set(-3.8 + smoothMouse.current.x * 0.7, 2.4 + smoothMouse.current.y * 0.55, -4.2);
        }
        if (orbBRef.current) {
            orbBRef.current.position.set(5.2 + smoothMouse.current.x * 0.5, -0.6 + Math.sin(t * 0.5) * 0.3, -3.2);
        }
        if (orbCRef.current) {
            orbCRef.current.position.set(1.2, 3.8 + Math.cos(t * 0.4) * 0.25, -5.2);
        }

        if (dustRef.current) {
            dustRef.current.rotation.y = t * 0.055;
        }
        if (gridRef.current) {
            gridRef.current.rotation.y = t * 0.02;
        }
    });

    if (typeof window !== 'undefined' && ! window.__castLandingPointerBound) {
        window.__castLandingPointerBound = true;
        window.addEventListener('pointermove', (event) => {
            mouse.current.x = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.current.y = -((event.clientY / window.innerHeight) * 2 - 1);
        }, { passive: true });
    }

    const gridGeometry = useMemo(() => makeGrid(24, 48), []);

    return (
        <>
            <fog attach="fog" args={[SCENE_PALETTES[0].fog, 0.022]} />
            <ambientLight intensity={0.78} color={0xf6f1e8} />
            <directionalLight position={[5.5, 8.5, 6]} intensity={1.15} color={0xfff8ef} castShadow />
            <pointLight ref={emberLightRef} position={[-3, 1.8, 2.8]} intensity={6} color={0xff5a3c} distance={18} decay={2} />
            <pointLight ref={cyanLightRef} position={[4, 0, 2.2]} intensity={4.5} color={0x0284c7} distance={15} decay={2} />
            <pointLight ref={rimLightRef} position={[0.4, 2.8, -3.2]} intensity={3} color={0x7c3aed} distance={13} decay={2} />

            <mesh rotation={[-Math.PI / 2, 0, 0]} position={[1.5, -1.96, 0]} receiveShadow>
                <circleGeometry args={[11, 64]} />
                <meshStandardMaterial color={0xefe8db} roughness={0.92} metalness={0.02} />
            </mesh>

            <lineSegments ref={gridRef} geometry={gridGeometry} position={[1.5, -1.95, 0]}>
                <lineBasicMaterial color={0xff5a3c} transparent opacity={0.035} />
            </lineSegments>

            <points ref={dustRef}>
                <bufferGeometry>
                    <bufferAttribute attach="attributes-position" args={[dustPositions, 3]} />
                </bufferGeometry>
                <pointsMaterial color={0xc4a484} size={0.014} transparent opacity={0.12} depthWrite={false} />
            </points>

            {orbTextures.map((map, i) => (
                map ? (
                    <sprite
                        key={i}
                        ref={i === 0 ? orbARef : i === 1 ? orbBRef : orbCRef}
                        scale={[i === 0 ? 3.8 : i === 1 ? 3.1 : 2.5, i === 0 ? 3.8 : i === 1 ? 3.1 : 2.5, 1]}
                    >
                        <spriteMaterial map={map} transparent opacity={0.35} depthWrite={false} />
                    </sprite>
                ) : null
            ))}

            <group ref={stageRef}>
                <SceneProps heroPoseRef={heroPoseRef} />
                <mesh ref={crystalRef}>
                    <icosahedronGeometry args={[0.2, 0]} />
                    <meshPhysicalMaterial color={0xff5a3c} emissive={0xff5a3c} emissiveIntensity={0.1} roughness={0.32} metalness={0.18} clearcoat={0.5} transparent opacity={0.28} />
                </mesh>
                {[0.7, 0.95].map((radius, i) => (
                    <mesh
                        key={radius}
                        ref={(el) => { ringsRef.current[i] = el; }}
                        rotation={[Math.PI / 2.15 + i * 0.1, 0, 0]}
                    >
                        <torusGeometry args={[radius, 0.01, 12, 80]} />
                        <meshPhysicalMaterial color={0x0284c7} emissive={0x0284c7} emissiveIntensity={0.12} transparent opacity={0.1 - i * 0.03} />
                    </mesh>
                ))}
            </group>
        </>
    );
}

function PostFX() {
    return (
        <EffectComposer>
            <Bloom intensity={0.07} luminanceThreshold={0.64} luminanceSmoothing={0.94} />
        </EffectComposer>
    );
}

export default function CinematicCanvas() {
    const heroPoseRef = useRef(SCENE_POSES_3D[0].hero);

    return (
        <div className="landing-canvas-wrap" aria-hidden="true">
            <Canvas
                className="landing-canvas"
                gl={{ antialias: true, alpha: true, powerPreference: 'high-performance' }}
                dpr={Math.min(typeof window !== 'undefined' ? window.devicePixelRatio : 1, 2)}
                camera={{ fov: 32, near: 0.1, far: 60, position: [1.55, 0.55, 7.4] }}
                onCreated={({ gl }) => {
                    gl.setClearColor(0xf6f1e8, 0);
                    gl.outputColorSpace = THREE.SRGBColorSpace;
                    gl.toneMapping = THREE.ACESFilmicToneMapping;
                    gl.toneMappingExposure = 1.05;
                }}
            >
                <Suspense fallback={null}>
                    <Environment heroPoseRef={heroPoseRef} />
                    <PostFX />
                </Suspense>
            </Canvas>
        </div>
    );
}
