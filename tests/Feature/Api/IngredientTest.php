<?php

namespace Api;

use App\Enums\Unit;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Knuckles\Scribe\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;

    #[Test, Group('ingredients')]
    public function index_lists_all_ingredients()
    {
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test, Group('ingredients')]
    public function index_lists_paginated_ingredients(): void
    {
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('ingredients')]
    public function index_limits_ingredients(): void
    {
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients?limit=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('ingredients')]
    public function index_prioritises_per_page_over_limit(): void
    {
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients?limit=3&per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('ingredients')]
    public function index_prioritises_limit_over_default(): void
    {
        Ingredient::factory()->count(3)->create();

        $response = $this->getJson('/api/ingredients?limit=3&per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('ingredients')]
    public function it_stores_ingredient_correctly(): void
    {
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
    public function it_does_not_store_ingredient_with_empty_data(): void
    {
        $response = $this->postJson('/api/ingredients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'default_unit']);

        $this->assertDatabaseCount('ingredients', 0);
    }

    #[Test, Group('ingredients')]
    public function it_does_not_store_ingredient_with_invalid_data():void
    {
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
    public function it_does_not_update_ingredient_with_empty_data(): void
    {
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
        Ingredient::factory()->create([
            'name' => 'white rum',
            'description' => 'light rum',
            'default_unit' => Unit::ML
        ]);

        $response = $this->deleteJson("/api/ingredients/999");

        $response->assertNotFound();

        $this->assertDatabaseCount('ingredients',1);
    }
}
