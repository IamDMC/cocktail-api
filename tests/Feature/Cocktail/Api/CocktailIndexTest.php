<?php

namespace Tests\Feature\Cocktail\Api;

use App\Models\Category;
use App\Models\Cocktail;
use App\Models\Ingredient;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Cocktail\CocktailTestCase;

class CocktailIndexTest extends CocktailTestCase
{
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
        $response = $this->getJson('/api/cocktails');

        $response->assertUnauthorized();
    }

    #[Test, Group('cocktails'), Group('auth')]
    public function it_requires_verified_user(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/cocktails');

        $response->assertForbidden();
    }

    #[Test, Group('cocktails')]
    public function it_lists_all_cocktails(): void
    {
        Sanctum::actingAs($this->user);

        $cocktails = $this->makeMultipleCocktails(5);

        $response = $this->getJson('/api/cocktails');

        $response->assertOk()
            ->assertJsonCount(count($cocktails), 'data');
    }

    #[Test, Group('cocktails')]
    public function it_lists_paginated_cocktails(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(5);

        $response = $this->getJson('/api/cocktails?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('cocktails')]
    public function it_limits_cocktails(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(5);

        $response = $this->getJson('/api/cocktails?limit=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('cocktails')]
    public function it_prioritises_per_page_over_limit(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(5);

        $response = $this->getJson('/api/cocktails?per_page=1&limit=2');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    #[Test, Group('cocktails')]
    public function it_limits_10_results_if_no_url_parameter_given(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(20);

        $response = $this->getJson('/api/cocktails');

        $response->assertOk()
            ->assertJsonCount(10, 'data');
    }

    #[Test, Group('cocktails')]
    public function it_applies_search(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(10);

        $cocktailData = $this->makeCocktail($this->user, defaultCocktailName: 'test-123-abc');
        $this->createCocktail($cocktailData);

        $response = $this->getJson('/api/cocktails?search=test-123-abc');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    #[Test, Group('cocktails')]
    public function it_applies_public_scope(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $cocktailData = $this->makeCocktail($this->user, defaultCocktailName: 'test-123-abc', isPublic: false);
        $this->createCocktail($cocktailData);

        $response = $this->getJson('/api/cocktails');

        $response->assertOk()
            ->assertJsonCount(8, 'data');

    }

    #[Test, Group('cocktails')]
    public function it_includes_user(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?include[]=user');

        $response->assertOk()
            ->assertJsonCount(8, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'user' => ['id', 'email', 'name']
                ],
            ],
        ]);
    }


    #[Test, Group('cocktails')]
    public function it_includes_categories(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?include[]=categories');

        $response->assertOk()
            ->assertJsonCount(8, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'categories' => [
                        '*' => ['id', 'name', 'description']
                    ]
                ],
            ],
        ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_steps(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?include[]=steps');

        $response->assertOk()
            ->assertJsonCount(8, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'steps' => [
                        '*' => ['id', 'step_number', 'instruction']
                    ]
                ],
            ],
        ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_ingredients(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?include[]=ingredients');

        $response->assertOk()
            ->assertJsonCount(8, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'ingredients' => [
                        '*' => ['id', 'name', 'amount', 'unit']
                    ]
                ],
            ],
        ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_ratings(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $user = User::factory()->create();

        $cocktails = Cocktail::all();

        foreach ($cocktails as $cocktail) {
            /** @var Cocktail $cocktail */
            $user->ratings()->create([
                'cocktail_id' => $cocktail->id,
                'rating' => rand(1,5),
                'comment' => fake()->sentence(2),
            ]);
        }

        $response = $this->getJson('/api/cocktails?include[]=ratings.user');

        $response->assertOk()
            ->assertJsonCount(8, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
            ],
        ]);
    }

    #[Test, Group('cocktails')]
    public function it_includes_favored_by(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $user = User::factory()->create();

        $cocktails = Cocktail::all()->modelKeys();

        $user->favoriteCocktails()->attach($cocktails);

        $response = $this->getJson('/api/cocktails?include[]=favoredBy');

        $response->assertOk()
            ->assertJsonCount(8, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'is_public',

                    'favoredBy' => [
                        '*' => ['id', 'email', 'name']
                    ]
                ],
            ],
        ]);
    }

    #[Test, Group('cocktails')]
    public function it_validates_include_to_be_an_array(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?include=user');  // Invalid data

        $response->assertJsonValidationErrors(['include']);
    }

    #[Test, Group('cocktails')]
    public function it_validates_include_to_be_an_array_of_strings(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?include[]=1');       // Invalid data

        $response->assertJsonValidationErrors(['include.0']);
    }

    #[Test, Group('cocktails')]
    public function it_validates_include_to_be_an_array_of_strings_with_allowed_relation_ships(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?include[]=test');        // Invalid data

        $response->assertJsonValidationErrors(['include.0']);
    }

    #[Test, Group('cocktails')]
    public function it_filters_categories(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(10);

        $categories = Category::factory()->count(3)->create();

        $cocktailData = $this->makeCocktail(
            $this->user,
            defaultCocktailName: 'test-123-abc',
            categories: $categories->modelKeys()
        );

        $this->createCocktail($cocktailData);

        $params = [
            'filter' => [
                [
                    'name' => 'categories',
                    'values' => $categories->modelKeys(),
                ],
            ],
        ];

        $response = $this->getJson('/api/cocktails?' . http_build_query($params));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => 'test-123-abc',
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_filters_ingredients(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(10);

        $ingredients = Ingredient::factory()->count(3)->create();

        $cocktailData = $this->makeCocktail(
            $this->user,
            defaultCocktailName: 'test-123-abc',
            ingredientsToBeUsed: $ingredients
        );

        $this->createCocktail($cocktailData);

        $params = [
            'filter' => [
                [
                    'name' => 'ingredients',
                    'values' => $ingredients->modelKeys(),
                ],
            ],
        ];

        $response = $this->getJson('/api/cocktails?' . http_build_query($params));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'name' => 'test-123-abc',
            ]);
    }

    #[Test, Group('cocktails')]
    public function it_validates_filter_to_be_an_array(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?filter=test');   // Invalid data

        $response->assertJsonValidationErrors(['filter']);
    }

    #[Test, Group('cocktails')]
    public function it_validates_filter_name_and_values_are_required_if_filter_is_applied(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $response = $this->getJson('/api/cocktails?filter[]');  // Invalid data

        $response->assertJsonValidationErrors([
            'filter.0.name',
            'filter.0.values'
        ]);
    }

    #[Test, Group('cocktails')]
    public function it_validates_filter_name_to_be_string(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $params = [
            'filter' => [
                [
                    'name' => 1,    // Invalid data
                    'values' => [1],
                ],
            ],
        ];

        $response = $this->getJson('/api/cocktails?' . http_build_query($params));

        $response->assertJsonValidationErrors(['filter.0.name']);
    }

    #[Test, Group('cocktails')]
    public function it_validates_filter_name_to_be_an_array_of_available_filter_strings(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $params = [
            'filter' => [
                [
                    'name' => 'test', // Invalid data
                    'values' => [1],
                ],
            ],
        ];

        $response = $this->getJson('/api/cocktails?' . http_build_query($params));

        $response->assertJsonValidationErrors(['filter.0.name']);
    }

    #[Test, Group('cocktails')]
    public function it_validates_filter_value_to_be_an_array(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $params = [
            'filter' => [
                [
                    'name' => 'categories',
                    'values' => 1, // Invalid data
                ],
            ],
        ];

        $response = $this->getJson('/api/cocktails?' . http_build_query($params));

        $response->assertJsonValidationErrors(['filter.0.values']);
    }

    #[Test, Group('cocktails')]
    public function it_validates_filter_value_to_be_an_array_of_integers(): void
    {
        Sanctum::actingAs($this->user);

        $this->makeMultipleCocktails(8);

        $params = [
            'filter' => [
                [
                    'name' => 'categories',
                    'values' => [[1]],          // Invalid data
                ],
            ],
        ];

        $response = $this->getJson('/api/cocktails?' . http_build_query($params));

        $response->assertJsonValidationErrors(['filter.0.values.0']);
    }
}
