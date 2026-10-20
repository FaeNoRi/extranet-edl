<?php

namespace Tests\Feature;

use App\Models\Referentiel;
use Database\Seeders\ReferentielSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferentielSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_the_full_trame(): void
    {
        $this->seed(ReferentielSeeder::class);

        // Trame v3 (octobre 2026) : 108 entrées en anglais sur 8 modules.
        $this->assertSame(108, Referentiel::count());
        $this->assertSame(108, Referentiel::where('langue', 'Anglais')->count());
        $this->assertSame(7, Referentiel::module('Bases')->count());
        $this->assertSame(39, Referentiel::module('Vocabulaire')->count());
        $this->assertSame(Referentiel::MODULES, array_values(array_unique(array_column(ReferentielSeeder::trame(), 0))));
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(ReferentielSeeder::class);
        $this->seed(ReferentielSeeder::class);

        $this->assertSame(108, Referentiel::count());
    }

    public function test_niveaux_et_badges_sont_charges(): void
    {
        $this->seed(ReferentielSeeder::class);

        $comp3 = Referentiel::where('code', 'Comp3')->firstOrFail();
        $this->assertSame(['essentiel', 'consolidation'], $comp3->niveaux);
        $this->assertSame(['COM', 'RH', 'ORAL'], $comp3->badges);

        $voc8 = Referentiel::where('code', 'Voc8')->firstOrFail();
        $this->assertSame([], $voc8->niveaux);
        $this->assertSame([], $voc8->badges);
    }

    public function test_les_corrections_du_fichier_source_sont_appliquees(): void
    {
        $codes = ReferentielSeeder::codes();

        $this->assertSame(count($codes), count(array_unique($codes)), 'Code en double dans la trame');
        foreach (['B5', 'Comp4', 'Voc7', 'Voc40', 'Voc42'] as $supprime) {
            $this->assertNotContains($supprime, $codes);
        }

        $this->seed(ReferentielSeeder::class);
        $this->assertSame('Sciences', Referentiel::where('code', 'Voc29')->value('contenu'));
        $this->assertSame('Médias', Referentiel::where('code', 'Voc23')->value('contenu'));
        $this->assertSame('Discours indirect', Referentiel::where('code', 'Cons9')->value('contenu'));
        $this->assertSame('Present perfect simple', Referentiel::where('code', 'Conj6')->value('contenu'));
    }
}
