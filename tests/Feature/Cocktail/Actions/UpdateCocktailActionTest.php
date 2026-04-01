<?php

namespace Tests\Feature\Cocktail\Actions;

use App\Actions\Cocktail\UpdateCocktailAction;
use App\Models\Category;
use App\Models\Cocktail;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Cocktail\CocktailTestCase;

class UpdateCocktailActionTest extends CocktailTestCase
{
    #[Test, Group('cocktail')]
    public function it_updates_cocktail_name(): void
    {
        $referenceCocktailData = $this->makeCocktail($this->user,'test-123-abc');
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeUpdateCocktailDto();
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray();

        $updatedCocktail = app(UpdateCocktailAction::class)->execute(
            $referenceCocktail,
            $cocktailData,
            $steps,
            $ingredients,
            $this->categoryIds
        );

        $this->assertDatabaseCount('cocktails', 1);
        $this->assertEquals($cocktailData->name, $updatedCocktail->name);

        $this->assertCocktailSteps($steps, $updatedCocktail);
        $this->assertIngredients($ingredients, $updatedCocktail);
        $this->assertCategories($this->categoryIds, $updatedCocktail);
    }

    #[Test, Group('cocktail')]
    public function it_updates_cocktail_description(): void
    {
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            descriptionText: 'test-123-abc',
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);


        $cocktailData = $this->makeUpdateCocktailDto();
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray();

        $updatedCocktail = app(UpdateCocktailAction::class)->execute(
            $referenceCocktail,
            $cocktailData,
            $steps,
            $ingredients,
            $this->categoryIds
        );

        $this->assertDatabaseCount('cocktails', 1);
        $this->assertEquals($cocktailData->description, $updatedCocktail->description);

        $this->assertCocktailSteps($steps, $updatedCocktail);
        $this->assertIngredients($ingredients, $updatedCocktail);
        $this->assertCategories($this->categoryIds, $updatedCocktail);
    }

    #[Test, Group('cocktail')]
    public function it_updates_cocktail_to_public_visibility(): void
    {
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            isPublic: false,
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);


        $cocktailData = $this->makeUpdateCocktailDto(isPublic: true);
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray();

        $updatedCocktail = app(UpdateCocktailAction::class)->execute(
            $referenceCocktail,
            $cocktailData,
            $steps,
            $ingredients,
            $this->categoryIds
        );

        $this->assertDatabaseCount('cocktails', 1);
        $this->assertTrue($updatedCocktail->is_public);

        $this->assertCocktailSteps($steps, $updatedCocktail);
        $this->assertIngredients($ingredients, $updatedCocktail);
        $this->assertCategories($this->categoryIds, $updatedCocktail);
    }

    #[Test, Group('cocktail')]
    public function it_updates_cocktail_to_private_visibility(): void
    {
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            isPublic: true,
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);


        $cocktailData = $this->makeUpdateCocktailDto(isPublic: false);
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray();

        $updatedCocktail = app(UpdateCocktailAction::class)->execute(
            $referenceCocktail,
            $cocktailData,
            $steps,
            $ingredients,
            $this->categoryIds
        );

        $this->assertDatabaseCount('cocktails', 1);
        $this->assertFalse($updatedCocktail->is_public);

        $this->assertCocktailSteps($steps, $updatedCocktail);
        $this->assertIngredients($ingredients, $updatedCocktail);
        $this->assertCategories($this->categoryIds, $updatedCocktail);
    }

    #[Test, Group('cocktail')]
    public function it_updates_and_normalizes_cocktail_steps(): void
    {
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            nrSteps: 10
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);


        $cocktailData = $this->makeUpdateCocktailDto(isPublic: false);
        $steps =  $this->makeCocktailStepDtoArray(nrSteps: 3, stepIncrement: 12);
        $ingredients = $this->makeIngredientDtoArray();

        $updatedCocktail = app(UpdateCocktailAction::class)->execute(
            $referenceCocktail,
            $cocktailData,
            $steps,
            $ingredients,
            $this->categoryIds
        );

        $this->assertDatabaseCount('cocktails', 1);

        $this->assertCocktailSteps($steps, $updatedCocktail);
        $this->assertIngredients($ingredients, $updatedCocktail);
        $this->assertCategories($this->categoryIds, $updatedCocktail);
    }

    #[Test, Group('cocktail')]
    public function it_updates_cocktail_categories(): void
    {
        $updCategory = Category::factory()->count(3)->create();

        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            categories: $this->categoryIds
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);


        $cocktailData = $this->makeUpdateCocktailDto(isPublic: false);
        $steps =  $this->makeCocktailStepDtoArray(nrSteps: 3, stepIncrement: 12);
        $ingredients = $this->makeIngredientDtoArray();

        $updatedCocktail = app(UpdateCocktailAction::class)->execute(
            $referenceCocktail,
            $cocktailData,
            $steps,
            $ingredients,
            $updCategory->modelKeys()
        );

        $this->assertDatabaseCount('cocktails', 1);

        $this->assertCocktailSteps($steps, $updatedCocktail);
        $this->assertIngredients($ingredients, $updatedCocktail);
        $this->assertCategories($updCategory->modelKeys(), $updatedCocktail);
    }

    #[Test, Group('cocktail')]
    public function it_updates_cocktail_ingredients(): void
    {

        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            nrIngredients: 12
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeUpdateCocktailDto();
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray(nrIngredients: 6);

        $updatedCocktail = app(UpdateCocktailAction::class)->execute(
            $referenceCocktail,
            $cocktailData,
            $steps,
            $ingredients,
            $this->categoryIds
        );

        $this->assertDatabaseCount('cocktails', 1);

        $this->assertCocktailSteps($steps, $updatedCocktail);
        $this->assertIngredients($ingredients, $updatedCocktail);
        $this->assertCategories($this->categoryIds, $updatedCocktail);
    }

    #[Test, Group('cocktails')]
    public function it_rolls_back_on_failure(): void
    {
        $referenceCocktailData = $this->makeCocktail(
            user: $this->user,
            defaultCocktailName: 'test-123-abc'
        );
        $referenceCocktail = $this->createCocktail($referenceCocktailData);

        $cocktailData = $this->makeUpdateCocktailDto();
        $steps =  $this->makeCocktailStepDtoArray();
        $ingredients = $this->makeIngredientDtoArray();

        $this->expectException(\Illuminate\Database\QueryException::class);

        $updatedCocktail = app(UpdateCocktailAction::class)->execute(
            $referenceCocktail,
            $cocktailData,
            $steps,
            $ingredients,
            [999, 998]
        );

        $this->assertDatabaseCount('cocktails', 1);

        $this->assertEquals('test-123-abc', $referenceCocktail->fresh()->name);
        $this->assertDatabaseCount('cocktail_steps', $referenceCocktail->steps()->count());
        $this->assertDatabaseCount('cocktail_ingredient', $referenceCocktail->ingredients()->count());
    }
}
