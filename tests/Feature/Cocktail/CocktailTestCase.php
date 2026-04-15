<?php

namespace Tests\Feature\Cocktail;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Support\Cocktail\CocktailTestHelper;
use Tests\TestCase;

class CocktailTestCase extends TestCase
{
    use RefreshDatabase, CocktailTestHelper;

    public User $user;
    public array $categoryIds;
    const MAX_NR_CATEGORIES = 5;
    const MAX_NR_INGREDIENTS = 20;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'test',
            'email' => 'test@test.at',
            'password' => Hash::make('password'),
            'email_verified_at' => now()
        ]);

        //Sanctum::actingAs($this->user);

        $categories = $this->createCategories(self::MAX_NR_CATEGORIES);
        $this->categoryIds = $categories->modelKeys();

        // Form request validation rule allows 20 ingredients
        $this->createIngredients(self::MAX_NR_INGREDIENTS);
    }
}
