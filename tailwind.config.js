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
                    // Refonte d'octobre 2026 (« trop froid ») : fond de page et teintes de texte foncées,
                    // lisibles (4,5:1) sur blanc comme sur les fonds teintés `bg-edl-*/10`.
                    fond: '#EDF4F8', // fond de page bleu très clair (retour EDL : le crème a été écarté)
                    sable: '#EDE4DA',
                    'texte-doux': '#6B5F58',
                    'bleu-fonce': '#0F5272',
                    'bleu-vert-fonce': '#0E5E62',
                    'jaune-fonce': '#5C4A00',
                    'orange-fonce': '#8A3B07',
                    'rose-fonce': '#A3134F',
                    'violet-fonce': '#7E1862',
                },
            },
        },
    },

    plugins: [forms],
};
