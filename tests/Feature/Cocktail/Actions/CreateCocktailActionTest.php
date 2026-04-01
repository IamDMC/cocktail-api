<?php

namespace Tests\Feature\Cocktail\Actions;

use App\Actions\Cocktail\CreateCocktailAction;
use App\Data\Cocktail\Create\CreateCocktailIngredientData;
use App\Enums\Unit;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Cocktail\CocktailTestCase;

class CreateCocktailActionTest extends CocktailTestCase
{
    #[Test, Group('cocktails')]
    public function it_creates_cocktail_correctly(): void
    {
        $cocktailData = $this->makeCreateCocktailDto($this->user);
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
        $this->assertCategories($this->categoryIds, $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_creates_cocktail_correctly_and_normalizes_step_numbers(): void
    {
        $cocktailData = $this->makeCreateCocktailDto($this->user);
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
        $this->assertCategories($this->categoryIds, $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_creates_private_cocktail_without_description_correctly(): void
    {
        $cocktailData = $this->makeCreateCocktailDto($this->user, isPublic: false, description: false);
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
        $this->assertCategories($this->categoryIds, $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_creates_cocktail_with_different_units_correctly(): void
    {
        $cocktailData = $this->makeCreateCocktailDto($this->user);
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
        $this->assertCategories($this->categoryIds, $cocktail);
    }

    #[Test, Group('cocktails')]
    public function it_rolls_back_on_failure(): void
    {
        $cocktailData = $this->makeCreateCocktailDto($this->user);
        $steps = $this->makeCocktailStepDtoArray();

        $ingredients = [
            new CreateCocktailIngredientData(
                ingredientId: 999999, // invalid
                amount: 1.5,
                defaultUnit: Unit::CL,
                overwriteUnit: null
            )
        ];

        $this->expectException(\Illuminate\Database\QueryException::class);

        app(CreateCocktailAction::class)->execute(
            $cocktailData,
            $steps,
            $this->categoryIds,
            $ingredients
        );

        $this->assertDatabaseCount('cocktails', 0);
        $this->assertDatabaseCount('cocktail_steps', 0);
    }

}
