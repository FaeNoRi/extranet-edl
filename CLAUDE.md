# Extranet EDL+ — guide de contribution

Extranet de suivi pédagogique de l'**École des Langues Grand Calais** (stagiaires FPC/OP,
formateurs, administration). Application **Laravel 13**, front Blade + Alpine + Tailwind CSS v3.

Le cahier des charges, la palette de marque et le schéma SQL de référence sont dans `CLAUDE/`.
La feuille de route est découpée en 7 phases (voir l'audit initial). **Phase en cours : 1.**

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

> À faire : régler le PHP par défaut de Laragon sur 8.4 pour pouvoir utiliser `php` et les
> scripts Composer (`composer test`, `composer dev`) directement.

Base de données : MySQL `edl_plus` (dev). Les tests tournent sur SQLite `:memory:` — garder
les migrations **portables** (pas de `->set()`, pas de type spécifique MySQL non émulé).

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

## Structure des espaces

`/tableau-de-bord` redirige vers le tableau de bord du rôle : `/admin`, `/formateur`, `/espace`.
Ces groupes de routes sont des points d'ancrage à remplir dans les phases suivantes.
