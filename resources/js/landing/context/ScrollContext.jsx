import { createContext, useContext } from 'react';

export const ScrollContext = createContext({
    progress: 0,
    sceneFloat: 0,
    activeScene: 0,
    reducedMotion: false,
    writeOpacity: 0,
    checkOpacity: 0,
    chapterLabel: 'Chapter 1',
    isFinal: false,
});

export function useScrollState() {
    return useContext(ScrollContext);
}
