import { useEffect } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import {
    SCENE_COUNT,
    SCENE_POSES,
    SCENE_PAGE_TARGETS,
    motionFactor,
    readScrollProgress,
} from '../lib/constants.js';

gsap.registerPlugin(ScrollTrigger);

export function useFlipbookMotion(stageRef, reducedMotion, onProgress) {
    useEffect(() => {
        const root = stageRef.current;
        if (! root) {
            return undefined;
        }

        const rig = root.querySelector('[data-flipbook-rig]');
        const book = root.querySelector('[data-flipbook-book]');
        const cover = root.querySelector('[data-flipbook-cover]');
        const pages = [...root.querySelectorAll('[data-flipbook-page]')];
        const shadow = root.querySelector('[data-flipbook-shadow]');
        const nextButton = document.querySelector('[data-flipbook-next]');
        const backdrops = [...root.querySelectorAll('[data-scene-backdrop]')];
        const scrollRoot = document.getElementById('landing-scroll');
        const sections = [...document.querySelectorAll('[data-landing-scene]')];

        if (! rig || ! book || ! cover || pages.length === 0 || ! shadow || ! nextButton || ! scrollRoot) {
            return undefined;
        }

        let activeScene = -1;
        const pageCount = pages.length;

        const updateChapterUi = (sceneIndex) => {
            const pageIndex = SCENE_PAGE_TARGETS[sceneIndex] ?? 0;
            root.classList.toggle('is-final', pageIndex === pageCount);
        };

        const setReducedScene = (sceneIndex) => {
            const target = SCENE_PAGE_TARGETS[sceneIndex] ?? 0;
            pages.forEach((page, idx) => {
                page.classList.toggle('is-current', idx === target);
            });
            backdrops.forEach((backdrop, index) => {
                backdrop.style.opacity = index === sceneIndex ? '0.45' : '0';
            });
            updateChapterUi(sceneIndex);
            onProgress?.(readScrollProgress(), sceneIndex);
        };

        if (reducedMotion) {
            root.classList.add('is-reduced');
            cover.style.transform = 'rotateY(-164deg)';
            gsap.set(rig, SCENE_POSES[0]);
            setReducedScene(0);

            const onReducedScroll = () => {
                const progress = readScrollProgress();
                const index = Math.max(0, Math.min(SCENE_COUNT - 1, Math.round(progress * (SCENE_COUNT - 1))));
                if (index !== activeScene) {
                    activeScene = index;
                    setReducedScene(index);
                } else {
                    onProgress?.(progress, index);
                }
            };
            const onReducedNext = () => {
                const nextIndex = (Math.max(0, activeScene) + 1) % sections.length;
                sections[nextIndex]?.scrollIntoView({ behavior: 'auto' });
            };
            window.addEventListener('scroll', onReducedScroll, { passive: true });
            nextButton.addEventListener('click', onReducedNext);
            onReducedScroll();

            return () => {
                window.removeEventListener('scroll', onReducedScroll);
                nextButton.removeEventListener('click', onReducedNext);
            };
        }

        gsap.set(rig, { x: 0, y: 0, scale: 1, rotation: 0, opacity: 1 });
        gsap.set(nextButton, { x: 0, y: 0 });
        gsap.set(cover, { rotateY: 0, transformOrigin: '0% 50%' });
        gsap.set(pages, { rotateY: 0, transformOrigin: '0% 50%' });
        gsap.set(shadow, { xPercent: 18, scaleX: 0.7, opacity: 0.18 });
        gsap.set(backdrops, { opacity: 0, scale: 1.08 });
        gsap.set(backdrops[0], { opacity: 0.4, scale: 1 });

        const opening = gsap.timeline({ delay: 0.2, defaults: { ease: 'power3.inOut' } });
        opening
            .to(cover, { rotateY: -164, duration: 1.15 }, 0)
            .to(shadow, { xPercent: 0, scaleX: 1, opacity: 0.32, duration: 1.05 }, 0.05)
            .fromTo(book, { rotateX: 5, rotateZ: 0 }, { rotateX: 8, rotateZ: -1.5, duration: 1.1 }, 0);

        const master = gsap.timeline({
            defaults: { ease: 'none' },
            scrollTrigger: {
                trigger: scrollRoot,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 0.65,
                invalidateOnRefresh: true,
                onUpdate: (self) => {
                    const index = Math.max(0, Math.min(
                        SCENE_COUNT - 1,
                        Math.floor(self.progress * (SCENE_COUNT - 1) + 0.12),
                    ));
                    if (index !== activeScene) {
                        activeScene = index;
                        updateChapterUi(index);
                    }
                    onProgress?.(self.progress, index);
                },
            },
        });

        SCENE_POSES.slice(1).forEach((pose, transitionIndex) => {
            const sceneIndex = transitionIndex + 1;
            const start = transitionIndex + 0.06;
            master
                .to(rig, {
                    x: () => pose.x * motionFactor(),
                    y: () => pose.y * motionFactor(),
                    scale: pose.scale,
                    rotation: pose.rotation,
                    duration: 0.88,
                    ease: 'power3.inOut',
                }, start)
                .to(nextButton, {
                    x: () => pose.x * motionFactor(),
                    y: () => pose.y * motionFactor(),
                    duration: 0.88,
                    ease: 'power3.inOut',
                }, start)
                .to(backdrops[sceneIndex - 1], { opacity: 0, scale: 1.14, duration: 0.62 }, start)
                .to(backdrops[sceneIndex], { opacity: 0.45, scale: 1, duration: 0.72 }, start + 0.12);
        });

        pages.forEach((page, pageIndex) => {
            const start = pageIndex + 0.16;
            const curl = page.querySelector('[data-flipbook-curl]');
            const sweep = page.querySelector('[data-flipbook-sweep]');
            master
                .set(page, { zIndex: 32 }, start)
                .fromTo(curl, { rotateY: 0, skewY: 0, opacity: 0 }, {
                    rotateY: -34, skewY: -3, opacity: 0.78, duration: 0.28, ease: 'power2.in',
                }, start)
                .fromTo(sweep, { xPercent: 76, opacity: 0 }, {
                    xPercent: -118, opacity: 0.64, duration: 0.66, ease: 'power2.inOut',
                }, start + 0.03)
                .to(page, { rotateY: -180, duration: 0.74, ease: 'power3.inOut' }, start + 0.05)
                .to(curl, { rotateY: -4, skewY: 0, opacity: 0, duration: 0.36, ease: 'power2.out' }, start + 0.48)
                .set(page, { zIndex: 3 + pageIndex }, start + 0.79)
                .to(shadow, {
                    xPercent: pageIndex % 2 === 0 ? -7 : 5,
                    scaleX: 1.12,
                    opacity: 0.42,
                    duration: 0.34,
                    yoyo: true,
                    repeat: 1,
                }, start + 0.08);
        });

        master
            .to(book, { rotateX: 12, rotateZ: -0.5, duration: 0.42, ease: 'power2.inOut' }, 0.2)
            .to(book, { rotateX: 7, rotateZ: -2.5, duration: 0.52, ease: 'power3.out' }, 0.64)
            .to(book, { rotateX: 10, rotateZ: 1.5, duration: 0.65, ease: 'power3.inOut' }, 1.2)
            .to(book, { rotateX: 6, rotateZ: -3, duration: 0.7, ease: 'power3.inOut' }, 2.15)
            .to(book, { rotateX: 8, rotateZ: 0, duration: 0.7, ease: 'power3.out' }, 3.12);

        const onNext = () => {
            const index = Math.max(0, activeScene);
            const nextIndex = (index + 1) % sections.length;
            sections[nextIndex]?.scrollIntoView({ behavior: 'smooth' });
        };

        nextButton.addEventListener('click', onNext);
        updateChapterUi(0);
        onProgress?.(0, 0);

        return () => {
            opening.kill();
            master.scrollTrigger?.kill();
            master.kill();
            gsap.killTweensOf([rig, book, cover, shadow, nextButton, ...pages, ...backdrops].filter(Boolean));
            nextButton.removeEventListener('click', onNext);
        };
    }, [stageRef, reducedMotion, onProgress]);
}
