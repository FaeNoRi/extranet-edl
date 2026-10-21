<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Garde-fous de structure issus de l'audit d'accessibilité (WCAG 2.1 AA). L'audit
 * automatisé complet (axe-core) se joue dans un navigateur ; ces tests évitent les
 * régressions les plus simples.
 */
class AccessibiliteTest extends TestCase
{
    use RefreshDatabase;

    public function test_chaque_page_a_un_seul_h1_un_titre_descriptif_et_un_lien_d_evitement(): void
    {
        $pages = [
            [User::factory()->admin()->create(), route('admin.dashboard'), 'Administration'],
            [User::factory()->formateur()->create(), route('formateur.dashboard'), null],
            [User::factory()->stagiaireOp()->create(), route('stagiaire.dashboard'), 'Mon espace'],
        ];

        foreach ($pages as [$utilisateur, $url, $titre]) {
            $html = $this->actingAs($utilisateur)->get($url)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '<h1'), "Un seul h1 attendu sur {$url}");
            $this->assertStringContainsString('href="#contenu-principal"', $html);
            $this->assertStringContainsString('<main id="contenu-principal"', $html);
            $this->assertStringContainsString('lang="fr"', $html);
            if ($titre) {
                $this->assertMatchesRegularExpression('/<title>\s*'.preg_quote($titre, '/').' — /u', $html);
            }
        }
    }

    public function test_les_navigations_sont_nommees_pour_etre_distinguees(): void
    {
        $html = $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))->getContent();

        preg_match_all('/<nav\b[^>]*>/', $html, $navs);

        $this->assertGreaterThanOrEqual(3, count($navs[0]));
        foreach ($navs[0] as $nav) {
            $this->assertStringContainsString('aria-label=', $nav, "Navigation sans nom : {$nav}");
        }
    }

    public function test_les_pages_publiques_ont_un_repere_principal_et_un_seul_h1(): void
    {
        foreach ([route('login'), route('legal.confidentialite'), route('legal.accessibilite'), route('accueil')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('<main', $html, $url);
            $this->assertSame(1, substr_count($html, '<h1'), "Un seul h1 attendu sur {$url}");
        }
    }

    public function test_les_erreurs_de_formulaire_sont_recapitulees_et_annoncees(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->from(route('admin.referentiel.create'))
            ->followingRedirects()
            ->post(route('admin.referentiel.store'), [])
            ->assertSee('role="alert"', false)
            ->assertSee('Le formulaire contient des erreurs');
    }

    public function test_les_couleurs_de_marque_atteignent_le_contraste_minimum_sur_blanc(): void
    {
        $config = file_get_contents(base_path('tailwind.config.js'));

        foreach (['rose', 'vert-fonce', 'bleu', 'marron'] as $couleur) {
            $hex = $this->couleur($config, $couleur);
            $this->assertGreaterThanOrEqual(4.5, $this->contraste($hex, '#FFFFFF'), "Contraste insuffisant pour {$couleur} ({$hex}) sur blanc");
        }
    }

    public function test_les_teintes_de_texte_de_la_refonte_restent_lisibles_sur_leur_fond(): void
    {
        $config = file_get_contents(base_path('tailwind.config.js'));

        // [texte, couleur du fond teinté, opacité du fond sur blanc] (`bg-edl-*/10` ou `/15`).
        $paires = [
            ['bleu-fonce', 'bleu', 0.10],
            ['bleu-vert-fonce', 'bleu-vert', 0.15],
            ['vert-fonce', 'vert', 0.15],
            ['jaune-fonce', 'jaune', 0.15],
            ['orange-fonce', 'orange', 0.15],
            ['rose-fonce', 'rose', 0.10],
            ['violet-fonce', 'violet', 0.10],
            ['texte-doux', 'creme', 1.0],
            ['marron', 'creme', 1.0],
        ];

        foreach ($paires as [$texte, $fond, $opacite]) {
            $hexTexte = $this->couleur($config, $texte);
            $hexFond = $this->surBlanc($this->couleur($config, $fond), $opacite);
            $this->assertGreaterThanOrEqual(4.5, $this->contraste($hexTexte, $hexFond), "Contraste insuffisant : {$texte} sur {$fond}/{$opacite}");
        }
    }

    private function couleur(string $config, string $nom): string
    {
        preg_match("/[\s{]'?{$nom}'?: '(#[0-9A-Fa-f]{6})'/", $config, $m);
        $this->assertNotEmpty($m, "Couleur {$nom} introuvable");

        return $m[1];
    }

    /** Couleur obtenue en posant `$hex` à l'opacité donnée sur un fond blanc. */
    private function surBlanc(string $hex, float $opacite): string
    {
        $canaux = array_map(fn ($c) => (int) round(255 - (255 - hexdec($c)) * $opacite), str_split(ltrim($hex, '#'), 2));

        return sprintf('#%02X%02X%02X', ...$canaux);
    }

    private function luminance(string $hex): float
    {
        [$r, $g, $b] = array_map(fn ($c) => hexdec($c) / 255, str_split(ltrim($hex, '#'), 2));
        $f = fn ($c) => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $f($r) + 0.7152 * $f($g) + 0.0722 * $f($b);
    }

    private function contraste(string $a, string $b): float
    {
        $la = $this->luminance($a);
        $lb = $this->luminance($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }
}
