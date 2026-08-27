import * as THREE from 'three';

export function lerp(a, b, t) {
    return a + (b - a) * t;
}

export function lerp3(a, b, t) {
    return [lerp(a[0], b[0], t), lerp(a[1], b[1], t), lerp(a[2], b[2], t)];
}

export function lerpColor(a, b, t) {
    return new THREE.Color(a).lerp(new THREE.Color(b), t);
}

export function easeInOutCubic(t) {
    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

export function easeOutExpo(t) {
    return t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
}

export function easeOutBack(t) {
    const c1 = 1.70158;
    const c3 = c1 + 1;

    return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
}

export function damp(current, target, lambda, dt) {
    return lerp(current, target, 1 - Math.exp(-lambda * dt));
}
