<?php

namespace Tests\Feature\Api;

use App\Enums\Unit;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;
    private User $adminUser, $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'admin-test',
            'email' => 'admin.test@test.at',
            'admin' => true,
            'password' => Hash::make('password')
        ]);

        $this->regularUser = User::factory()->create([
            'name' => 'test',
            'email' => 'test@test.at',
            'password' => Hash::make('password')
        ]);
    }

    #[Test, Group('ingredients'), Group('auth')]
    public function it_is_protected_from_unauthorized_access(): void
    {
        $this->getJson('/api/ingredients')->assertUnauthorized();

        $this->postJson('/api/ingredients', [])->assertUnauthorized();

        $this->putJson('/api/ingredients/1', [])->assertUnauthorized();

        $this->deleteJson('/api/ingredients/1')->assertUnauthorized();
    }
    #[Test, Group('ingredients'), Group('auth')]
    public function it_requires_verified_user(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        Ingredient::factory()->create();

        $this->getJson('/api/ingredients')->assertForbidden();

        $this->postJson('/api/ingredients', [])->assertForbidden();

        $this->putJson('/api/ingredients/1', [])->assertForbidden();

        $this->deleteJson('/api/ingredients/1')->assertForbidden();
    }

    #[Test, Group('ingredients')]
    public function index_lists_all_ingredients()
    {
        Sanctum::actingAs($this->regularUser);
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test, Group('ingredients')]
    public function index_lists_paginated_ingredients(): void
    {
        Sanctum::actingAs($this->regularUser);
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('ingredients')]
    public function index_limits_ingredients(): void
    {
        Sanctum::actingAs($this->regularUser);
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients?limit=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('ingredients')]
    public function index_prioritises_per_page_over_limit(): void
    {
        Sanctum::actingAs($this->regularUser);
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients?limit=3&per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('ingredients')]
    public function index_prioritises_limit_over_default(): void
    {
        Sanctum::actingAs($this->regularUser);
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients?limit=3&per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('ingredients')]
    public function it_stores_ingredient_correctly(): void
    {
        Sanctum::actingAs($this->adminUser);
        $data = Ingredient::factory()->make([
            'name' => 'white rum',
            'description' => 'Light rum',
            'default_unit' => Unit::CL
        ])->toArray();

        $response = $this->postJson('/api/ingredients', $data);

        $response->assertCreated()
            ->assertJsonFragment($data);

        $this->assertDatabaseCount('ingredients', 1);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_allow_regular_user_to_store_ingredient(): void
    {
        Sanctum::actingAs($this->regularUser);

        $data = Ingredient::factory()->make([
            'name' => 'white rum',
            'description' => 'Light rum',
            'default_unit' => Unit::CL
        ])->toArray();

        $response = $this->postJson('/api/ingredients', $data);

        $response->assertForbidden();

        $this->assertDatabaseCount('ingredients', 0);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_store_ingredient_with_empty_data(): void
    {
        Sanctum::actingAs($this->adminUser);
        $response = $this->postJson('/api/ingredients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'default_unit']);

        $this->assertDatabaseCount('ingredients', 0);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_store_ingredient_with_invalid_data():void
    {
        Sanctum::actingAs($this->adminUser);
        $invalidData = [
            'name' => 1,
            'default_unit' => Unit::CL
        ];

        $response =  $this->postJson('/api/ingredients', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('ingredients', 0);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_store_ingredient_if_name_is_not_unique():void
    {
        Sanctum::actingAs($this->adminUser);
        Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'Light rum',
            'default_unit' => Unit::CL
        ]);

        $invalidData = [
            'name' => 'white rum',
            'default_unit' => Unit::ML
        ];

        $response =  $this->postJson('/api/ingredients', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('ingredients', 1);
    }

    #[Test, Group('ingredients')]
    public function it_shows_single_ingredient_correctly(): void
    {
        Sanctum::actingAs($this->regularUser);
        $ingredient = Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::CL
        ]);

        $response = $this->getJson("/api/ingredients/{$ingredient->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'white rum',
                'description' => 'light rum',
                'default_unit' => Unit::CL
            ]);
    }

    #[Test, Group('ingredients')]
    public function it_returns_404_if_single_ingredient_not_found(): void
    {
        Sanctum::actingAs($this->regularUser);
        Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::CL
        ]);

        $response = $this->getJson("/api/ingredients/999");

        $response->assertNotFound();
    }

    #[Test, Group('ingredients')]
    public function it_updates_ingredient_correctly(): void
    {
        Sanctum::actingAs($this->adminUser);
        $ingredient = Ingredient::factory()->create([
            'name' => 'dark rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $updateData = [
            'name' => 'white rum',
            'default_unit' => Unit::CL
        ];

        $expected = [
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::CL
        ];

        $response = $this->putJson("/api/ingredients/{$ingredient->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment($expected);

        $this->assertDatabaseHas('ingredients', $expected);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_allow_regular_user_to_update_ingredient(): void
    {
        Sanctum::actingAs($this->regularUser);

        $ingredient = Ingredient::factory()->create([
            'name' => 'dark rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $updateData = [
            'name' => 'white rum',
            'default_unit' => Unit::CL
        ];


        $response = $this->putJson("/api/ingredients/{$ingredient->id}", $updateData);

        $response->assertForbidden();

        $this->assertDatabaseHas('ingredients', [
            'name' => $ingredient->name,
            'description' => $ingredient->description,
            'default_unit' => $ingredient->default_unit,
        ]);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_update_ingredient_with_empty_data(): void
    {
        Sanctum::actingAs($this->adminUser);
        $ingredient = Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $response = $this->putJson("/api/ingredients/{$ingredient->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'default_unit']);

        $this->assertDatabaseHas('ingredients', [
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_update_ingredient_with_invalid_data(): void
    {
        Sanctum::actingAs($this->adminUser);
        $ingredient = Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $invalidData = [
            'name' => 1,
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ];

        $response = $this->putJson("/api/ingredients/{$ingredient->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseHas('ingredients', [
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);
    }

    #[Test, Group('ingredients')]
    public function it_ignores_unique_validation_rule_on_update(): void
    {
        Sanctum::actingAs($this->adminUser);
        $ingredient = Ingredient::factory()->create([
            'name' => 'dark rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $updateData = [
            'name' => 'dark rum',
            'description' => 'strong rum',
            'default_unit' => Unit::CL
        ];

        $expected = [
            'name' => 'dark rum',
            'description' => 'strong rum',
            'default_unit' => Unit::CL
        ];

        $response = $this->putJson("/api/ingredients/{$ingredient->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment($expected);

        $this->assertDatabaseHas('ingredients', $expected);
    }

    #[Test, Group('ingredients')]
    public function it_deletes_ingredient_correctly(): void
    {
        Sanctum::actingAs($this->adminUser);
        $ingredient = Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $response = $this->deleteJson("/api/ingredients/{$ingredient->id}");

        $response->assertNoContent();

        $this->assertDatabaseCount('ingredients',0);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_delete_ingredient_if_not_found(): void
    {
        Sanctum::actingAs($this->adminUser);
        Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $response = $this->deleteJson("/api/ingredients/999");

        $response->assertNotFound();

        $this->assertDatabaseCount('ingredients',1);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_allow_regular_user_to_delete_ingredient(): void
    {
        Sanctum::actingAs($this->regularUser);

        $ingredient = Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $response = $this->deleteJson("/api/ingredients/{$ingredient->id}");

        $response->assertForbidden();

        $this->assertDatabaseCount('ingredients',1);
    }
}
