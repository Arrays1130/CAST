import SceneBackdrop from './SceneBackdrop.jsx';
import FlipBookOverlay from './FlipBookOverlay.jsx';
import WritingHand from '../actors/WritingHand.jsx';
import CheckingHand from '../actors/CheckingHand.jsx';
import { useScrollState } from '../context/ScrollContext.jsx';

export default function FlipBookStage({ pages, stageRef }) {
    const { activeScene, writeOpacity, checkOpacity, reducedMotion } = useScrollState();

    return (
        <div className="landing-flipbook-stage" data-flipbook ref={stageRef} aria-label="Interactive CAST manuscript">
            <SceneBackdrop />
            <div className="landing-flipbook-rig" data-flipbook-rig>
                <div className="landing-flipbook-shadow" data-flipbook-shadow aria-hidden="true" />
                <FlipBookOverlay pages={pages} />
                <div className="landing-work-actors" aria-hidden="true">
                    <WritingHand
                        opacity={writeOpacity}
                        active={activeScene <= 1}
                        reducedMotion={reducedMotion}
                    />
                    <CheckingHand
                        opacity={checkOpacity}
                        active={activeScene >= 2 && activeScene <= 3}
                        reducedMotion={reducedMotion}
                    />
                </div>
            </div>
        </div>
    );
}
