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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                brand: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Fondos y superficies oscuras
                ink: {
                    950: '#0a0a0c',
                    900: '#0f0f12',
                    850: '#16161a',
                    800: '#1c1c22',
                    700: '#26262e',
                    600: '#33333d',
                    500: '#4a4a56',
                },
                // Acento dorado
                gold: {
                    200: '#f0e2b0',
                    300: '#e6cd7a',
                    400: '#d4af37',
                    500: '#c19a2e',
                    600: '#a07f22',
                    700: '#806419',
                },
            },
            boxShadow: {
                gold: '0 10px 30px -10px rgba(212, 175, 55, 0.25)',
            },
        },
    },

    plugins: [forms],
};
