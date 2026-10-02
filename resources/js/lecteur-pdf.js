// Lecteur « durci » des stagiaires OP : dissuasion contre la récupération des documents.
// Les PDF sont dessinés page par page sur des canvas (PDF.js) au lieu d'utiliser le
// lecteur natif du navigateur, dont la barre d'outils offre enregistrement et impression.
// Aucun mécanisme web ne bloque une capture d'écran : ce sont des freins, pas un verrou.

function durcir(conteneur) {
    const bloquer = (e) => e.preventDefault();

    ['contextmenu', 'selectstart', 'dragstart', 'copy', 'cut'].forEach((evt) => conteneur.addEventListener(evt, bloquer));

    const flouter = (actif) => conteneur.classList.toggle('lecteur-flou', actif);

    window.addEventListener('blur', () => flouter(true));
    window.addEventListener('focus', () => flouter(false));
    document.addEventListener('visibilitychange', () => flouter(document.hidden));

    const viderPressePapiers = () => navigator.clipboard?.writeText('').catch(() => {});

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && ['p', 's'].includes(e.key.toLowerCase())) {
            e.preventDefault();
        }
        if (e.key === 'PrintScreen') {
            flouter(true);
            viderPressePapiers();
        }
    });

    document.addEventListener('keyup', (e) => {
        if (e.key === 'PrintScreen') {
            viderPressePapiers();
            setTimeout(() => flouter(!document.hasFocus()), 1500);
        }
    });
}

function dessinerFiligrane(ctx, largeur, hauteur, texte, ratio) {
    const diagonale = Math.hypot(largeur, hauteur);

    ctx.save();
    ctx.globalAlpha = 0.14;
    ctx.fillStyle = '#000';
    ctx.font = `600 ${15 * ratio}px sans-serif`;
    ctx.translate(largeur / 2, hauteur / 2);
    ctx.rotate((-25 * Math.PI) / 180);

    const pasX = ctx.measureText(texte).width + 70 * ratio;
    const pasY = 130 * ratio;

    for (let y = -diagonale, ligne = 0; y < diagonale; y += pasY, ligne++) {
        for (let x = -diagonale + (ligne % 2) * (pasX / 2); x < diagonale; x += pasX) {
            ctx.fillText(texte, x, y);
        }
    }

    ctx.restore();
}

async function lecteurPdf(racine) {
    const zone = racine.querySelector('[data-zone]');
    const etat = racine.querySelector('[data-etat]');
    const infoPage = racine.querySelector('[data-page-info]');

    try {
        const pdfjs = await import('pdfjs-dist/build/pdf.min.mjs');
        const worker = (await import('pdfjs-dist/build/pdf.worker.min.mjs?url')).default;
        pdfjs.GlobalWorkerOptions.workerSrc = worker;

        const pdf = await pdfjs.getDocument({
            url: racine.dataset.url,
            withCredentials: true,
            disableRange: true,
            disableStream: true,
        }).promise;

        const premiere = await pdf.getPage(1);
        const largeurNative = premiere.getViewport({ scale: 1 }).width;
        const hauteurNative = premiere.getViewport({ scale: 1 }).height;
        const ajusterEchelle = () => Math.min(2.5, Math.max(0.5, (zone.clientWidth - 32) / largeurNative));
        let echelle = ajusterEchelle();

        etat.remove();

        const pages = [];
        const visibles = new Set();

        for (let n = 1; n <= pdf.numPages; n++) {
            const div = document.createElement('div');
            div.className = 'relative mx-auto mb-4 bg-white shadow';
            const canvas = document.createElement('canvas');
            canvas.className = 'block';
            div.appendChild(canvas);
            zone.appendChild(div);
            pages.push({ n, div, canvas, tache: null, rendue: false });
        }

        const dimensionner = (p, largeur, hauteur) => {
            p.div.style.width = `${largeur}px`;
            p.div.style.height = `${hauteur}px`;
        };

        const mesurer = () => pages.forEach((p) => dimensionner(p, largeurNative * echelle, hauteurNative * echelle));

        const rendre = async (p) => {
            p.tache?.cancel();
            const page = await pdf.getPage(p.n);
            const ratio = window.devicePixelRatio || 1;
            const vue = page.getViewport({ scale: echelle });

            p.canvas.width = Math.floor(vue.width * ratio);
            p.canvas.height = Math.floor(vue.height * ratio);
            p.canvas.style.width = `${vue.width}px`;
            p.canvas.style.height = `${vue.height}px`;
            dimensionner(p, vue.width, vue.height);

            const ctx = p.canvas.getContext('2d');
            p.tache = page.render({ canvasContext: ctx, viewport: page.getViewport({ scale: echelle * ratio }) });

            try {
                await p.tache.promise;
                // Le filigrane est incrusté dans l'image : il survit à une capture d'écran.
                dessinerFiligrane(ctx, p.canvas.width, p.canvas.height, racine.dataset.filigrane, ratio);
                p.rendue = true;
            } catch (e) {
                if (e?.name !== 'RenderingCancelledException') throw e;
            }
        };

        const observateur = new IntersectionObserver(
            (entrees) => {
                entrees.forEach((entree) => {
                    const p = pages.find((x) => x.div === entree.target);
                    if (entree.isIntersecting) {
                        visibles.add(p);
                        if (!p.rendue) rendre(p);
                    } else {
                        visibles.delete(p);
                    }
                });
            },
            { root: zone, rootMargin: '800px 0px' },
        );

        mesurer();
        pages.forEach((p) => observateur.observe(p.div));

        const majInfo = () => {
            const haut = zone.scrollTop + zone.clientHeight / 3;
            const courante = pages.filter((p) => p.div.offsetTop <= haut).length || 1;
            infoPage.textContent = `${courante} / ${pdf.numPages}`;
        };
        let attente = false;
        zone.addEventListener('scroll', () => {
            if (attente) return;
            attente = true;
            requestAnimationFrame(() => {
                attente = false;
                majInfo();
            });
        });
        majInfo();

        const allerA = (n) => {
            const p = pages[Math.min(pdf.numPages, Math.max(1, n)) - 1];
            zone.scrollTo({ top: p.div.offsetTop - 8, behavior: 'smooth' });
        };
        const pageCourante = () => Math.max(1, pages.filter((p) => p.div.offsetTop <= zone.scrollTop + zone.clientHeight / 3).length);

        const zoomer = (nouvelle) => {
            const n = pageCourante();
            echelle = Math.min(3, Math.max(0.5, nouvelle));
            pages.forEach((p) => {
                p.rendue = false;
            });
            mesurer();
            visibles.forEach((p) => rendre(p));
            allerA(n);
        };

        racine.querySelector('[data-action="precedent"]').addEventListener('click', () => allerA(pageCourante() - 1));
        racine.querySelector('[data-action="suivant"]').addEventListener('click', () => allerA(pageCourante() + 1));
        racine.querySelector('[data-action="zoom-moins"]').addEventListener('click', () => zoomer(echelle / 1.2));
        racine.querySelector('[data-action="zoom-plus"]').addEventListener('click', () => zoomer(echelle * 1.2));
        racine.querySelector('[data-action="ajuster"]').addEventListener('click', () => zoomer(ajusterEchelle()));
    } catch (e) {
        console.error(e);
        if (etat) etat.textContent = 'Impossible d’afficher ce document.';
    }
}

document.querySelectorAll('[data-lecteur-durci]').forEach(durcir);
document.querySelectorAll('[data-lecteur-pdf]').forEach(lecteurPdf);
