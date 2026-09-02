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
            },
            colors: {
                // Palette de marque EDL (cf. CLAUDE/edl_plus_couleurs.txt)
                edl: {
                    'bleu-vert': '#41B9BF',
                    jaune: '#ECBA03',
                    rose: '#E31E73',
                    violet: '#A52280',
                    orange: '#FF8F43',
                    vert: '#79BD6F',
                    bleu: '#156C93',
                    marron: '#544741',
                    gris: '#58595C',
                    'vert-fonce': '#22A473',
                },
            },
        },
    },

    plugins: [forms],
};
