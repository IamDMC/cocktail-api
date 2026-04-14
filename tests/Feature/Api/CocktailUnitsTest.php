<?php

namespace Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CocktailUnitsTest extends \Tests\TestCase
{
    use RefreshDatabase;

    #[Test, Group('units'), Group('auth')]
    public function it_is_protected_from_unauthorized_access(): void
    {
        $this->getJson('/api/units')->assertUnauthorized();
    }

    #[Test, Group('units'), Group('auth')]
    public function it_requires_verified_user(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/units')->assertForbidden();
    }

    #[Test, Group('units'), Group('auth')]
    public function it_lists_all_units(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/units')
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'name',
                    'value'
                ]
            ]);

    }
}
