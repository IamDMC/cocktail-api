<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test, Group('categories')]
    public function index_lists_all_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test, Group('categories')]
    public function index_lists_paginated_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('categories')]
    public function index_limits_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories?limit=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('categories')]
    public function index_prioritises_per_page_over_limit(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories?limit=3&per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('categories')]
    public function index_prioritises_limit_over_default(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories?limit=3&per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test, Group('categories')]
    public function it_stores_category_correctly(): void
    {
        $data = Category::factory()->make([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ])->toArray();

        $response = $this->postJson('/api/categories', $data);

        $response->assertCreated()
            ->assertJsonFragment($data);

        $this->assertDatabaseCount('categories', 1);
    }

    #[Test, Group('categories')]
    public function it_does_not_store_category_with_empty_data(): void
    {
        $response = $this->postJson('/api/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);

        $this->assertDatabaseCount('categories', 0);
    }

    #[Test, Group('categories')]
    public function it_does_not_store_category_with_invalid_data(): void
    {
        $response = $this->postJson('/api/categories', ['name' => 1]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);

        $this->assertDatabaseCount('categories', 0);
    }

    #[Test, Group('categories')]
    public function it_shows_single_category_correctly(): void
    {
        $category = Category::factory()->create([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);

        $response = $this->getJson("/api/categories/{$category->id}");


        $response->assertOk()->assertJsonFragment([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);
    }

    #[Test, Group('categories')]
    public function it_returns_404_if_single_category_not_found(): void
    {
        Category::factory()->create([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);

        $response = $this->getJson("/api/categories/999");

        $response->assertNotFound();
    }

    #[Test, Group('categories')]
    public function it_updates_category_correctly(): void
    {
        $category = Category::factory()->create([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);

        $updateData = [
            'name' => 'Updated name',
            'description' => 'Updated description'
        ];

        $response = $this->putJson("/api/categories/{$category->id}", $updateData);

        $response->assertOk()->assertJsonFragment($updateData);

        $this->assertDatabaseHas('categories', $updateData);
    }

    #[Test, Group('categories')]
    public function it_does_not_update_category_with_empty_data(): void
    {
        $category = Category::factory()->create([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);

        $response = $this->putJson("/api/categories/{$category->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);

        $this->assertDatabaseHas('categories', [
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);
    }

    #[Test, Group('categories')]
    public function it_does_not_update_category_with_invalid_data(): void
    {
        $category = Category::factory()->create([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);

        $invalidData = [
            'name' => 22,
        ];

        $response = $this->putJson("/api/categories/{$category->id}", $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description']);

        $this->assertDatabaseHas('categories', [
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);
    }

    #[Test, Group('categories')]
    public function it_deletes_category_correctly(): void
    {
        $category = Category::factory()->create([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertNoContent();

        $this->assertDatabaseCount('categories', 0);
    }

    #[Test, Group('categories')]
    public function it_does_not_delete_category_if_not_found(): void
    {
        Category::factory()->create([
            'name' => 'Cocktails',
            'description' => 'Alcoholic drinks',
        ]);

        $response = $this->deleteJson("/api/categories/999");

        $response->assertStatus(404);

        $this->assertDatabaseCount('categories', 1);
    }
}
