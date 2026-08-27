import InkTrail from './InkTrail.jsx';
import { WritingHandSvg } from './HandSvg.jsx';

export default function WritingHand({ opacity = 1, active = false, reducedMotion = false }) {
    return (
        <div
            className={`landing-work-actor landing-work-actor--write${active ? ' is-active' : ''}`}
            data-write-actor
            style={{ opacity }}
        >
            <span className="landing-work-label sr-only">Nagsusulat</span>
            <div className="landing-hand-wrap">
                <WritingHandSvg reducedMotion={reducedMotion} />
            </div>
            <InkTrail active={active && ! reducedMotion} reducedMotion={reducedMotion} />
        </div>
    );
}
