import { motion } from 'motion/react';

const LINES = [
    { width: '82%', delay: 0 },
    { width: '68%', delay: 0.35 },
    { width: '88%', delay: 0.7 },
    { width: '58%', delay: 1.05 },
];

export default function InkTrail({ active, reducedMotion = false }) {
    if (! active) {
        return null;
    }

    return (
        <div className="landing-ink-trail" aria-hidden="true">
            {LINES.map((line, index) => (
                <motion.i
                    key={index}
                    initial={{ width: reducedMotion ? line.width : 0, opacity: 0.12 }}
                    animate={{
                        width: line.width,
                        opacity: [0.12, 0.38, 0.22],
                    }}
                    transition={reducedMotion ? { duration: 0 } : {
                        duration: 2.6,
                        repeat: Infinity,
                        delay: line.delay,
                        ease: [0.65, 0, 0.35, 1],
                    }}
                />
            ))}
        </div>
    );
}
