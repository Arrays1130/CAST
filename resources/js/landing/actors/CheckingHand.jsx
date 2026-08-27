import { motion } from 'motion/react';
import { CheckingHandSvg } from './HandSvg.jsx';

const CHECKS = [0, 1, 2];

export default function CheckingHand({ opacity = 1, active = false, reducedMotion = false }) {
    return (
        <div
            className={`landing-work-actor landing-work-actor--check${active ? ' is-active' : ''}`}
            data-check-actor
            style={{ opacity }}
        >
            <span className="landing-work-label sr-only">Nagche-check</span>
            <div className="landing-hand-wrap">
                <CheckingHandSvg reducedMotion={reducedMotion} />
            </div>
            <div className="landing-work-checks">
                {CHECKS.map((i) => (
                    <span key={i} className="landing-work-check" style={{ '--i': i }}>
                        <motion.svg viewBox="0 0 24 24" className="landing-check-svg" aria-hidden="true">
                            <motion.path
                                d="M4 12 L10 18 L20 6"
                                fill="none"
                                stroke="#059669"
                                strokeWidth="3"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                initial={{ pathLength: reducedMotion ? 1 : 0, opacity: reducedMotion ? 0.85 : 0 }}
                                animate={active ? {
                                    pathLength: 1,
                                    opacity: reducedMotion ? 0.85 : [0, 1, 0.85],
                                } : { pathLength: 0, opacity: 0 }}
                                transition={reducedMotion ? { duration: 0 } : {
                                    duration: 2.8,
                                    repeat: Infinity,
                                    delay: 0.45 + i * 0.55,
                                    ease: [0.65, 0, 0.35, 1],
                                }}
                            />
                        </motion.svg>
                    </span>
                ))}
            </div>
        </div>
    );
}
