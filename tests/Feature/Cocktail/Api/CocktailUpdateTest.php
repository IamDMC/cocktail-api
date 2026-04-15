<?php

namespace Tests\Feature\Cocktail\Api;

use App\Data\Cocktail\Create\CreateCocktailData;
use App\Data\Cocktail\Create\CreateCocktailIngredientData;
use App\Data\Cocktail\Create\CreateCocktailStepData;
use App\Models\Category;
use App\Models\Cocktail;
use App\Models\Ingredient;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Cocktail\CocktailTestCase;

class CocktailUpdateTest extends CocktailTestCase
{
    /**
     * @param CreateCocktailData $cocktailData
     * @param array<int, CreateCocktailStepData> $stepsDto
     * @param array<int, CreateCocktailIngredientData> $ingredientsDto
     * @param array<int, int> $categoryIds
     * @return array{
     *     name: string,
     *     description: string|null,
     *     isPublic: bool,
     *     steps: array<int, array<string, mixed>>,
     *     ingredients: array<int, array<string, mixed>>,
     *     categoryIds: array<int, int>
     * }
     */
    private function createParams(CreateCocktailData $cocktailData, array $stepsDto, array $ingredientsDto, array $categoryIds): array
    {
        $steps = [];

        foreach ($stepsDto as $step){
            $steps[] = $step->toArray();
        }

        $ingredients = [];
        foreach ($ingredientsDto as $ingredient){
            $ingredients[] = $ingredient->toArray();
        }

        return [
            'name' => $cocktailData->name,
            'description' => $cocktailData->description,
            'isPublic' => $cocktailData->isPublic,

            'steps' => $steps,
            'ingredients' => $ingredients,
            'categoryIds' => $categoryIds
        ];
    }

    #[Test, Group('cocktails'), Group('auth')]
    public function it_is_protected_from_unauthorized_access(): void
    {
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            defaultCocktailName: 'test-abc-123'
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, defaultName: 'test-efg-456');
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray();
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertUnauthorized();
    }

    #[Test, Group('cocktails'), Group('auth')]
    public function it_requires_verified_user(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        Cocktail::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->putJson('/api/cocktails/1', []);

        $response->assertForbidden();
    }

    #[Test, Group('cocktails')]
    public function it_only_allows_update_on_user_owned_cocktail(): void
    {
        Sanctum::actingAs($this->user);

        $user = User::factory()->create();

        $referenceCocktailData = $this->makeCocktail(
            user: $user,
            nrSteps: 1
        );

        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($user, isPublic: false);
        $steps =  $this->makeCocktailStepDtoArray(nrSteps: 5, stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray();
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertForbidden();
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_name(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            defaultCocktailName: 'test-abc-123'
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, defaultName: 'test-efg-456');
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray();
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($cocktail->name, $cocktailData->name);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_and_ignores_unique_name_validation_for_same_cocktail(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            defaultCocktailName: 'test-abc-123'
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, defaultName: 'test-abc-123');
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray();
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($cocktail->name, $cocktailData->name);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_description(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            description: true,
            descriptionText: 'test-abc-123'
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, description: 'test-efg-456');
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray();
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($cocktail->description, $cocktailData->description);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_visibility_to_public(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            isPublic: false
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, isPublic: true);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray();
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertTrue($cocktail->is_public);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_visibility_to_private(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            isPublic: true
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, isPublic: false);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray();
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertFalse($cocktail->is_public);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_steps(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            nrSteps: 1
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, isPublic: false);
        $steps =  $this->makeCocktailStepDtoArray(nrSteps: 5, stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray();
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_ingredients(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            nrIngredients: 1
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, isPublic: false);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 10);
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_categories(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            categories: Category::factory()->count(2)->create()->modelKeys()
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, isPublic: false);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 10);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_cocktail_name_to_be_unique(): void
    {
        Sanctum::actingAs($this->user);

        $this->createCocktail(
            $this->makeCocktail(
                user: $this->user,
                defaultCocktailName: 'test-abc-123'
            )
        );

        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            defaultCocktailName: 'test-def-456',
            stepIncrement: 1
        );

        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user, defaultName: 'test-abc-123');     // Invalid data
        $steps =  $this->makeCocktailStepDtoArray(nrSteps:3, stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 3);
        $categories = Category::factory()->count(3)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('cocktails', 2);

        $cocktail = Cocktail::query()->where('id', $referenceCocktail->id)->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_cocktail_name_to_be_string(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['name'] = 1;      // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_cocktail_name_min_length(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['name'] = 'abc';      // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_cocktail_name_max_length(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['name'] = str_repeat('a', 61);        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_description_to_be_string(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['description'] = 1;       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['description']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_description_min_length(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['description'] = 'abc';       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['description']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_description_max_length(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['description'] = str_repeat('a', 256);        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['description']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_required_boolean_is_public(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        unset($data['isPublic']);       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['isPublic']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_required_steps(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        unset($data['steps']);          // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_steps_to_be_an_array(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'] = 1;     // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_steps_min_array_size(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'] = [];        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_steps_max_array_size(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(nrSteps: 6, stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'] = [];        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_required_step_number(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        unset($data['steps'][0]['stepNumber']);     // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.0.stepNumber']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_step_number_to_be_an_integer(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'][0]['stepNumber'] = 'abc';        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.0.stepNumber']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_step_number_min_value(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'][0]['stepNumber'] = 0;        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.0.stepNumber']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_step_number_max_value(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'][0]['stepNumber'] = 16;       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.0.stepNumber']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_step_number_distinct_value(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'][0]['stepNumber'] = 1;
        $data['steps'][1]['stepNumber'] = 1;        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.1.stepNumber']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_required_step_instruction(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        unset($data['steps'][0]['instruction']);        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.0.instruction']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_step_instruction_to_be_string(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'][0]['instruction'] = 1;       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.0.instruction']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_step_instruction_min_string_length(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'][0]['instruction'] = 'abc';       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.0.instruction']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_step_instruction_max_string_length(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['steps'][0]['instruction'] = str_repeat('a', 256);        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['steps.0.instruction']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_required_ingredients(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        unset($data['ingredients']);        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredients_to_be_an_array(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'] = 1;       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredients_min_array_size(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'] = [];      // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredients_max_array_size(): void
    {
        Sanctum::actingAs($this->user);

        Ingredient::factory()->create();

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 21);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'] = [];      // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_required_ingredient_id(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        unset($data['ingredients'][0]['id']);       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.0.id']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredient_id_to_be_an_integer(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'][0]['id'] = 'abc';      // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.0.id']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredient_id_to_be_distinct(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        // Invalid data
        $data['ingredients'][][] = [
            'id' => $data['ingredients'][0]['id'],
            'amount' => $data['ingredients'][0]['amount'],
            'overwriteUnit' => null,
        ];

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.1.id']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredient_id_exists(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        // Invalid data
        $data['ingredients'][][] = [
            'id' => 999,
            'amount' => $data['ingredients'][0]['amount'],
            'overwriteUnit' => null,
        ];

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.1.id']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_required_ingredient_amount(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        unset($data['ingredients'][0]['amount']);       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.0.amount']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredient_amount_is_numeric(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'][0]['amount'] = 'abc';      // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.0.amount']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredient_amount_min_value(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'][0]['amount'] = 0.01;       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.0.amount']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredient_amount_max_value(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'][0]['amount'] = 1001;       // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.0.amount']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_updates_cocktail_without_ingredient_over_write_unit(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'][0]['overwriteUnit'] = null;        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertOk();

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($cocktailData->name, $cocktail->name);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($categories->modelKeys(), $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_ingredient_over_write_unit_to_be_of_type_unit_enum(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['ingredients'][0]['overwriteUnit'] = 'test-123-abc';      // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['ingredients.0.overwriteUnit']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_required_category_ids(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        unset($data['categoryIds']);        // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['categoryIds']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_category_ids_to_be_an_array(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['categoryIds'] = 1;           // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['categoryIds']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_category_ids_min_array_size(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(5)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['categoryIds'] = [];          // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['categoryIds']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_category_ids_max_array_size(): void
    {
        Sanctum::actingAs($this->user);

        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $categories = Category::factory()->count(6)->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients, $categories->modelKeys());

        $data['categoryIds'] = [];          // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['categoryIds']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_category_ids_to_be_distinct(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $category = Category::factory()->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients,  [$category->id]);

        $data['categoryIds'][] = $category->id;     // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['categoryIds.1']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_validates_category_ids_exists(): void
    {
        Sanctum::actingAs($this->user);
        $referenceCocktailData = $this->makeCocktail(user: $this->user, stepIncrement: 1);
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps =  $this->makeCocktailStepDtoArray(stepIncrement: 1);
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 1);
        $category = Category::factory()->create();

        $data = $this->createParams($cocktailData, $steps, $ingredients,  [$category->id]);

        $data['categoryIds'][] = 999;           // Invalid data

        $this->putJson("/api/cocktails/{$referenceCocktail->id}", $data)
            ->assertJsonValidationErrors(['categoryIds.1']);

        $this->assertDatabaseCount('cocktails', 1);

        $cocktail = Cocktail::query()->first();

        $this->assertEquals($referenceCocktailData['cocktailData']->name, $cocktail->name);

        $this->assertCocktailSteps($referenceCocktailData['steps'], $cocktail);
        $this->assertIngredients($referenceCocktailData['ingredients'], $cocktail);
        $this->assertCategories($referenceCocktailData['categories'], $cocktail);
    }
}
