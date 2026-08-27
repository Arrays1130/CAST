import { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import * as THREE from 'three';
import { useScrollState } from '../context/ScrollContext.jsx';
import { damp } from '../lib/math.js';

const OFFSETS = [
    [-1.85, 1.08, 0.65],
    [1.72, 0.72, 0.82],
    [-1.68, 0.58, 0.68],
    [1.78, 0.82, 0.55],
    [1.55, 1.12, 0.72],
];

function StudioProp({ groupRef }) {
    return (
        <group ref={groupRef} userData={{ kind: 'studio' }}>
            <mesh>
                <octahedronGeometry args={[0.42, 1]} />
                <meshPhysicalMaterial color={0xff5a3c} transparent opacity={0.76} roughness={0.28} emissive={0xff5a3c} emissiveIntensity={0.12} />
            </mesh>
            <mesh rotation={[Math.PI / 2.25, 0, 0]}>
                <torusGeometry args={[0.82, 0.015, 12, 96]} />
                <meshPhysicalMaterial color={0x0284c7} transparent opacity={0.46} emissive={0x0284c7} emissiveIntensity={0.12} />
            </mesh>
        </group>
    );
}

function SubmitProp({ groupRef }) {
    const arrowRef = useRef();

    return (
        <group ref={groupRef} userData={{ kind: 'submit' }}>
            <mesh rotation={[0, 0, -0.08]}>
                <boxGeometry args={[1.18, 1.52, 0.09]} />
                <meshPhysicalMaterial color={0x7c3aed} transparent opacity={0.7} emissive={0x7c3aed} emissiveIntensity={0.12} />
            </mesh>
            <group ref={arrowRef} position={[0, 0, 0.18]}>
                <mesh>
                    <cylinderGeometry args={[0.055, 0.055, 0.66, 20]} />
                    <meshPhysicalMaterial color={0xff5a3c} emissive={0xff5a3c} emissiveIntensity={0.12} />
                </mesh>
                <mesh position={[0, 0.48, 0]}>
                    <coneGeometry args={[0.22, 0.34, 24]} />
                    <meshPhysicalMaterial color={0xff5a3c} emissive={0xff5a3c} emissiveIntensity={0.12} />
                </mesh>
            </group>
        </group>
    );
}

function ReviewProp({ groupRef }) {
    const cardsRef = useRef([]);
    const ringRef = useRef();

    return (
        <group ref={groupRef} userData={{ kind: 'review', cards: cardsRef, ring: ringRef }}>
            {[-0.65, 0, 0.65].map((x, index) => (
                <mesh
                    key={x}
                    ref={(el) => { cardsRef.current[index] = el; }}
                    position={[x, (index - 1) * 0.24, -Math.abs(index - 1) * 0.16]}
                    rotation={[0, 0, (index - 1) * 0.12]}
                >
                    <boxGeometry args={[0.72, 0.46, 0.045]} />
                    <meshPhysicalMaterial color={index === 1 ? 0xff5a3c : 0xea580c} transparent opacity={0.68} emissive={index === 1 ? 0xff5a3c : 0xea580c} emissiveIntensity={0.12} />
                </mesh>
            ))}
            <mesh ref={ringRef} position={[0.7, 0.72, 0.1]}>
                <torusGeometry args={[0.52, 0.035, 16, 96]} />
                <meshPhysicalMaterial color={0x059669} transparent opacity={0.8} emissive={0x059669} emissiveIntensity={0.12} />
            </mesh>
        </group>
    );
}

function ScanProp({ groupRef }) {
    const beamRef = useRef();

    return (
        <group ref={groupRef} userData={{ kind: 'scan', beam: beamRef }}>
            <mesh>
                <torusGeometry args={[0.58, 0.055, 18, 96]} />
                <meshPhysicalMaterial color={0x0284c7} transparent opacity={0.8} emissive={0x0284c7} emissiveIntensity={0.12} />
            </mesh>
            <mesh position={[0.5, -0.55, 0]} rotation={[0, 0, -0.72]}>
                <boxGeometry args={[0.12, 0.72, 0.1]} />
                <meshPhysicalMaterial color={0x0284c7} transparent opacity={0.76} />
            </mesh>
            <mesh ref={beamRef}>
                <boxGeometry args={[1.55, 0.045, 0.04]} />
                <meshPhysicalMaterial color={0x38bdf8} transparent opacity={0.72} emissive={0x38bdf8} emissiveIntensity={0.12} />
            </mesh>
            {[-0.45, 0, 0.45].map((x, index) => (
                <mesh key={x} position={[x, 0.82 + Math.abs(index - 1) * 0.12, 0]}>
                    <sphereGeometry args={[0.07, 20, 20]} />
                    <meshPhysicalMaterial color={index === 1 ? 0xff5a3c : 0x0284c7} emissive={index === 1 ? 0xff5a3c : 0x0284c7} emissiveIntensity={0.12} />
                </mesh>
            ))}
        </group>
    );
}

function ShipProp({ groupRef }) {
    const sealRef = useRef();

    return (
        <group ref={groupRef} userData={{ kind: 'ship', seal: sealRef }}>
            <mesh position={[-0.23, -0.68, -0.06]} rotation={[0, 0, 0.18]}>
                <planeGeometry args={[0.34, 0.92]} />
                <meshPhysicalMaterial color={0xff5a3c} transparent opacity={0.7} side={THREE.DoubleSide} />
            </mesh>
            <mesh position={[0.23, -0.68, -0.06]} rotation={[0, 0, -0.18]}>
                <planeGeometry args={[0.34, 0.92]} />
                <meshPhysicalMaterial color={0xff5a3c} transparent opacity={0.7} side={THREE.DoubleSide} />
            </mesh>
            <mesh ref={sealRef} rotation={[Math.PI / 2, 0, 0]}>
                <cylinderGeometry args={[0.58, 0.58, 0.13, 64]} />
                <meshPhysicalMaterial color={0x059669} transparent opacity={0.84} emissive={0x059669} emissiveIntensity={0.12} />
            </mesh>
            <mesh position={[0, 0, 0.09]}>
                <torusGeometry args={[0.4, 0.035, 16, 80]} />
                <meshPhysicalMaterial color={0xffd0c0} transparent opacity={0.74} />
            </mesh>
        </group>
    );
}

const PROPS = [StudioProp, SubmitProp, ReviewProp, ScanProp, ShipProp];

function setOpacity(object, weight) {
    object.traverse((child) => {
        if (child.material) {
            child.material.opacity = weight * 0.62;
            child.material.transparent = child.material.opacity < 0.98;
        }
    });
}

export default function SceneProps({ heroPoseRef }) {
    const refs = [useRef(), useRef(), useRef(), useRef(), useRef()];
    const scales = useRef([0.01, 0.01, 0.01, 0.01, 0.01]);
    const { sceneFloat } = useScrollState();

    useFrame(({ clock }, dt) => {
        const t = clock.getElapsedTime();
        const heroPose = heroPoseRef.current;
        const objectTravel = typeof window !== 'undefined' && window.innerWidth < 768 ? 0.55 : (window.innerWidth < 1024 ? 0.78 : 1);
        const objectScale = typeof window !== 'undefined' && window.innerWidth < 768 ? 0.78 : 1;

        refs.forEach((ref, objectIndex) => {
            const object = ref.current;
            if (! object || ! heroPose) {
                return;
            }
            const distance = Math.abs(sceneFloat - objectIndex);
            const weight = Math.max(0, 1 - distance * 1.35);
            const offset = OFFSETS[objectIndex];
            object.visible = weight > 0.015;
            object.position.set(
                heroPose.x + offset[0] * objectTravel,
                heroPose.y + offset[1] + Math.sin(t * 0.8 + objectIndex) * 0.12,
                heroPose.z + offset[2],
            );
            const targetScale = (0.62 + weight * 0.55) * objectScale;
            scales.current[objectIndex] = damp(scales.current[objectIndex], targetScale, 8, dt);
            object.scale.setScalar(scales.current[objectIndex]);
            setOpacity(object, weight);
            object.rotation.y = t * (0.16 + objectIndex * 0.025) + objectIndex * 0.28;
            object.rotation.x = Math.sin(t * 0.45 + objectIndex) * 0.12;
        });

        const studio = refs[0].current;
        if (studio?.children[0]) {
            studio.children[0].rotation.set(t * 0.48, t * 0.62, t * 0.28);
        }
        if (studio?.children[1]) {
            studio.children[1].rotation.z = t * 0.32;
        }

        const submit = refs[1].current;
        const submitArrow = submit?.children[1];
        if (submitArrow) {
            submitArrow.position.y = Math.sin(t * 1.6) * 0.18;
        }

        const review = refs[2].current;
        review?.children?.forEach((card, cardIndex) => {
            if (cardIndex < 3) {
                card.position.z = -Math.abs(cardIndex - 1) * 0.16 + Math.sin(t * 0.9 + cardIndex * 0.8) * 0.12;
                card.rotation.y = Math.sin(t * 0.6 + cardIndex) * 0.16;
            }
        });
        if (review?.children[3]) {
            review.children[3].rotation.z = t * 0.7;
        }

        const scan = refs[3].current;
        if (scan?.children[2]) {
            scan.children[2].position.y = Math.sin(t * 1.8) * 0.52;
        }

        const ship = refs[4].current;
        if (ship?.children[2]) {
            ship.children[2].rotation.z = t * 0.38;
        }
    });

    return (
        <>
            {PROPS.map((Component, index) => (
                <Component key={index} groupRef={refs[index]} />
            ))}
        </>
    );
}
