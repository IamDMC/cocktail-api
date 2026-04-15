<?php

namespace Cocktail\Api;

use App\Models\Cocktail;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CocktailDestroyTest extends \Tests\Feature\Cocktail\CocktailTestCase
{
    #[Test, Group('cocktails'), Group('auth')]
    public function it_is_protected_from_unauthorized_access(): void
    {
        $response = $this->deleteJson('/api/cocktails/1');

        $response->assertUnauthorized();
    }

    #[Test, Group('cocktails'), Group('auth')]
    public function it_requires_verified_user(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->deleteJson('/api/cocktails/1');

        $response->assertForbidden();
    }

    #[Test, Group('cocktails')]
    public function it_deletes_cocktail(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->deleteJson('/api/cocktails/1');

        $response->assertNoContent();
    }

    #[Test, Group('cocktails')]
    public function it_only_allows_delete_on_user_owned_cocktail(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Sanctum::actingAs($userA);

        Cocktail::factory()->create([
            'user_id' => $userB->id
        ]);

        $response = $this->deleteJson('/api/cocktails/1');

        $response->assertForbidden();
    }
}
