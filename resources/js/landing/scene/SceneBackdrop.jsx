import { SCENE_NAMES } from '../lib/constants.js';

export default function SceneBackdrop() {
    return (
        <div className="landing-cinematic-backdrops" aria-hidden="true">
            {SCENE_NAMES.map((scene, index) => (
                <span
                    key={scene}
                    className={`landing-cinematic-backdrop landing-cinematic-backdrop--${scene}`}
                    data-scene-backdrop={index}
                />
            ))}
        </div>
    );
}
