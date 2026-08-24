import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'Georgia', 'serif'],
            },
            colors: {
                ink: '#12141a',
                paper: '#f6f1e8',
                ember: '#ff5a3c',
                notion: {
                    text: '#16141c',
                    muted: 'rgba(22, 20, 28, 0.62)',
                    faint: 'rgba(22, 20, 28, 0.42)',
                    hover: 'rgba(22, 20, 28, 0.06)',
                    line: 'rgba(22, 20, 28, 0.10)',
                    sidebar: '#12141a',
                    board: '#efe8db',
                    blue: '#e24b32',
                },
            },
            boxShadow: {
                notion: '0 24px 80px -32px rgba(18, 20, 26, 0.45)',
                card: '0 1px 0 rgba(18,20,26,0.06), 0 12px 32px -18px rgba(18,20,26,0.25)',
                glow: '0 0 0 1px rgba(255,90,60,0.25), 0 18px 50px -20px rgba(255,90,60,0.45)',
            },
            borderRadius: {
                notion: '10px',
            },
        },
    },

    plugins: [forms],
};
