import { useCallback, useEffect, useRef, useState } from 'react';
import {
    SCENE_COUNT,
    SCENE_PAGE_TARGETS,
    progressToSceneFloat,
} from '../lib/constants.js';

export function useScrollProgress(reducedMotion) {
    const [state, setState] = useState({
        progress: 0,
        sceneFloat: 0,
        activeScene: 0,
        writeOpacity: 0,
        checkOpacity: 0,
        chapterLabel: 'Chapter 1',
        isFinal: false,
    });

    const activeSceneRef = useRef(-1);
    const pageCount = 3;

    useEffect(() => {
        document.querySelectorAll('.landing-scene-copy').forEach((el) => {
            el.classList.add('is-visible');
        });

        const sections = [...document.querySelectorAll('[data-landing-scene]')];
        const dots = [...document.querySelectorAll('.landing-dot')];

        dots.forEach((dot) => {
            const onClick = () => {
                const target = sections[Number(dot.dataset.scrollTo)];
                target?.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth' });
            };
            dot.addEventListener('click', onClick);
            dot.__castLandingClick = onClick;
        });

        return () => {
            dots.forEach((dot) => {
                if (dot.__castLandingClick) {
                    dot.removeEventListener('click', dot.__castLandingClick);
                    delete dot.__castLandingClick;
                }
            });
        };
    }, [reducedMotion]);

    const onProgress = useCallback((progress, sceneIndex) => {
        const sceneFloat = progressToSceneFloat(progress);
        const activeScene = Math.max(0, Math.min(SCENE_COUNT - 1, sceneIndex));
        const pageIndex = SCENE_PAGE_TARGETS[activeScene] ?? 0;
        const label = pageIndex < pageCount
            ? `Chapter ${pageIndex + 1}`
            : 'Defense ready';

        const sections = document.querySelectorAll('[data-landing-scene]');
        const dots = document.querySelectorAll('.landing-dot');
        const scrollHint = document.querySelector('.landing-scroll-hint');
        const progressFill = document.querySelector('.landing-progress-fill');
        const sceneCounter = document.querySelector('.landing-scene-counter');

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

        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === activeScene);
        });

        if (scrollHint) {
            scrollHint.style.opacity = window.scrollY > 70 ? '0' : '1';
        }
        if (progressFill) {
            progressFill.style.transform = `scaleX(${progress})`;
        }

        if (activeScene !== activeSceneRef.current) {
            activeSceneRef.current = activeScene;
            document.body.dataset.landingScene = String(activeScene);
            window.dispatchEvent(new CustomEvent('landing:scenechange', {
                detail: { index: activeScene },
            }));
        }

        const writeOpacity = computeActorOpacity(progress, 0.13, 0.42, activeScene <= 1);
        const checkOpacity = computeActorOpacity(progress, 0.445, 0.93, activeScene >= 2 && activeScene <= 3);

        setState({
            progress,
            sceneFloat,
            activeScene,
            writeOpacity,
            checkOpacity,
            chapterLabel: label,
            isFinal: pageIndex >= pageCount,
        });
    }, []);

    return { state, onProgress };
}

function computeActorOpacity(progress, fadeIn, fadeOut, inRange) {
    if (! inRange) {
        return 0;
    }
    if (progress < fadeIn) {
        return Math.min(1, progress / fadeIn);
    }
    if (progress > fadeOut) {
        return Math.max(0, 1 - (progress - fadeOut) / 0.07);
    }

    return 1;
}
