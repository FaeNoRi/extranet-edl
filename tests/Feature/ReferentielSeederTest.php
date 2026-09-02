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

        $this->assertSame(52, Referentiel::count());
        $this->assertSame(6, Referentiel::module('Bases')->count());
        $this->assertSame(17, Referentiel::module('Grammaire')->count());
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(ReferentielSeeder::class);
        $this->seed(ReferentielSeeder::class);

        $this->assertSame(52, Referentiel::count());
    }

    public function test_niveaux_are_cast_to_an_array(): void
    {
        $this->seed(ReferentielSeeder::class);

        $entry = Referentiel::where('code', 'C-C5')->firstOrFail();
        $this->assertSame(['A1', 'A2', 'B1'], $entry->niveaux);

        $sansNiveau = Referentiel::where('code', 'A-C1')->firstOrFail();
        $this->assertSame([], $sansNiveau->niveaux);
    }
}
