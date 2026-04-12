<?php

namespace Api;

use App\Models\Cocktail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CocktailFavoredByTest extends TestCase
{
    use RefreshDatabase;

    #[Test, Group('cocktails'), Group('cocktail-favored-by')]
    public function it_adds_cocktail_to_user_favorites(): void
    {
        $user = User::factory()->create();

        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->postJson("/api/favorite/cocktails/{$cocktail->id}");

        $response->assertNoContent();

        $this->assertDatabaseCount('user_cocktail', 1);

        $this->assertDatabaseHas('user_cocktail', [
            'cocktail_id' => $cocktail->id,
            'user_id' => $user->id
        ]);
    }

    #[Test, Group('cocktails'), Group('cocktail-favored-by')]
    public function it_removes_cocktail_from_user_favorites(): void
    {
        $user = User::factory()->create();

        $cocktail = Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $cocktail->favoredBy()->attach([$user->id]);

        $response = $this->deleteJson("/api/favorite/cocktails/{$cocktail->id}");

        $response->assertNoContent();

        $this->assertDatabaseCount('user_cocktail', 0);
    }
}
