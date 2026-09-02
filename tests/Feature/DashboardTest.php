<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    #[DataProvider('roleRoutes')]
    public function test_each_role_lands_on_its_own_dashboard(string $roleState, string $expectedRoute): void
    {
        $user = User::factory()->{$roleState}()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route($expectedRoute));
    }

    public static function roleRoutes(): array
    {
        return [
            'admin' => ['admin', 'admin.dashboard'],
            'formateur' => ['formateur', 'formateur.dashboard'],
            'stagiaire FPC' => ['stagiaireFpc', 'stagiaire.dashboard'],
        ];
    }

    public function test_a_stagiaire_cannot_reach_the_admin_area(): void
    {
        $user = User::factory()->stagiaireFpc()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_an_admin_can_reach_the_admin_area(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->get('/admin')->assertOk();
    }
}
