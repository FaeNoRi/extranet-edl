<?php

namespace App\Support;

/**
 * Contenu du registre des activités de traitement (art. 30 du RGPD), établi
 * à partir de ce que l'application fait réellement. Les bases légales et les
 * durées proposées doivent être validées par le responsable de traitement :
 * tant que `edl.legal.registre_valide_le` est vide, le registre est présenté
 * comme un projet. Les « points d'attention » sont des écarts ou décisions
 * ouvertes à arbitrer, pas du texte juridique.
 */
class RegistreTraitements
{
    /** @return array<string, mixed> */
    public static function donnees(): array
    {
        $structure = config('edl.structure');
        $legal = config('edl.legal');

        return [
            'valide_le' => $legal['registre_valide_le'] ?: null,

            'responsable' => [
                'nom' => $structure['nom'],
                'forme' => $structure['forme_juridique'],
                'adresse' => $structure['adresse'],
                'email' => $structure['email'],
                'siret' => $structure['siret'],
                'dpo' => $legal['dpo_nom'] && $legal['dpo_email'] ? $legal['dpo_nom'].' — '.$legal['dpo_email'] : null,
            ],

            'hebergeur' => $legal['hebergeur'],

            'transferts' => 'Aucun transfert de données personnelles hors de l\'Union européenne n\'est '
                .'prévu par l\'application. Aucun service de mesure d\'audience ni de publicité n\'est utilisé.',

            'destinataires_techniques' => [
                'L\'hébergeur ci-dessus (sous-traitant) héberge l\'application et sa base de données.',
                'Aucun autre service tiers n\'est appelé par les pages (la police de caractères est hébergée sur l\'extranet).',
            ],

            'securite' => [
                'Authentification par identifiant et mot de passe (mot de passe stocké sous forme d\'empreinte, jamais en clair).',
                'Accès cloisonné par rôle (administrateur, formateur, stagiaire) et par session de formation.',
                'Stagiaires OP : consultation des documents à l\'écran uniquement (pas de téléchargement, filigrane).',
                'Connexion chiffrée (HTTPS) et en-têtes de sécurité HTTP en production.',
                'Contrôle des fichiers déposés (types et tailles autorisés) ; stockage des fichiers hors du dossier public.',
                'Journalisation des actions de l\'administration et des formateurs.',
            ],

            'points_generaux' => [
                'Sauvegardes et procédure de restauration : à formaliser avant la mise en production.',
                'Effacement définitif : la suppression d\'un compte est aujourd\'hui « logique » (le compte disparaît '
                    .'de l\'application mais ses données restent en base) ; l\'effacement ou l\'anonymisation définitifs '
                    .'restent à mettre en place.',
            ],

            'traitements' => [
                [
                    'nom' => 'Gestion des comptes et authentification',
                    'finalite' => 'Créer et gérer les accès des stagiaires, des formateurs et des administrateurs ; authentifier les utilisateurs.',
                    'base_legale' => 'Stagiaires : exécution de la convention de formation (art. 6.1.b). '
                        .'Formateurs et administrateurs : intérêt légitime de la structure à organiser son activité (art. 6.1.f).',
                    'personnes' => 'Stagiaires (OP et FPC), formateurs, administrateurs.',
                    'donnees' => 'Nom, prénom, identifiant, adresse e-mail de contact, rôle, mot de passe (empreinte), '
                        .'dates de création et de modification ; pour les formateurs : photo et texte de présentation (facultatifs).',
                    'origine' => 'Export GESCOF (stagiaires) ; saisie par l\'administration (formateurs, administrateurs) ; photo et présentation fournies par le formateur.',
                    'destinataires' => 'Administration ; formateurs (pour les stagiaires de leurs sessions) ; stagiaires (identité et présentation du formateur de leur session).',
                    'conservation' => [
                        'Stagiaires OP : '.$legal['conservation_op'],
                        'Stagiaires FPC : '.$legal['conservation_fpc'],
                        'Formateurs et administrateurs : durée de leur intervention, puis archivage du compte.',
                    ],
                    'points_attention' => [
                        'Durée de conservation des comptes formateurs et administrateurs à fixer.',
                        'La photo du formateur, visible des stagiaires, relève plutôt du consentement : à recueillir et tracer.',
                    ],
                ],
                [
                    'nom' => 'Suivi pédagogique',
                    'finalite' => 'Planifier les sessions et les séances, tenir les fiches pédagogiques, mettre des ressources à disposition des stagiaires et suivre leur avancement.',
                    'base_legale' => 'Exécution de la convention de formation (art. 6.1.b).',
                    'personnes' => 'Stagiaires, formateurs.',
                    'donnees' => 'Rattachement à une session, séances (date, objectifs, contenu, outils, sources, analyse du formateur), '
                        .'fiches individuelles des stagiaires FPC, modules du référentiel consultés, ressources déposées.',
                    'origine' => 'Saisie par les formateurs et l\'administration.',
                    'destinataires' => 'Stagiaire concerné ; formateurs de la session ; administration.',
                    'conservation' => ['Avec le compte du stagiaire (voir ci-dessus).'],
                    'points_attention' => [
                        'Les fiches pédagogiques PDF sont rangées dans le stockage privé : à inclure lors d\'un effacement définitif.',
                    ],
                ],
                [
                    'nom' => 'Émargement (formations FPC à distance)',
                    'finalite' => 'Attester de la présence des stagiaires aux séances de formation.',
                    'base_legale' => 'Obligation légale liée à la formation professionnelle (art. 6.1.c) : justificatifs de réalisation de l\'action de formation.',
                    'personnes' => 'Stagiaires FPC en formation à distance.',
                    'donnees' => 'Stagiaire, séance, présence, date et heure de signature, commentaire.',
                    'origine' => 'Le stagiaire (signature en ligne).',
                    'destinataires' => 'Administration ; formateurs ; financeurs et organismes de contrôle sur demande.',
                    'conservation' => ['Durée des obligations légales et contractuelles de conservation des justificatifs de formation.'],
                    'points_attention' => [
                        'Durée exacte à confirmer (financeurs, certification qualité).',
                        'Un effacement définitif du compte supprimerait aussi ses émargements : à arbitrer avec l\'obligation de conservation.',
                    ],
                ],
                [
                    'nom' => 'Questionnaires de satisfaction et d\'évaluation',
                    'finalite' => 'Recueillir la satisfaction à chaud et à froid et évaluer les acquis, pour l\'amélioration continue de la formation.',
                    'base_legale' => 'Intérêt légitime d\'amélioration de la qualité (art. 6.1.f) et exigences de la certification qualité.',
                    'personnes' => 'Stagiaires.',
                    'donnees' => 'Réponses aux questions, date de soumission.',
                    'origine' => 'Les stagiaires.',
                    'destinataires' => 'Administration.',
                    'conservation' => ['Avec le compte du stagiaire (voir ci-dessus).'],
                    'points_attention' => [
                        'Les réponses sont nominatives dans la base : décider si elles doivent être anonymisées après exploitation.',
                    ],
                ],
                [
                    'nom' => 'Import GESCOF',
                    'finalite' => 'Créer et mettre à jour les comptes stagiaires et les sessions à partir de l\'export du logiciel de gestion GESCOF.',
                    'base_legale' => 'Exécution de la convention de formation (art. 6.1.b).',
                    'personnes' => 'Stagiaires.',
                    'donnees' => 'Nom, prénom, adresse e-mail, client, code et libellé du stage, intervenants ; rapport d\'import (anomalies).',
                    'origine' => 'Fichier exporté de GESCOF par l\'administration.',
                    'destinataires' => 'Administrateurs.',
                    'conservation' => ['Fichier importé supprimé après application de l\'import ; rapport d\'import conservé.'],
                    'points_attention' => [
                        'Durée de conservation du rapport d\'import à fixer (il peut citer des noms dans les anomalies).',
                    ],
                ],
                [
                    'nom' => 'Journal des actions',
                    'finalite' => 'Assurer la sécurité et la traçabilité : savoir qui a modifié quoi et quand.',
                    'base_legale' => 'Intérêt légitime de sécurité et de traçabilité (art. 6.1.f).',
                    'personnes' => 'Administrateurs, formateurs et personnes dont les données sont modifiées.',
                    'donnees' => 'Auteur de l\'action, nature de l\'action, objet concerné, valeurs avant et après modification, date.',
                    'origine' => 'Générées automatiquement par l\'application.',
                    'destinataires' => 'Administrateurs.',
                    'conservation' => [$legal['conservation_journal']],
                    'points_attention' => [],
                ],
                [
                    'nom' => 'Connexions techniques',
                    'finalite' => 'Maintenir la session authentifiée d\'un utilisateur et sécuriser l\'accès.',
                    'base_legale' => 'Intérêt légitime de sécurité (art. 6.1.f).',
                    'personnes' => 'Tous les utilisateurs.',
                    'donnees' => 'Identifiant de session, adresse IP, navigateur utilisé, dernière activité.',
                    'origine' => 'Générées automatiquement à la connexion.',
                    'destinataires' => 'Administrateurs techniques.',
                    'conservation' => ['Durée de la session : '.config('session.lifetime').' minutes d\'inactivité.'],
                    'points_attention' => [],
                ],
            ],
        ];
    }
}
