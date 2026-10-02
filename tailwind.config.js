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
                sans: ['Abel', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Palette de marque EDL
                edl: {
                    'bleu-vert': '#41B9BF',
                    jaune: '#ECBA03',
                    rose: '#CC1966', // assombri depuis #E31E73 (contraste 4,5:1 requis, WCAG 1.4.3)
                    violet: '#A52280',
                    orange: '#FF8F43',
                    vert: '#79BD6F',
                    bleu: '#156C93',
                    marron: '#544741',
                    gris: '#58595C',
                    'vert-fonce': '#177350', // assombri depuis #22A473 (contraste, WCAG 1.4.3)
                },
            },
        },
    },

    plugins: [forms],
};
