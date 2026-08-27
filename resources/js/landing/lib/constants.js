export const SCENE_COUNT = 5;

export const SCENE_NAMES = ['studio', 'submit', 'review', 'scan', 'ship'];

export const SCENE_PAGE_TARGETS = [0, 1, 2, 3, 3];

export const SCENE_POSES = [
    { x: 0, y: 0, scale: 1, rotation: 0 },
    { x: 155, y: -62, scale: 0.94, rotation: 4.5 },
    { x: 36, y: 54, scale: 1.1, rotation: -5 },
    { x: 168, y: 4, scale: 0.9, rotation: 6 },
    { x: 62, y: -42, scale: 1.14, rotation: -1.5 },
];

export const SCENE_PALETTES = [
    { fog: 0xf6f1e8, ember: 0xff5a3c, accent: 0xff5a3c, cyan: 0x0284c7, grid: 0xff5a3c, bloom: 0.12 },
    { fog: 0xf6f1e8, ember: 0x7c3aed, accent: 0x7c3aed, cyan: 0xa78bfa, grid: 0x7c3aed, bloom: 0.14 },
    { fog: 0xf6f1e8, ember: 0xea580c, accent: 0xea580c, cyan: 0xfb923c, grid: 0xea580c, bloom: 0.13 },
    { fog: 0xf6f1e8, ember: 0x0284c7, accent: 0x0284c7, cyan: 0x38bdf8, grid: 0x0284c7, bloom: 0.15 },
    { fog: 0xf6f1e8, ember: 0x059669, accent: 0x059669, cyan: 0x34d399, grid: 0x059669, bloom: 0.12 },
];

export const SCENE_POSES_3D = [
    { cam: [1.55, 0.55, 7.4], look: [1.7, 0.25, 0], stage: [1.85, 0.05, 0], rotY: -0.42, fov: 34, hero: { x: 0.15, y: 0.18, z: 0.15, rotX: -0.18, rotY: -0.62, rotZ: 0.08, scale: 1.62 } },
    { cam: [0.05, 0.12, 5.5], look: [1.15, 0.05, 0.1], stage: [1.1, -0.08, 0.35], rotY: 0.4, fov: 40, hero: { x: 0.55, y: -0.02, z: 0.1, rotX: -0.1, rotY: 0.48, rotZ: -0.06, scale: 1.42 } },
    { cam: [2.5, 0.9, 6.8], look: [1.65, 0.4, -0.15], stage: [2.0, 0.2, -0.15], rotY: -0.78, fov: 32, hero: { x: -0.15, y: 0.32, z: 0.4, rotX: -0.22, rotY: -0.9, rotZ: 0.1, scale: 1.72 } },
    { cam: [0.25, 0.48, 4.9], look: [1.0, 0.18, 0], stage: [1.0, 0.22, 0.5], rotY: 0.68, fov: 42, hero: { x: 0.85, y: -0.05, z: -0.2, rotX: 0.04, rotY: 0.62, rotZ: -0.08, scale: 1.48 } },
    { cam: [1.8, 0.85, 8.2], look: [1.85, 0.4, 0], stage: [1.9, 0.35, 0], rotY: -0.28, fov: 30, hero: { x: 0.05, y: 0.42, z: 0.12, rotX: -0.2, rotY: -0.45, rotZ: 0.05, scale: 1.78 } },
];

export function motionFactor() {
    if (typeof window === 'undefined') {
        return 1;
    }
    if (window.innerWidth < 768) {
        return 0.24;
    }
    if (window.innerWidth < 1024) {
        return 0.58;
    }

    return 1;
}

export function readScrollProgress() {
    const max = document.documentElement.scrollHeight - window.innerHeight;
    if (max <= 0) {
        return 0;
    }

    return Math.min(1, Math.max(0, window.scrollY / max));
}

export function progressToSceneFloat(progress) {
    return Math.min(SCENE_COUNT - 0.001, progress * SCENE_COUNT);
}

export function prefersReducedMotion() {
    return typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
