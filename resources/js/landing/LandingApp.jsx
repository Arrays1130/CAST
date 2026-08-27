import { lazy, Suspense, useRef, useEffect } from 'react';
import { ScrollContext } from './context/ScrollContext.jsx';
import { useScrollProgress } from './hooks/useScrollProgress.js';
import { useFlipbookMotion } from './hooks/useFlipbookMotion.js';
import { prefersReducedMotion } from './lib/constants.js';
import FlipBookStage from './scene/FlipBookStage.jsx';

const CinematicCanvas = lazy(() => import('./scene/CinematicCanvas.jsx'));

export default function LandingApp({ pages }) {
    const stageRef = useRef(null);
    const reducedMotion = prefersReducedMotion();
    const { state: scrollState, onProgress } = useScrollProgress(reducedMotion);

    useFlipbookMotion(stageRef, reducedMotion, onProgress);

    useEffect(() => {
        const label = document.querySelector('[data-flipbook-label]');
        if (label) {
            label.textContent = scrollState.chapterLabel;
        }
    }, [scrollState.chapterLabel]);

    return (
        <ScrollContext.Provider value={{ ...scrollState, reducedMotion }}>
            {! reducedMotion && (
                <Suspense fallback={null}>
                    <CinematicCanvas />
                </Suspense>
            )}
            <FlipBookStage pages={pages} stageRef={stageRef} />
        </ScrollContext.Provider>
    );
}
