<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function pagesAdmin(): array
    {
        return [
            ['admin.dashboard'],
            ['admin.imports.index'],
            ['admin.sessions.index'],
            ['admin.sessions.create'],
            ['admin.formateurs.index'],
            ['admin.formateurs.create'],
            ['admin.stagiaires.index'],
            ['admin.purges.index'],
            ['admin.journal.index'],
        ];
    }

    #[DataProvider('pagesAdmin')]
    public function test_un_non_admin_est_refuse(string $route): void
    {
        $this->actingAs(User::factory()->formateur()->create())
            ->get(route($route))
            ->assertForbidden();
    }

    #[DataProvider('pagesAdmin')]
    public function test_l_admin_accede(string $route): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route($route))
            ->assertOk();
    }

    public function test_le_lien_administration_n_apparait_que_pour_l_admin(): void
    {
        $this->actingAs(User::factory()->stagiaireOp()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('stagiaire.dashboard'));

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertSee('Administration');
    }
}
