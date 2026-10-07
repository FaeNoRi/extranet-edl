<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Tout vient de l'extranet lui-même : aucune ressource tierce (cohérent avec la politique de
     * confidentialité). 'unsafe-eval' est requis par Alpine.js, 'wasm-unsafe-eval' par PDF.js ;
     * les styles en ligne servent aux attributs style de quelques vues.
     */
    public const POLITIQUE_CONTENU = "default-src 'self'; script-src 'self' 'unsafe-eval' 'wasm-unsafe-eval'; "
        ."style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'; media-src 'self' blob:; "
        ."worker-src 'self' blob:; connect-src 'self'; frame-src 'self'; object-src 'none'; "
        ."base-uri 'self'; form-action 'self'; frame-ancestors 'self'";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $entetes = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Content-Security-Policy' => self::POLITIQUE_CONTENU,
        ];

        // Pages connectées : jamais conservées par le navigateur. Sur un poste partagé, le bouton
        // « Précédent » après la déconnexion ne doit pas ré-afficher les données d'un autre compte.
        if ($request->user() && str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        if ($request->secure() || app()->environment('production')) {
            $entetes['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($entetes as $cle => $valeur) {
            if (! $response->headers->has($cle)) {
                $response->headers->set($cle, $valeur);
            }
        }

        return $response;
    }
}
