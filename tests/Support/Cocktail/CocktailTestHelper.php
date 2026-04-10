<?php

namespace Tests\Support\Cocktail;

use App\Data\Cocktail\Create\CreateCocktailData;
use App\Data\Cocktail\Create\CreateCocktailIngredientData;
use App\Data\Cocktail\Create\CreateCocktailStepData;
use App\Data\Cocktail\Update\UpdateCocktailData;
use App\Enums\Unit;
use App\Models\Category;
use App\Models\Cocktail;
use App\Models\CocktailStep;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

trait CocktailTestHelper {

    /**
     * @param int $maxNrCategories
     * @return Collection<int, Category>
     */
    public function createCategories(int $maxNrCategories = 1): Collection
    {
        return Category::factory()->count($maxNrCategories)->create();
    }

    /**
     * @param int $maxNrIngredients
     * @return Collection<int, Ingredient>
     */
    public function createIngredients(int $maxNrIngredients = 1): Collection
    {
        return Ingredient::factory()->count($maxNrIngredients)->create();
    }

    /**
     * @param User $user
     * @param string|null $defaultName
     * @param bool $isPublic
     * @param bool $description
     * @param string|null $descriptionText
     * @return CreateCocktailData
     */
    public function makeCreateCocktailDto(
        User $user,
        ?string $defaultName = null,
        bool $isPublic = true,
        bool $description = true,
        ?string $descriptionText = null
    ): CreateCocktailData
    {
        if(! $description){
            $descriptionText = null;
        } elseif (! $descriptionText){
            $descriptionText = fake()->sentence(2);
        }

        $data = Cocktail::factory()->make([
            'name' => $defaultName ?: fake()->name,
            'description' => $descriptionText,
            'is_public' => $isPublic,
            'user_id' => $user->id,
        ]);

        return new CreateCocktailData(
            name: $data->name,
            description: $data->description,
            isPublic:  $data->is_public,
            userId: $data->user_id
        );
    }

    /**
     * @param bool $isPublic
     * @param bool $description
     * @return UpdateCocktailData
     */
    public function makeUpdateCocktailDto(bool $isPublic = true, bool $description = true): UpdateCocktailData
    {
        $data = Cocktail::factory()->make([
            'description' => $description ? fake()->sentence(2) : null,
            'is_public' => $isPublic
        ]);

        return new UpdateCocktailData(
            name: $data->name,
            description: $data->description,
            isPublic:  $data->is_public,
        );
    }

    /**
     * @param int|null $nrSteps
     * @param int $stepIncrement
     * @return array<int, CreateCocktailStepData>
     */
    public function makeCocktailStepDtoArray(?int $nrSteps = null, int $stepIncrement = 0): array
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

    /**
     * @param int|null $nrIngredients
     * @param bool $defaultUnit
     * @param Collection<int, Ingredient>|null $ingredientsToBeUsed
     * @return array<int, CreateCocktailIngredientData>
     */
    public function makeIngredientDtoArray(?int $nrIngredients = null, bool $defaultUnit = true, ?Collection $ingredientsToBeUsed = null): array
    {
        $ingredientsDto = [];

        // No ingredients collection passed
        if (! $ingredientsToBeUsed){
            $maxNrIngredients = max(1, Ingredient::query()->count());
            if(! $nrIngredients) $nrIngredients = rand(1, $maxNrIngredients);

            $ingredients = Ingredient::query()->limit($nrIngredients)->get();

            foreach ($ingredients as $ingredient){
                $ingredientsDto[] = new CreateCocktailIngredientData(
                    ingredientId: $ingredient->id,
                    amount: (float) rand(1, 10),
                    defaultUnit:  $ingredient->default_unit,
                    overwriteUnit: $defaultUnit ? null : fake()->randomElement(Unit::cases())
                );
            }

            //dd($ingredientsDto);

        }else{

            foreach ($ingredientsToBeUsed as $ingredient){

                $ingredientsDto[] = new CreateCocktailIngredientData(
                    ingredientId: $ingredient->id,
                    amount: (float) rand(1, 10),
                    defaultUnit:  $ingredient->default_unit,
                    overwriteUnit: $defaultUnit ? null : fake()->randomElement(Unit::cases())
                );
            }
        }

        return $ingredientsDto;
    }

    /**
     * @param User $user
     * @param string|null $defaultCocktailName
     * @param bool $isPublic
     * @param bool $description
     * @param string|null $descriptionText
     * @param int|null $nrSteps
     * @param int $stepIncrement
     * @param int|null $nrIngredients
     * @param bool $defaultUnit
     * @param Collection<int, Ingredient>|null $ingredientsToBeUsed
     * @param array<int, int> $categories
     * @return array{
     *     cocktailData: CreateCocktailData,
     *     steps: array<int, CreateCocktailStepData>,
     *     ingredients: array<int, CreateCocktailIngredientData>,
     *     categories: array<int, int>
     * }
     */
    public function makeCocktail(
        // Cocktail data
        User $user,
        ?string $defaultCocktailName = null,
        bool $isPublic = true,
        bool $description = true,
        ?string $descriptionText = null,

        // Cocktail step data
        ?int $nrSteps = null,
        int $stepIncrement = 0,

        // Ingredients data
        ?int $nrIngredients = null,
        bool $defaultUnit = true,
        ?Collection $ingredientsToBeUsed = null,

        // Category IDs
        array $categories = []
    ): array
    {
        return [
            'cocktailData' => $this->makeCreateCocktailDto($user, $defaultCocktailName, $isPublic, $description, $descriptionText),
            'steps' => $this->makeCocktailStepDtoArray($nrSteps, $stepIncrement),
            'ingredients' => $this->makeIngredientDtoArray($nrIngredients, $defaultUnit, $ingredientsToBeUsed),
            'categories' => empty($categories) ? $this->categoryIds : $categories,
        ];
    }

    /**
     * @param array{
     *     cocktailData: CreateCocktailData,
     *     steps: array<int, CreateCocktailStepData>,
     *     ingredients: array<int, CreateCocktailIngredientData>,
     *     categories: array<int, int>
     * } $cocktailData
     * @return Cocktail
     */
    public function createCocktail(
        array $cocktailData
    ): Cocktail
    {
        $cocktail = $cocktailData['cocktailData'];
        $steps = $cocktailData['steps'];
        $ingredients = $cocktailData['ingredients'];
        $categories = $cocktailData['categories'];

        if (! $cocktail || empty($steps) || empty($ingredients) || empty($categories))
        {
            throw new \DomainException('cocktail data is not set correctly, use makeCocktail()');
        }

        $cocktail = Cocktail::create([
            'name' => $cocktail->name,
            'description' => $cocktail->description,
            'is_public' => $cocktail->isPublic,
            'user_id' => $cocktail->userId,
        ]);

        foreach ($steps as $step){
            $cocktail->steps()->create([
                'step_number' => $step->stepNumber,
                'instruction' => $step->instruction,
            ]);
        }

        foreach ($ingredients as $ingredient){
            $cocktail->ingredients()->attach([
                $ingredient->ingredientId => [
                    'amount' => $ingredient->amount,
                    'unit' => $ingredient->overwriteUnit ?: $ingredient->defaultUnit,
                ]
            ]);
        }

        $cocktail->categories()->attach($categories);

        return $cocktail->fresh()->load(['steps', 'categories', 'ingredients']);
    }

    /**
     * @param array<int, CreateCocktailStepData> $steps
     * @param Cocktail $cocktail
     * @return void
     */
    public function assertCocktailSteps(array $steps, Cocktail $cocktail): void
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

    /**
     * @param array<int, CreateCocktailIngredientData> $ingredients
     * @param Cocktail $cocktail
     * @return void
     */
    public function assertIngredients(array $ingredients, Cocktail $cocktail): void
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

    /**
     * @param array<int, int> $categories
     * @param Cocktail $cocktail
     * @return void
     */
    public function assertCategories(array $categories, Cocktail $cocktail): void
    {

        $this->assertCount(count($categories), $cocktail->categories);

        foreach ($categories as $id){
            $this->assertDatabaseHas('category_cocktail', [
                'cocktail_id' => $cocktail->id,
                'category_id' => $id,
            ]);
        }
    }
}
