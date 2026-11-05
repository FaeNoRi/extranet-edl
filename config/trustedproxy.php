<?php

/*
 * Proxys de confiance, lus par le middleware TrustProxies à chaque requête. Derrière le reverse
 * proxy de l'hébergeur, sans eux, l'appli croit être en HTTP (liens, cookies sécurisés, HSTS) et
 * voit l'adresse du proxy au lieu de celle du visiteur (limites de tentatives faussées).
 * TRUSTED_PROXIES = adresses/CIDR séparés par des virgules, ou « * ».
 *
 * Valeur lue ici, et non par env() dans bootstrap/app.php : une fois la configuration mise en
 * cache (php artisan optimize), le .env n'est plus chargé et env() y renvoie null.
 */
return [
    'proxies' => env('TRUSTED_PROXIES') ?: null,
];
