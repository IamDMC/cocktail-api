<?php

namespace Tests\Feature\Actions\Cocktail;

use App\Actions\Cocktail\CreateCocktailAction;
use App\Data\Cocktail\CreateCocktailData;
use App\Data\CocktailStep\CreateCocktailStepData;
use App\Data\IngredientData\CreateCocktailIngredientData;
use App\Enums\Unit;
use App\Models\Category;
use App\Models\Cocktail;
use App\Models\CocktailStep;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCocktailActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private array $categoryIds;
    const MAX_NR_CATEGORIES = 5;
    const MAX_NR_INGREDIENTS = 20;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'test',
            'email' => 'test@test.at',
            'password' => Hash::make('password')
        ]);

        $categories = Category::factory()->count(self::MAX_NR_CATEGORIES)->create();
        $this->categoryIds = $categories->modelKeys();

        // Form request validation rule allows 20 ingredients
        Ingredient::factory()->count(self::MAX_NR_INGREDIENTS)->create();
    }

    private function makeCocktailDto(?User $user = null, bool $isPublic = true, bool $description = true): CreateCocktailData
    {
        $data = Cocktail::factory()->make([
            'user_id' => $user ? $user->id : $this->user->id,
            'description' => $description ? fake()->sentence(2) : null,
            'is_public' => $isPublic
        ]);

        return new CreateCocktailData(
            name: $data->name,
            description: $data->description,
            isPublic:  $data->is_public,
            userId: $data->user_id
        );
    }

    private function makeCocktailStepDtoArray(?int $nrSteps = null, int $stepIncrement = 0): array
    {
        if(! $nrSteps) $nrSteps = rand(1,5);

        $cocktailSteps = CocktailStep::factory()
            ->count($nrSteps)
            ->sequence(fn ($sequence) => [
                'step_number' => $sequence->index + $stepIncrement
            ])
            ->make();

        $stepsDto = [];

        foreach ($cocktailSteps as $step){
            $stepsDto[] = new CreateCocktailStepData(
                stepNumber: $step->step_number,
                instruction: $step->instruction
            );
        }

        return $stepsDto;
    }

    private function makeIngredientDtoArray(?int $nrIngredients = null, bool $defaultUnit = true): array
    {
        if(! $nrIngredients) $nrIngredients = rand(1, self::MAX_NR_INGREDIENTS);

        $ingredients = Ingredient::query()->limit($nrIngredients)->get();

        $ingredientsDto = [];
        foreach ($ingredients as $ingredient){
            $ingredientsDto[] = new CreateCocktailIngredientData(
                ingredientId: $ingredient->id,
                amount: (float) rand(1, 10),
                defaultUnit:  $ingredient->default_unit,
                overwriteUnit: $defaultUnit ? null : fake()->randomElement(Unit::cases())
            );
        }

        return $ingredientsDto;
    }

    #[Test, Group('cocktails')]
    public function it_creates_cocktail_correctly(): void
    {
        $cocktailData = $this->makeCocktailDto();
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray();

        $cocktail = app(CreateCocktailAction::class)->execute(
            cocktailData: $cocktailData,
            steps: $steps,
            categoryIds: $this->categoryIds,
            ingredients: $ingredients,
        );

        $this->assertDatabaseCount('cocktails', 1);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_creates_cocktail_correctly_and_normalizes_step_numbers(): void
    {
        $cocktailData = $this->makeCocktailDto();
        $steps =  $this->makeCocktailStepDtoArray(10, 3);
        $ingredients = $this->makeIngredientDtoArray();

        $cocktail = app(CreateCocktailAction::class)->execute(
            cocktailData: $cocktailData,
            steps: $steps,
            categoryIds: $this->categoryIds,
            ingredients: $ingredients,
        );

        $this->assertDatabaseCount('cocktails', 1);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_creates_private_cocktail_without_description_correctly(): void
    {
        $cocktailData = $this->makeCocktailDto(isPublic: false, description: false);
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray();

        $cocktail = app(CreateCocktailAction::class)->execute(
            cocktailData: $cocktailData,
            steps: $steps,
            categoryIds: $this->categoryIds,
            ingredients: $ingredients,
        );

        $this->assertDatabaseCount('cocktails', 1);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_creates_cocktail_with_different_units_correctly(): void
    {
        $cocktailData = $this->makeCocktailDto();
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray(defaultUnit: false);

        $cocktail = app(CreateCocktailAction::class)->execute(
            cocktailData: $cocktailData,
            steps: $steps,
            categoryIds: $this->categoryIds,
            ingredients: $ingredients,
        );

        $this->assertDatabaseCount('cocktails', 1);

        $this->assertCocktailSteps($steps, $cocktail);
        $this->assertIngredients($ingredients, $cocktail);
        $this->assertCategories($cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_rolls_back_on_failure(): void
    {
        $cocktailData = $this->makeCocktailDto();
        $steps = $this->makeCocktailStepDtoArray();

        $ingredients = [
            new CreateCocktailIngredientData(
                ingredientId: 999999, // invalid
                amount: 1.5,
                defaultUnit: Unit::CL,
                overwriteUnit: null
            )
        ];

        $this->expectException(\Throwable::class);

        app(CreateCocktailAction::class)->execute(
            $cocktailData,
            $steps,
            $this->categoryIds,
            $ingredients
        );

        $this->assertDatabaseCount('cocktails', 0);
        $this->assertDatabaseCount('cocktail_steps', 0);
    }

    private function assertCocktailSteps(array $steps, Cocktail $cocktail): void
    {
        $this->assertCount(count($steps), $cocktail->steps);

        foreach ($steps as $index => $step){
            $this->assertDatabaseHas('cocktail_steps', [
                'cocktail_id' => $cocktail->id,
                'step_number' => $index + 1,
                'instruction' => $step->instruction
            ]);
        }

        $this->assertEquals(
            range(1, count($steps)),
            $cocktail->steps->pluck('step_number')->toArray()
        );
    }

    private function assertIngredients(array $ingredients, Cocktail $cocktail): void
    {
        $this->assertCount(count($ingredients), $cocktail->ingredients);

        foreach ($ingredients as $ingredient){

            $this->assertDatabaseHas('cocktail_ingredient', [
                'cocktail_id' => $cocktail->id,
                'ingredient_id' => $ingredient->ingredientId,
                'amount' => $ingredient->amount,
                'unit' => $ingredient->overwriteUnit ?: $ingredient->defaultUnit
            ]);
        }
    }

    private function assertCategories(Cocktail $cocktail): void
    {
        $this->assertCount(count($this->categoryIds), $cocktail->categories);

        foreach ($this->categoryIds as $id){
            $this->assertDatabaseHas('category_cocktail', [
                'cocktail_id' => $cocktail->id,
                'category_id' => $id,
            ]);
        }
    }
}
