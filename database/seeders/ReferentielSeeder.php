<?php

namespace Database\Seeders;

use App\Models\Referentiel;
use Illuminate\Database\Seeder;

/**
 * Trame du référentiel EDL au 19/01/2026 (cahier des charges §3).
 * Le référentiel étant « en cours de finalisation », ces données sont
 * destinées à être révisées via l'administration.
 */
class ReferentielSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->trame() as [$module, $code, $contenu, $niveaux]) {
            Referentiel::updateOrCreate(
                ['code' => $code],
                ['module' => $module, 'contenu' => $contenu, 'niveaux' => $niveaux],
            );
        }
    }

    /**
     * @return array<int, array{0:string,1:string,2:string,3:array<int,string>}>
     */
    private function trame(): array
    {
        return [
            ['Bases', 'B-C1', 'Culture pays anglo-saxons', ['A1']],
            ['Bases', 'B-C2', 'Salutations', ['A1']],
            ['Bases', 'B-C3', 'Se présenter', ['A1']],
            ['Bases', 'B-C4', 'Chiffres / dates / heures', ['A1']],
            ['Bases', 'B-C5', 'Construction de phrase', ['A1']],
            ['Bases', 'B-C6', 'Like / dislike', ['A1']],

            ['Conjugaison', 'C-C1', 'BE et HAVE', ['A1']],
            ['Conjugaison', 'C-C2', 'Présent simple / -ING', ['A1']],
            ['Conjugaison', 'C-C3', 'Les temps futurs et passés', ['A1']],
            ['Conjugaison', 'C-C4', 'Les modaux', ['A1', 'A2', 'B1']],
            ['Conjugaison', 'C-C5', 'Prétérit simple / -ING', ['A1', 'A2', 'B1']],
            ['Conjugaison', 'C-C6', 'Présent perfect simple / -ING', ['A2', 'B1']],
            ['Conjugaison', 'C-C7', 'Les verbes irréguliers', ['A2', 'B1']],
            ['Conjugaison', 'C-C8', 'Le conditionnel', ['A2', 'B1']],
            ['Conjugaison', 'C-C9', 'Les temps complexes (futur antérieur, plus-que-parfait, subjonctif...)', ['B1', 'B2']],

            ['Grammaire', 'G-C1', 'Les pronoms', ['A1']],
            ['Grammaire', 'G-C2', 'Les adverbes', ['A1']],
            ['Grammaire', 'G-C3', 'Mots interrogatifs', ['A1']],
            ['Grammaire', 'G-C4', 'Possession', ['A1']],
            ['Grammaire', 'G-C5', 'Articles (the, a/an)', ['A1']],
            ['Grammaire', 'G-C6', 'Le pluriel des noms', ['A1']],
            ['Grammaire', 'G-C7', 'Les adjectifs', ['A1']],
            ['Grammaire', 'G-C8', 'La quantité', ['A1']],
            ['Grammaire', 'G-C9', 'Les prépositions', ['A1']],
            ['Grammaire', 'G-C10', 'La fréquence', ['A2', 'B1']],
            ['Grammaire', 'G-C11', 'La comparaison', ['A2', 'B1']],
            ['Grammaire', 'G-C12', 'Les verbes à particules', ['A2', 'B1']],
            ['Grammaire', 'G-C13', 'La voix passive', ['A2', 'B1']],
            ['Grammaire', 'G-C14', "L'hypothèse", ['A2', 'B1']],
            ['Grammaire', 'G-C15', 'Les pronoms relatifs (auquel, lesquels...)', ['B1', 'B2']],
            ['Grammaire', 'G-C16', 'La mise en relief', ['C1', 'C2']],
            ['Grammaire', 'G-C17', 'Phrases verbales', []],

            ['Prononciation', 'P-C1', 'Phonétique, prononciation, accents', ['A2', 'B1', 'C1', 'C2']],
            ['Prononciation', 'P-C2', 'Intonation, débit', ['A2', 'B1', 'C1', 'C2']],

            ['Methodologie', 'M-C1', "Rédaction d'e-mails / lettres / messages", ['A2', 'B1']],
            ['Methodologie', 'M-C2', 'Donner son avis', ['A2', 'B1']],
            ['Methodologie', 'M-C3', 'Commenter', ['B1', 'B2']],
            ['Methodologie', 'M-C4', 'Faire un exposé, un compte rendu, un commentaire', ['B1', 'B2']],
            ['Methodologie', 'M-C5', 'Rédaction de CV et lettres', ['C1', 'C2']],
            ['Methodologie', 'M-C6', "Rédiger en s'adaptant aux différents styles", ['C1', 'C2']],
            ['Methodologie', 'M-C7', 'Rédaction de toutes sortes de documents', ['C1', 'C2']],
            ['Methodologie', 'M-C8', 'Réaliser des présentations', ['C1', 'C2']],
            ['Methodologie', 'M-C9', 'Utiliser différents registres de langage', ['C1', 'C2']],

            ['Vocabulaire', 'V-C1', 'Famille, travail, quotidien, vêtements, nourriture, loisirs, sentiments', ['A1']],
            ['Vocabulaire', 'V-C2', 'Temps et espace, logement, météo, pays/villes, argent, transports, événements, médias', ['A2', 'B1']],
            ['Vocabulaire', 'V-C3', "Sujets culturels et d'actualité, faits de société, système scolaire, psychologie, enrichissement lexical", ['B1', 'B2']],
            ['Vocabulaire', 'V-C4', 'Expressions idiomatiques, proverbes, faux amis', ['C1', 'C2']],

            ['Au Quotidien', 'A-C1', 'Animaux', []],
            ['Au Quotidien', 'A-C2', 'Météo', []],
            ['Au Quotidien', 'A-C3', "L'heure", []],
            ['Au Quotidien', 'A-C4', 'Noël', []],
            ['Au Quotidien', 'A-C5', 'Halloween', []],
        ];
    }
}
