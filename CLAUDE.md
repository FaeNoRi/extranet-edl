# Extranet EDL+ — guide de contribution

Extranet de suivi pédagogique de l'**École des Langues Grand Calais** (stagiaires FPC/OP,
formateurs, administration). Application **Laravel 13**, front Blade + Alpine + Tailwind CSS v3.

Le cahier des charges d'origine n'est plus dans le dépôt : les migrations font foi pour le schéma et `tailwind.config.js` pour la palette de marque. Le dossier `CLAUDE/` est un simple dossier de transfert temporaire (ignoré par git, à ne pas utiliser comme stockage permanent ; médias dans `public/img`, polices dans `resources/fonts`).
La feuille de route est découpée en 7 phases (voir l'audit initial). **Phases 0 à 4 terminées**, **phase 5 en cours** (questionnaires + socle conformité faits ; export et registre RGPD faits ; audit d'accessibilité fait ; perf, sauvegardes et revue de sécurité faites ; reste l'effacement définitif, en attente des décisions de l'EDL). **Phase 6 (mise en production) : préparation faite** (`docs/deploiement.md`).

## Prérequis d'environnement (Windows / Laragon)

Le PHP par défaut du poste est **8.2** mais les dépendances exigent **PHP ≥ 8.4**.
Utiliser explicitement un binôme 8.4 de Laragon, p. ex. :

```
C:\laragon\bin\php\php-8.4.7-nts-Win32-vs17-x64\php.exe
```

Commandes types :

```sh
"C:\laragon\bin\php\php-8.4.7-nts-Win32-vs17-x64\php.exe" artisan test
"C:\laragon\bin\php\php-8.4.7-nts-Win32-vs17-x64\php.exe" artisan serve --port=8123
"C:\laragon\bin\php\php-8.4.7-nts-Win32-vs17-x64\php.exe" vendor/bin/pint
```

Front : `public/build` est **ignoré par git** et servi tel quel par `php artisan serve` (pas de
`vite dev` lancé). Après tout ajout de classes Tailwind dans les vues, relancer
`npm run build`, sinon les nouvelles classes n'existent pas dans le CSS servi.

> À faire : régler le PHP par défaut de Laragon sur 8.4 pour pouvoir utiliser `php` et les
> scripts Composer (`composer test`, `composer dev`) directement.

Base de données : MySQL `edl_plus` (dev) — **démarrer MySQL via Laragon** (« Démarrer tout »)
avant d'utiliser l'appli. Les tests tournent sur SQLite `:memory:` — garder les migrations
**portables** (pas de `->set()`, pas de type spécifique MySQL non émulé).

> Le `.env` local est en `SESSION_DRIVER=file` / `CACHE_STORE=file` : la page d'accueil, la
> connexion et les pages légales s'affichent même si MySQL est arrêté (utile après un crash
> Laragon). `.env.example` garde `database` (recommandation production). Toute page de données
> a quand même besoin de MySQL.

## Conventions

- **Style** : Laravel Pint (`pint.json`, preset `laravel`). `vendor/bin/pint` avant chaque commit.
- **Tests** : PHPUnit 12. Attributs (`#[DataProvider]`), pas d'annotations. `RefreshDatabase`.
- **Rôles** : enum `App\Enums\Role`. Cast sur `User::role`. Helpers `isAdmin()`, `isStagiaireFpc()`…
  Middleware `role:` (`->middleware('role:admin')`). `Gate::before` : l'admin a tous les droits.
- **Autorisations** : Policies dans `app/Policies` (auto-découvertes). Aucune suppression de
  données hors rôle admin (exigence CDC).
- **Auth** : connexion par **identifiant** (`login`), pas par e-mail. Pas d'inscription publique.
  Mots de passe créés/réinitialisés via `password_reset_tokens` (jeton + expiration + `used`),
  notification `PasswordSetupLink`, commande `edl:acces {login} [--nouveau]`.
- **Langue** : tout en français (UI, commentaires, libellés). `lang/fr/*`, `lang/fr.json`.
- **Config structure** : coordonnées / horaires / liens dans `config/edl.php`.
- **Couleurs** : classes Tailwind `edl-*` (`bg-edl-bleu`, `text-edl-rose`, …).
- **Journal des actions** : `spatie/laravel-activitylog`. Trait `App\Models\Concerns\Journalisable`
  (surcharger `$journalAttributs` pour restreindre les champs). Actif sur User, SessionFormation,
  Seance, Referentiel, Document.
- **Enums** : `App\Enums\Role`, `App\Enums\CodeProduit`.

## Modèle de données (phase 1)

Schéma refondu — migrations `2026_09_02_1200xx`. Après `git pull` : `php artisan migrate:fresh --seed`.

- `users` : e-mail **non unique** (1 accès = 1 session), `softDeletes`, champs formateur
  (`photo_path`, `presentation`, `formateur_fpc`, `formateur_op`).
- `session_formations` : `client_id`, `formateur_id`, `code_produit`, `rythme_op`, `distanciel`,
  `objectifs` (FPC), `dates_planning` (export brut) ; `session_jours` = planning jour par jour
  avec `actif` (décochage fériés/vacances).
- `seances` = fiche pédagogique : `session_formation_id`, `formateur_id`, `user_id` (rempli pour
  les fiches FPC individuelles, nul pour une séance de groupe OP), `objectifs`/`outils` en JSON,
  `fiche_pdf_path`. Pivots : `seances_referentiel` (modules), `seances_ressources`
  (`transmis` = visible par le stagiaire).
- `documents` : `categorie` (`presentation_structure` / `mes_documents`), `session_formation_id`
  nul = document commun structure.
- `emargements` (FPC distanciel), `questionnaires` + `questionnaire_questions` +
  `questionnaire_reponses` (formulaires en ligne — UI en phase 4).
- `referentiel` : trame réelle du CDC §3 chargée par `ReferentielSeeder` (52 entrées, idempotent).

## Import GESCOF (phase 2)

- Lecteur `App\Support\SpreadsheetReader` : `.xlsx` (sans dépendance) et `.csv`, en-têtes
  normalisées (sans accents/casse).
- `App\Support\CodeStage::analyser()` : dérive langue + FPC/OP + « stage -ST » depuis le
  code (`AN-OP-8-9`, `ES-FPC-AIS`, `AN-CLSH`…).
- `App\Services\Gescof\GescofImporter` : `simuler()` / `appliquer()` (transaction +
  rollback en simulation). Règles : exclut `AccesPlateforme≠Oui`, codes `-ST`, hors
  FPC/OP ; 1 login par (stagiaire, session) ; e-mail non unique ; « pas de suppression »
  → `session_formation_user.disparu_import_at`.
- Formateurs : `session_formations.formateur_id` (référent) + pivot
  `session_formation_formateur` (équipe). Appariement souple par nom ; non-reconnus =
  anomalie, à compléter dans le formulaire admin. `intervenants_import` conserve la
  chaîne brute (colonne libre, saisie humaine).
- Chaque exécution (simulation comprise) est tracée dans `gescof_imports` (rapport JSON).
- Commande : `php artisan edl:import-gescof <fichier> [--appliquer] [--envoyer-acces]`.
- Colonnes attendues : `Nom, Prenom, NomClient, CodeProduit, LibelleStage, NumSession,
  Email, ListeItv, AccesPlateforme` (compat. ancien `NomParticipant` unique).

## Back-office admin (phase 2)

Sous `/admin` (`role:admin`), layout `<x-admin.shell active="…">` (barre latérale) +
`<x-admin.card>`.

- **Import** (`admin.imports.*`) : upload → simulation (`GescofImportController@simuler`,
  fichier stocké dans `storage/app/gescof/`) → page rapport → `@appliquer` (réutilise le
  fichier, le supprime ensuite). Historique = table `gescof_imports`.
- **Formateurs** (`resource`, sans `show`) : CRUD, photo (`public` disk), `envoyer_acces`,
  archivage bloqué si rattaché à une session. `FormateurRequest` : FPC ou OP obligatoire.
- **Sessions** (`resource` complet) : CRUD ; le formulaire réconcilie les formateurs
  (référent + équipe, le référent est toujours dans le pivot avec `principal=true`) ;
  `admin.sessions.planning.sync` gère `session_jours` (ajout de dates + cases actif).
  `SessionFormationRequest` : rythme OP obligatoire si `code_produit=OP`.
- **Stagiaires** (`admin.stagiaires.index` + `destroy`) : liste filtrable (session,
  « absents du dernier import »), suppression (soft delete).
- **Référentiel** (`admin.referentiel.*`, `resource` sans `show`) : CRUD, liste groupée
  par module (filtrable), niveaux CECRL en cases à cocher (`App\Casts\SetCast`).
  Suppression bloquée si l'entrée est utilisée dans une séance (`cascadeOnDelete` sur
  `seances_referentiel`, on ne veut pas casser l'historique des fiches pédagogiques).
- **Journal** (`admin.journal.index`) : `activity_log` paginé, filtres objet/événement,
  diff old/new.
- **Purges** (`admin.purges.*`) : `PurgeComptesService` — comptes OP dont les sessions
  se sont terminées avant le 1er septembre ; comptes FPC terminés en N-1.
  `SessionFormation::finLe()` estime la fin (jours > séances > dates_planning > import).
  Soft delete + une entrée `activity_log` (log « Purge »). Commande
  `edl:purge-comptes [--op] [--fpc] [--appliquer]`, planifiée quotidiennement à 3h
  (OP appliquée automatiquement, FPC seulement signalée — validée dans l'UI).
  Config : `config('edl.purges')`.
- **Archive session FPC** (`admin.sessions.archive`) : `SessionArchiveService` produit un
  ZIP (un dossier par séance, fiche + ressources `date.RPn`, `MANIFESTE.txt` des fichiers
  attendus mais absents — fiches PDF en phase 3).
- **Séances** (`admin.seances.*`) : consultation/modification d'une fiche pédagogique
  depuis la fiche de session (liste dans une carte « Séances »). Réutilise les mêmes vues
  que l'espace formateur via des partials communs (`resources/views/seances/_show.blade.php`,
  `_form.blade.php`, paramétrées par un `$prefix` de route) et la même logique d'écriture
  (`App\Services\SeanceService`). Pas de création côté admin — la création reste réservée
  au formateur (`formateur.seances.create/store`), qui s'attribue la séance à l'enregistrement ;
  l'admin ne modifie donc jamais le `formateur_id` d'une séance existante.

`x-primary-button` est thématisé EDL (`bg-edl-bleu`).

## Espace formateur (phase 3)

Sous `/formateur` (`role:formateur`), layout `<x-formateur.shell>` (accent orange).
Accès limité par `SeancePolicy` / `sessionsPourFormateur()` (référent OU équipe).

- **Tableau de bord** : cartes des sessions, séances récentes/à venir.
- **Sessions** (`formateur.sessions.*`) : liste + fiche. Pour une session FPC, la fiche
  affiche le **suivi de progression** (séances regroupées par stagiaire). Pas de dépôt de
  ressources au niveau session : les fichiers se déposent uniquement depuis la fiche
  pédagogique (séance) ; la carte « Ressources de la session » reste en lecture/suppression.
- **Fiche pédagogique = séance** (`formateur.seances.*`) : formulaire complet (champs
  auto : stage, formateur, langue ; date, stagiaire si FPC, objectifs `OptionsSeance`
  + objectifs perso de la session, contenu, outils, sources, modules du référentiel,
  analyse). Fichiers déposés en `fichiers_transmis[]` / `fichiers_internes[]`
  → `Ressource` + pivot `seances_ressources.transmis`.
- À l'enregistrement : `FichePedagogiqueService` génère le **PDF** (`barryvdh/laravel-dompdf`,
  vue `pdf.fiche-pedagogique`) rangé dans `storage/app/private/seances/{id}/`. Régénéré
  à chaque modification. Téléchargement : `formateur.seances.fiche`.
- Le **dossier de séance** (page show) réunit résumé, ressources (transmis/travail) et
  fiches du référentiel des modules cochés.

## Espace stagiaire (phase 4)

Sous `/espace` (`role:stagiaire_op,stagiaire_fpc`), `<x-stagiaire.shell>` (accent rose).
Tout est cadré à `User::sessionStagiaire()` (1 accès = 1 session).

- **Tableau de bord** : bienvenue + nom, intitulé de session, formateur (+ présentation),
  bouton Teams si distanciel, **2 blocs documents** (`presentation_structure` communs /
  `mes_documents` de la session), planning (`session_jours` actifs).
- **Ressources pédagogiques** (`stagiaire.ressources.*`) : un dossier par séance
  **réalisée** (`date <= today`), FPC = ses séances individuelles + les séances de groupe.
  Le dossier montre les ressources **transmises** et les fiches du référentiel des modules
  vus (nommées `date.contenu`) ; jamais la fiche pédagogique PDF. Volet de visualisation
  (iframe, `?apercu=1` → `Storage::response`).
- **Téléchargements** : `TelechargementController` vérifie que le document/la ressource
  appartient bien à la session du stagiaire (ou est un document commun structure).
- **Stagiaire OP : consultation seule** (dissuasion, pas un verrou). `TelechargementController`
  ne sert jamais un OP en pièce jointe (même en forçant l'URL) et refuse la navigation directe
  vers le fichier (`Sec-Fetch-Dest` document/iframe/embed/object → 403, `Cache-Control: no-store`) :
  seuls les appels du lecteur passent (fetch, `<video>`, `<img>`). Lecteur
  `<x-stagiaire.apercu-durci>` (pages `stagiaire.documents.apercu` et `stagiaire.ressources.apercu`,
  shell `large`) : les PDF ne passent **pas** par le lecteur natif du navigateur (barre d'outils
  avec enregistrer/imprimer) mais sont dessinés sur canvas par PDF.js
  (`resources/js/lecteur-pdf.js`, entrée Vite dédiée, chargée uniquement sur ces pages), avec
  le filigrane `config('edl.structure.nom')` **incrusté dans le canvas** (jamais le nom de
  l'utilisateur). Durcissement : clic droit/sélection/copie bloqués, Ctrl+S/Ctrl+P bloqués,
  `print:hidden`, contenu flouté si la fenêtre perd le focus ou sur « Impr. écran ». Vidéo :
  `controlsList="nodownload"` ; types non affichables : message. Le volet de visualisation de
  `stagiaire.ressources.show` n'existe plus que pour les FPC (qui peuvent télécharger). Limites
  assumées : rien n'empêche une capture d'écran/photo, et les outils de développement du
  navigateur permettent de récupérer les octets.
- **Émargement** (`stagiaire.emargement`) : FPC distanciel uniquement, une séance réalisée
  → `Emargement` (present + signe_at).

## Documents (admin, complément phase 2)

`Admin\DocumentController` : `/admin/documents` gère les documents communs
(`presentation_structure`, `session_formation_id` nul) ; la fiche d'une session gère ses
`mes_documents`. Stockage disque privé, `typesStructure()` / `typesMesDocuments()`.

## Structure des espaces

`/tableau-de-bord` redirige vers le tableau de bord du rôle : `/admin`, `/formateur`, `/espace`.

## Questionnaires (phase 5)

Enums `TypeQuestionnaire` (satisfaction_chaud/froid, evaluation_acquis) et `TypeQuestion`
(texte, choix_unique, choix_multiple, echelle).

- **Admin** (`admin.questionnaires.*`) : constructeur (form + repeater de questions Alpine,
  `QuestionnaireRequest::questionsNormalisees()`), portée = session précise ou toutes
  (`session_formation_id` nul), page **résultats** agrégés (moyenne/histogramme échelle,
  comptes choix, liste textes).
- **Stagiaire** (`stagiaire.questionnaires.*`) : `Questionnaire::scopePourSession()`,
  formulaire, un seul envoi (`questionnaire_soumissions`, unique (questionnaire, user)),
  validation par question (obligatoire, échelle 1-5, options).

## Conformité (phase 5, socle)

- **En-têtes de sécurité** : `App\Http\Middleware\SecurityHeaders` (append au groupe web) —
  X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, HSTS en prod.
- **Pages légales** publiques : `/mentions-legales`, `/politique-de-confidentialite`,
  `/accessibilite` (`PageLegaleController`, `<x-legal.page>`), liens en pied de page.
  Contenu piloté par `config('edl.structure')` + `config('edl.legal')` — **à compléter par
  l'EDL** (SIRET, hébergeur, DPO…).
- **Uploads** : `App\Rules\FichierAutorise::regles()` (allowlist d'extensions +
  `config('edl.uploads')`), appliqué aux documents et ressources.

## RGPD (phase 5)

Sous `/admin/rgpd` (`Admin\RgpdController`, entrée « RGPD » de la barre latérale).

- **Registre des traitements** (`admin.rgpd.registre` + `.pdf`) : contenu dans
  `App\Support\RegistreTraitements` (7 traitements établis d'après le fonctionnement réel de
  l'appli ; durées reprises de `config('edl.legal')`). Présenté comme **projet à valider** tant que
  `EDL_REGISTRE_VALIDE_LE` est vide. Chaque traitement porte des « points d'attention » (décisions
  ouvertes : durée des comptes formateurs, émargements vs effacement, etc.). Garder ce fichier à jour
  quand l'appli collecte une nouvelle donnée ou ajoute un destinataire/sous-traitant.
- **Export des données d'un utilisateur** (`admin.rgpd.export`, bouton « Exporter » dans les listes
  Stagiaires et Formateurs) : `ExportDonneesUtilisateurService`, JSON téléchargeable (droit d'accès
  et portabilité). Sans secrets (mot de passe, jetons) ni données de tiers ; les modifications du
  journal ne sont reprises que si l'utilisateur est l'*objet* de l'action, pas son auteur. Fonctionne
  aussi pour un compte supprimé logiquement. Chaque export est journalisé (`activity('RGPD')`).
  **À étendre** à chaque nouvelle table portant une donnée personnelle.
- **Journal des actions** : purgé après 3 ans (`activitylog.clean_after_days` = 1095, commande
  `activitylog:clean` planifiée à 3h30) — c'est ce que promet la politique de confidentialité.
- **Référent RGPD** : `EDL_DPO_NOM` / `EDL_DPO_EMAIL` dans le `.env` de **chaque environnement**
  (volontairement absents du dépôt public) ; sans eux, la politique de confidentialité renvoie
  vers l'adresse générale de la structure. Les tests utilisent un référent fictif.
- **Imports GESCOF non appliqués** : `edl:purge-imports` (planifiée à 3h15) supprime les fichiers
  téléversés de plus de `edl.imports.conservation_fichier_jours` (7) jours, y compris orphelins ;
  la simulation reste consultable mais ne peut plus être appliquée.
- **Police** : Abel hébergée dans `resources/fonts` (aucun service tiers appelé par les pages).
- Reste (non fait) : effacement/anonymisation **définitifs** (la purge actuelle est un soft delete).

## Accessibilité (phase 5)

Audit du 2026-10-02 : axe-core (règles WCAG 2.1 A/AA + bonnes pratiques) piloté dans Edge headless sur
50 pages des 4 profils + pages publiques (bureau 1366 px et 375 px), 0 anomalie après correctifs ; clavier,
focus, 320 px et contrastes vérifiés à la main. Test avec NVDA concluant (octobre 2026) ; **VoiceOver pas testé.**
Conventions à garder :

- **Une page = un seul `<h1>`** : le titre du bandeau de `<x-app-layout>` (`<x-slot name="header"><h1>`) ;
  les titres de contenu sont des `<h2>`/`<h3>` sans saut de niveau. `<title>` = texte du bandeau + nom du site.
- **Repères** : lien d'évitement « Aller au contenu » → `<main id="contenu-principal">` (ne pas réutiliser
  l'id `contenu`, pris par des champs). Chaque `<nav>` a un `aria-label` distinct. Pages invité/légales :
  `<header>`/`<main>`/`<footer>`.
- **Couleurs** : texte jamais en `text-gray-400` (2,5:1) ; `text-gray-500` seulement sur fond blanc,
  `text-gray-600` sur le fond gris de la page. Rose (`#CC1966`) et vert foncé (`#177350`) ont été assombris
  par rapport à la charte d'origine (`#E31E73`, `#22A473`) pour atteindre 4,5:1 ; le texte sur fond orange
  est sombre (`text-gray-900`), jamais blanc. Un test (`AccessibiliteTest`) garde ces contrastes.
- **Focus** : `:focus-visible` forcé en bleu 2 px dans `app.css` (prime sur les `focus:outline-none`).
- **Formulaires** : tout champ a un `<label for>` ou un `aria-label` (filtres, champs fichier) ; les erreurs
  sont récapitulées par `<x-form-errors>` (`role="alert"`, dans les 3 shells) ; flashs `role="status"`/`alert`.
- **Boutons à état** : `aria-expanded` (menu utilisateur, « Voir plus » / « Réduire »).
- **Lecteur OP** : PDF dessiné sur canvas → illisible par un lecteur d'écran (compromis assumé avec la
  protection des contenus, signalé dans `/accessibilite`) ; la zone de défilement est focusable et nommée.
- Vues de pagination publiées dans `resources/views/vendor/pagination` (correctif ARIA du pied « Précédent »).

## Performance (phase 5)

- **Pas de N+1** : `Model::preventLazyLoading()` est actif hors production (`AppServiceProvider`) — une
  relation chargée à la volée dans une boucle lève une exception en dev/test. Charger les relations avec
  `with()` / `withCount()`. `SessionFormation::finLe()` réutilise les relations `jours`/`seances` déjà
  chargées (sinon 2 requêtes par session). `PerformanceTest` vérifie que le nombre de requêtes des pages
  principales (admin, formateur, stagiaire) ne croît pas avec le nombre de sessions (2 → 10).
- **Index** : migration `2026_10_06_100001_add_performance_indexes` (users role/deleted_at, code_produit,
  seances.date, ressources.created_at, activity_log.created_at). Les clés étrangères et pivots unique
  étaient déjà indexés.
- **Images** : `loading="lazy"` sur les logos de pied de page et la photo formateur.
- **En production** (à lancer à chaque déploiement) : `composer install --no-dev -o`, `npm run build`,
  `php artisan optimize` (config, routes, vues, événements en cache), OPcache activé côté PHP.

## Sauvegardes (phase 5)

`spatie/laravel-backup` (config : `config/backup.php`, procédure complète : `docs/sauvegardes.md`).

- **Contenu** : dump de la base + `storage/app/private` + `storage/app/public` (hors `private/gescof`,
  temporaire). **Pas le `.env`** (secrets à conserver à part : mot de passe d'archive, `APP_KEY`).
- **Planification** (`routes/console.php`) : `backup:run` 2h30, `backup:clean` 2h50, `backup:monitor` 8h.
  Alerte e-mail uniquement en cas d'échec / sauvegarde périmée (`BACKUP_NOTIFICATION_EMAIL`).
- **Archive chiffrée AES-256** (`BACKUP_ARCHIVE_PASSWORD`, obligatoire en production). Disque local `backups`
  (`storage/app/backups`) + disque hors-site facultatif `BACKUP_OFFSITE_DISK` (à déclarer dans `filesystems.php`).
- **Conservation 28 jours** (7 jours complets + 21 quotidiennes) : les archives contiennent aussi des comptes
  déjà purgés, donc durée courte, annoncée dans la politique de confidentialité (`edl.legal.conservation_sauvegardes`)
  et le registre — à valider avec l'EDL.
- **Windows** : `DB_DUMP_BINARY_PATH` (dossier de `mysqldump`) avec des `/`, sinon `mysqldump` échoue.
  Les chemins de `config/backup.php` normalisent les séparateurs (sinon les chemins relatifs de l'archive sont faux).
- Restauration testée le 2026-10-06 (30 tables, mêmes nombres de lignes) ; à refaire avant la mise en service puis
  chaque trimestre. `SauvegardeTest` couvre config, planification et un aller-retour chiffré.

## Sécurité (revue du 2026-10-07)

Revue du code (autorisations, uploads, authentification, en-têtes, dépendances). Garde-fous dans `SecuriteTest`.

- **Fiches pédagogiques** (`SeanceRequest` / `SeanceService`) : une ressource réutilisée doit appartenir à la **même
  session** (sinon un formateur pouvait exposer aux stagiaires les fichiers d'une autre formation en devinant un
  id) ; le stagiaire d'une fiche FPC doit être **inscrit à la session** ; réenregistrer une fiche **conserve le
  statut transmis / non transmis** des fichiers déjà rattachés (avant : les documents de travail devenaient
  visibles des stagiaires à chaque modification).
- **Fichiers affichés à l'écran** (`App\Support\ReponseFichier::enLigne`, aperçu FPC + lecteur OP) : seul un PDF,
  une image, un son ou une vidéo **détecté dans le contenu** est servi en ligne (415 sinon) ; un HTML renommé en
  `.jpg` ne devient plus une page de l'extranet. `nosniff` ; hors PDF, `Content-Security-Policy: sandbox`
  (pas pour les PDF : le visualiseur du navigateur refuse un document sandboxé).
- **CSP** (`SecurityHeaders::POLITIQUE_CONTENU`) : tout en `'self'`, aucun hôte tiers. `'unsafe-eval'` pour Alpine,
  `'wasm-unsafe-eval'` pour PDF.js. Vérifiée dans Edge (visionneuse OP, Alpine, constructeur de questionnaires).
  **Toute nouvelle ressource externe (CDN, police, analytics) sera bloquée** : l'ajouter consciemment à la CSP **et**
  à la politique de confidentialité. Ni `<script>` ni gestionnaire en ligne (`onclick`, `onsubmit`, `onchange`…) dans les vues : ils sont bloqués en silence (déconnexion cassée, suppressions sans confirmation). Utiliser Alpine (`x-data @submit="if (! confirm(…)) $event.preventDefault()"`, `x-on:click.prevent`) ; un test de `SecuriteTest` le vérifie.
- **Pages connectées** : `Cache-Control: no-store, private` sur le HTML (poste partagé : le bouton « Précédent »
  après déconnexion ne ré-affiche rien).
- **Authentification** : mots de passe **10 caractères minimum, lettres + chiffres** (`Password::defaults()`) ;
  demandes de lien limitées **par compte** (3 / 30 min) en plus de l'IP (5 / min) ; cookie de session `Secure`
  par défaut en production ; `TRUSTED_PROXIES` (env) pour un hébergeur derrière un reverse proxy ; https forcé en production.
- Questionnaires : réponses texte plafonnées (5000), choix unique/multiple validés contre les options.
- **Dépendances** : `composer audit` / `npm audit` dans la CI (job `audit`) + Dependabot hebdomadaire.
- Vérifié sans objet : aucun `{!! !!}`, aucun SQL brut, `$request->all()` jamais passé au modèle.

## Déploiement (phase 6, préparation)

Procédure complète : `docs/deploiement.md` ; modèle de config : `.env.production.example`.

- **`php artisan edl:preflight`** (`PreflightService`) : contrôle bloquant / à traiter avant ouverture et après
  chaque déploiement (production, debug coupé, HTTPS, cookie sécurisé, migrations, compte de démo, e-mail réel,
  droits d'écriture, chiffrement + `mysqldump` + hors-site des sauvegardes, cron, mentions légales, OPcache,
  limites d'upload). Ajouter un contrôle ici quand une nouvelle exigence d'exploitation apparaît.
- **`php artisan edl:creer-admin`** : crée le premier administrateur (mot de passe aléatoire inconnu, lien envoyé
  par e-mail). `DatabaseSeeder` ne charge que le référentiel en production : **ne jamais lancer `UserSeeder`/`DemoSeeder`**
  (compte `admin` / `password`).
- **`scripts/deployer.sh <tag>`** : sauvegarde, maintenance, `git checkout`, composer, assets, migrations, caches,
  preflight. Pas de `migrate:rollback` en production : restaurer la sauvegarde.
- **Cron** `schedule:run` indispensable (sauvegardes, purges) ; un témoin de vie est écrit chaque minute dans le cache
  (`edl:planificateur:dernier-passage`) et lu par le preflight.
- Pages d'erreur françaises (`resources/views/errors`), `robots.txt` en `Disallow: /`.

## Reste (phase 5-6)

- RGPD : effacement/anonymisation définitifs d'un utilisateur (voir section RGPD).
- Accessibilité : test VoiceOver (NVDA fait).
- Stockage de sauvegarde hors serveur (pCloud envisagé, via WebDAV : à valider avec un compte de test ; sous-traitant à déclarer au registre).
- Phase 6 : choix de l'hébergement et du service d'e-mail, déploiement, test interne EDL (1 session OP + 1 FPC), puis évolutions (familles, fusion plann'EDL, notifications).
