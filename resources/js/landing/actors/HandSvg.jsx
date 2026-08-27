import { motion } from 'motion/react';

export function WritingHandSvg({ reducedMotion = false }) {
    return (
        <svg viewBox="0 0 200 160" className="landing-hand-svg" aria-hidden="true">
            <motion.g
                animate={reducedMotion ? { x: 78, y: 52 } : {
                    x: [12, 34, 78, 84, 118],
                    y: [24, 42, 42, 66, 66],
                }}
                transition={reducedMotion ? { duration: 0 } : {
                    duration: 2.6,
                    repeat: Infinity,
                    ease: [0.65, 0, 0.35, 1],
                }}
            >
                <path
                    d="M34 92 C28 72 38 52 58 48 C72 46 82 58 78 72 L74 88 C70 98 58 104 48 98 Z"
                    fill="none"
                    stroke="rgba(18,20,26,0.35)"
                    strokeWidth="2.2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />
                <path
                    d="M48 56 C56 44 68 46 74 56"
                    fill="none"
                    stroke="rgba(18,20,26,0.28)"
                    strokeWidth="2"
                    strokeLinecap="round"
                />
                <line x1="112" y1="34" x2="112" y2="78" stroke="#12141a" strokeWidth="3" strokeLinecap="round" />
                <path d="M108 78 L118 92 L112 78 Z" fill="#ff5a3c" opacity="0.9" />
            </motion.g>
        </svg>
    );
}

export function CheckingHandSvg({ reducedMotion = false }) {
    return (
        <svg viewBox="0 0 200 160" className="landing-hand-svg" aria-hidden="true">
            <motion.g
                animate={reducedMotion ? { x: 92, y: 56 } : {
                    x: [16, 62, 66, 102],
                    y: [26, 32, 58, 62],
                }}
                transition={reducedMotion ? { duration: 0 } : {
                    duration: 2.9,
                    repeat: Infinity,
                    ease: [0.65, 0, 0.35, 1],
                }}
            >
                <path
                    d="M38 94 C32 74 42 54 62 50 C76 48 86 60 82 74 L78 90 C74 100 62 106 52 100 Z"
                    fill="none"
                    stroke="rgba(18,20,26,0.35)"
                    strokeWidth="2.2"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />
                <path
                    d="M52 58 C60 46 72 48 78 58"
                    fill="none"
                    stroke="rgba(18,20,26,0.28)"
                    strokeWidth="2"
                    strokeLinecap="round"
                />
                <line x1="116" y1="38" x2="116" y2="82" stroke="#ea580c" strokeWidth="3.5" strokeLinecap="round" />
                <ellipse cx="116" cy="86" rx="4" ry="2.5" fill="#ea580c" opacity="0.75" />
            </motion.g>
        </svg>
    );
}
