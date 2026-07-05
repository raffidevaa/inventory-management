import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Telkomsel-inspired brand red (600 = primary #ec1c24)
                brand: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#f9a8a8',
                    400: '#f36b6b',
                    500: '#ea3b3f',
                    600: '#ec1c24',
                    700: '#c50f16',
                    800: '#a21015',
                    900: '#861418',
                    950: '#490708',
                },
            },
        },
    },

    plugins: [forms],
};
