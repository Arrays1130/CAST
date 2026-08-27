import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/landing.jsx',
            ],
            refresh: true,
        }),
        react(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    three: ['three', '@react-three/fiber', '@react-three/drei', '@react-three/postprocessing'],
                    gsap: ['gsap'],
                    motion: ['motion'],
                },
            },
        },
    },
});
