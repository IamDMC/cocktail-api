<?php

namespace Tests\Feature\Cocktail\Api;

use App\Models\Cocktail;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Cocktail\CocktailTestCase;

class CocktailShowTest extends CocktailTestCase
{
    /**
     * @return array<int, array{
     *     cocktailData: array<string, mixed>,
     *     cocktail: Cocktail
     * }>
     */
    private function makeMultipleCocktails(int $nrCocktails = 3): array
    {
        $cocktails = [];

        for ($i = 0; $i < $nrCocktails; $i++) {
            $cocktails[$i]['cocktailData'] = $this->makeCocktail($this->user);
            $cocktails[$i]['cocktail'] = $this->createCocktail($cocktails[$i]['cocktailData']);
        }

        return $cocktails;
    }
    #[Test, Group('cocktails'), Group('auth')]
    public function it_is_protected_from_unauthorized_access(): void
    {
        $cocktails = $this->makeMultipleCocktails(8);

        /** @var Cocktail $cocktail */
        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}");

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

        $response = $this->getJson("/api/cocktails/1");

        $response->assertForbidden();
    }

    #[Test, Group('cocktails')]
    public function it_shows_cocktail(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        /** @var Cocktail $cocktail */
        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_public',
                ],
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_returns_404_if_cocktail_not_found(): void
    {
        Sanctum::actingAs($this->user);
        $this->makeMultipleCocktails(2);

        $response = $this->getJson("/api/cocktails/999"); // Invalid data

        $response->assertNotFound();
    }

    #[Test, Group('cocktails')]
    public function it_includes_user(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include[]=user");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'user' => ['id', 'email', 'name']
                ],
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_categories(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include[]=categories");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'categories' => [
                        '*' => ['id', 'name', 'description']
                    ]
                ],
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_steps(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include[]=steps");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'steps' => [
                        '*' => ['id', 'step_number', 'instruction']
                    ]
                ],
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_ingredients(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include[]=ingredients");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'ingredients' => [
                        '*' => ['id', 'name', 'amount', 'unit']
                    ]
                ],
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_ratings(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $user = User::factory()->create();

        $user->ratings()->create([
            'cocktail_id' => $cocktail->id,
            'rating' => rand(1,5),
            'comment' => fake()->sentence(2),
        ]);

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include[]=ratings.user");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'ratings' => [
                        '*' => [
                            'id',
                            'rating',
                            'comment',
                            'user' => ['id', 'email', 'name']
                        ]
                    ]
                ],
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_favored_by(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $user = User::factory()->create();

        $user->favoriteCocktails()->attach([$cocktail->id]);

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include[]=favoredBy");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'favoredBy' => [
                        '*' => ['id', 'email', 'name']
                    ]
                ],
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_validates_include_to_be_an_array(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include=user");  // Invalid data

        $response->assertJsonValidationErrors(['include']);
    }

    #[Test, Group('cocktails')]
    public function it_validates_include_to_be_an_array_of_strings(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include[]=1");   // Invalid data

        $response->assertJsonValidationErrors(['include.0']);
    }

    #[Test, Group('cocktails')]
    public function it_validates_include_to_be_an_array_of_strings_with_allowed_relation_ships(): void
    {
        Sanctum::actingAs($this->user);
        $cocktails = $this->makeMultipleCocktails(8);

        $cocktail = $cocktails[rand(0, count($cocktails) -1)]['cocktail'];

        $response = $this->getJson("/api/cocktails/{$cocktail->id}?include[]=test");    // Invalid data

        $response->assertJsonValidationErrors(['include.0']);
    }
}
