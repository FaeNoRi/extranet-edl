<?php

namespace Database\Seeders;

use App\Models\Referentiel;
use Illuminate\Database\Seeder;

/**
 * Trame du référentiel EDL — version 3 (fichier « Update Référentiel v3 061026 », octobre 2026).
 * Entrées en anglais ; niveaux EDL ; badges multiples.
 *
 * Corrections appliquées au fichier d'origine : lignes marquées « supprimée » ignorées
 * (B5, Comp4, Voc7, Voc40, Voc42) ; second « Voc23 » (sciences) renommé Voc29 ; « discourt
 * indirect » → « Discours indirect » ; accents (présent, prétérit, négation, impératif,
 * prépositions) ; majuscule en début de contenu ; badges en majuscules.
 *
 * Sur une base existante, charger avec `php artisan edl:referentiel-charger` (archive l'ancienne
 * trame) plutôt qu'avec ce seeder seul.
 */
class ReferentielSeeder extends Seeder
{
    public const LANGUE = 'Anglais';

    public function run(): void
    {
        foreach (self::trame() as [$module, $code, $contenu, $niveaux, $badges]) {
            Referentiel::updateOrCreate(
                ['code' => $code, 'langue' => self::LANGUE],
                ['module' => $module, 'contenu' => $contenu, 'niveaux' => $niveaux, 'badges' => $badges, 'archive_le' => null],
            );
        }
    }

    /** @return array<int, string> codes de la trame */
    public static function codes(): array
    {
        return array_column(self::trame(), 1);
    }

    /**
     * @return array<int, array{0:string,1:string,2:string,3:array<int,string>,4:array<int,string>}>
     */
    public static function trame(): array
    {
        return [
            // Bases
            ['Bases', 'B1', 'Alphabet', ['essentiel'], ['JUNIOR']],
            ['Bases', 'B2', 'Syntaxe', ['essentiel', 'consolidation', 'perfectionnement'], ['JUNIOR']],
            ['Bases', 'B3', 'Consigne de classe', ['essentiel'], ['JUNIOR']],
            ['Bases', 'B4', 'Nombres', ['essentiel'], ['JUNIOR']],
            ['Bases', 'B6', 'Date', ['essentiel'], ['JUNIOR']],
            ['Bases', 'B7', 'Heure', ['essentiel'], ['JUNIOR']],
            ['Bases', 'B8', 'Salutations et politesse', ['essentiel', 'consolidation', 'perfectionnement'], ['JUNIOR']],

            // Conjugaison
            ['Conjugaison', 'Conj1', 'BE et HAVE', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Conjugaison', 'Conj2', 'Présent simple', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Conjugaison', 'Conj3', 'Présent continu', ['essentiel'], ['JUNIOR']],
            ['Conjugaison', 'Conj4', 'Prétérit simple', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Conjugaison', 'Conj5', 'Prétérit continu', ['essentiel', 'consolidation', 'perfectionnement'], []],
            ['Conjugaison', 'Conj6', 'Present perfect simple', ['consolidation'], []],
            ['Conjugaison', 'Conj7', 'Present perfect continu', ['consolidation'], []],
            ['Conjugaison', 'Conj8', 'Past perfect', ['essentiel', 'consolidation', 'perfectionnement'], []],
            ['Conjugaison', 'Conj9', 'Futur', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Conjugaison', 'Conj10', 'Conditionnel', ['essentiel', 'consolidation', 'perfectionnement'], []],
            ['Conjugaison', 'Conj11', 'Modaux', ['essentiel', 'consolidation', 'perfectionnement'], []],
            ['Conjugaison', 'Conj12', 'Impératif', ['essentiel', 'consolidation'], []],
            ['Conjugaison', 'Conj13', 'Voix passive', ['essentiel', 'consolidation', 'perfectionnement'], []],
            ['Conjugaison', 'Conj14', 'There is/are', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Conjugaison', 'Conj15', 'Verbes irréguliers', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Conjugaison', 'Conj16', 'Présent simple ou continu', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Conjugaison', 'Conj17', 'Prétérit simple ou continu', ['essentiel', 'consolidation'], []],
            ['Conjugaison', 'Conj18', 'Prétérit ou present perfect', ['essentiel', 'consolidation'], []],

            // Grammaire
            ['Grammaire', 'Gram1', 'Articles', ['essentiel'], ['JUNIOR']],
            ['Grammaire', 'Gram2', 'Pluriel', ['essentiel'], ['JUNIOR']],
            ['Grammaire', 'Gram3', 'Quantité', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Grammaire', 'Gram4', 'Noms composés', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Grammaire', 'Gram5', 'Pronoms', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Grammaire', 'Gram6', 'Possession', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Grammaire', 'Gram7', 'Adjectifs', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Grammaire', 'Gram8', 'Adverbes', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Grammaire', 'Gram9', 'Intensifier', ['essentiel', 'consolidation', 'perfectionnement'], ['JUNIOR']],
            ['Grammaire', 'Gram10', 'Comparatif', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Grammaire', 'Gram11', 'Superlatif', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Grammaire', 'Gram12', 'Prépositions', ['essentiel', 'consolidation', 'perfectionnement'], ['JUNIOR']],
            ['Grammaire', 'Gram13', 'Phrasal verbs', ['essentiel', 'consolidation'], ['ORAL']],
            ['Grammaire', 'Gram14', 'Positions', ['essentiel', 'consolidation', 'perfectionnement'], []],

            // Construction de phrases
            ['Construction de phrases', 'Cons1', 'Interrogation', ['essentiel'], ['JUNIOR']],
            ['Construction de phrases', 'Cons2', 'Négation', ['essentiel'], ['JUNIOR']],
            ['Construction de phrases', 'Cons3', 'Liaison / coordination', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Construction de phrases', 'Cons4', 'Cause', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Construction de phrases', 'Cons5', 'Conséquence', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Construction de phrases', 'Cons6', 'But', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Construction de phrases', 'Cons7', 'Opposition/ concession', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Construction de phrases', 'Cons8', 'Gérondif ou infinitif', ['essentiel', 'consolidation'], []],
            ['Construction de phrases', 'Cons9', 'Discours indirect', ['essentiel', 'consolidation'], []],
            ['Construction de phrases', 'Cons10', 'Mise en relief', ['essentiel', 'consolidation', 'perfectionnement'], []],

            // Prononciation
            ['Prononciation', 'P1', 'Phonétique, prononciation, accents', ['essentiel'], ['ORAL']],

            // Compétences
            ['Compétences', 'Comp1', 'Exprimer goût', ['essentiel', 'consolidation'], ['JUNIOR', 'ORAL']],
            ['Compétences', 'Comp2', 'Exprimer opinion', ['essentiel', 'consolidation'], ['ORAL']],
            ['Compétences', 'Comp3', 'Accord/ désaccord', ['essentiel', 'consolidation'], ['COM', 'RH', 'ORAL']],
            ['Compétences', 'Comp5', 'Négocier', ['essentiel', 'consolidation', 'perfectionnement'], ['COM', 'RH', 'ORAL']],
            ['Compétences', 'Comp6', 'Rédiger', ['consolidation', 'perfectionnement'], ['COM', 'RH', 'ORAL']],
            ['Compétences', 'Comp7', 'Adapter le registre', ['consolidation', 'perfectionnement'], ['ORAL', 'COM']],
            ['Compétences', 'Comp8', 'Appel', ['essentiel', 'consolidation', 'perfectionnement'], ['ORAL']],
            ['Compétences', 'Comp9', 'Déplacements et voyages', ['essentiel', 'consolidation'], ['DEPL', 'TOUR']],
            ['Compétences', 'Comp10', 'Gérer un problème', ['consolidation', 'perfectionnement'], ['ORAL', 'COM', 'LOG']],
            ['Compétences', 'Comp11', 'CV/entretien', ['consolidation'], ['RH']],
            ['Compétences', 'Comp12', 'Conflit/ diplomatie', ['consolidation', 'perfectionnement'], ['RH', 'ORAL']],
            ['Compétences', 'Comp13', 'Commandes', ['consolidation', 'perfectionnement'], ['COM', 'LOG']],
            ['Compétences', 'Comp14', 'Transaction', ['essentiel', 'consolidation'], ['COM']],
            ['Compétences', 'Comp15', 'Marchandise', ['consolidation'], ['LOG']],
            ['Compétences', 'Comp16', 'Présenter', ['essentiel', 'consolidation', 'perfectionnement'], ['COM', 'RH', 'ORAL']],
            ['Compétences', 'Comp17', 'Réunion', ['essentiel', 'consolidation', 'perfectionnement'], ['COM', 'RH', 'ORAL']],
            ['Compétences', 'Comp18', 'Ventes et marketing', ['essentiel', 'consolidation', 'perfectionnement'], ['COM']],

            // Vocabulaire
            ['Vocabulaire', 'Voc1', 'Famille', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Vocabulaire', 'Voc2', 'Corps', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Vocabulaire', 'Voc3', 'Vêtements', ['essentiel', 'consolidation'], ['JUNIOR', 'COM']],
            ['Vocabulaire', 'Voc4', 'Couleurs', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Vocabulaire', 'Voc5', 'Formes', ['essentiel', 'consolidation'], ['JUNIOR']],
            ['Vocabulaire', 'Voc6', 'Émotions', ['essentiel', 'consolidation'], ['JUNIOR', 'ORAL']],
            ['Vocabulaire', 'Voc8', 'Routines', [], []],
            ['Vocabulaire', 'Voc9', 'Loisirs et sports', [], []],
            ['Vocabulaire', 'Voc10', 'École et matériel', [], []],
            ['Vocabulaire', 'Voc11', 'Maison', [], []],
            ['Vocabulaire', 'Voc12', 'Ville', [], []],
            ['Vocabulaire', 'Voc13', 'Animaux', [], []],
            ['Vocabulaire', 'Voc14', 'Nourriture', [], []],
            ['Vocabulaire', 'Voc15', 'Météo', [], []],
            ['Vocabulaire', 'Voc16', 'Saisons', [], []],
            ['Vocabulaire', 'Voc17', 'Fêtes et traditions', [], []],
            ['Vocabulaire', 'Voc18', 'Transports', [], ['DEPL', 'TOUR', 'LOG']],
            ['Vocabulaire', 'Voc19', 'Voyage', [], ['DEPL', 'TOUR']],
            ['Vocabulaire', 'Voc20', 'Shopping', [], ['TOUR', 'COM']],
            ['Vocabulaire', 'Voc21', 'Santé', [], []],
            ['Vocabulaire', 'Voc22', 'Technologies', [], []],
            ['Vocabulaire', 'Voc23', 'Médias', [], []],
            ['Vocabulaire', 'Voc24', 'Commerce', [], ['COM']],
            ['Vocabulaire', 'Voc25', 'Nature et environnement', [], []],
            ['Vocabulaire', 'Voc26', 'Société et actualité', [], []],
            ['Vocabulaire', 'Voc27', 'Travail', [], []],
            ['Vocabulaire', 'Voc28', 'Art et culture', [], []],
            ['Vocabulaire', 'Voc29', 'Sciences', [], []],
            ['Vocabulaire', 'Voc30', 'Pays anglo-saxons', [], ['TOUR']],
            ['Vocabulaire', 'Voc31', 'Faux amis', [], []],
            ['Vocabulaire', 'Voc32', 'Synonymes/antonymes', [], []],
            ['Vocabulaire', 'Voc33', 'Expressions idiomatiques', [], []],
            ['Vocabulaire', 'Voc34', 'Noël', [], []],
            ['Vocabulaire', 'Voc35', 'Pâques', [], []],
            ['Vocabulaire', 'Voc36', 'Halloween', [], []],
            ['Vocabulaire', 'Voc37', 'Thanksgiving', [], []],
            ['Vocabulaire', 'Voc38', 'RH', [], ['RH']],
            ['Vocabulaire', 'Voc39', 'Logistique', [], ['LOG']],
            ['Vocabulaire', 'Voc43', 'Tourisme', [], ['TOUR']],

            // Préparation au test CLOE
            ['Préparation au test CLOE', 'Preptest1', 'Guide du candidat', [], ['ORAL']],
            ['Préparation au test CLOE', 'Preptest2', 'Exercices', [], ['ORAL']],
        ];
    }
}
