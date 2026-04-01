<?php

namespace App\Actions\Cocktail;

use App\Data\Cocktail\Create\CreateCocktailIngredientData;
use App\Data\Cocktail\Create\CreateCocktailStepData;
use App\Data\Cocktail\Update\UpdateCocktailData;
use App\Models\Cocktail;
use App\Models\CocktailStep;
use App\Support\Cocktail\CocktailQueryHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateCocktailAction
{
    /**
     * @param Cocktail $cocktail
     * @param UpdateCocktailData $updateCocktailData
     * @param array<int, CreateCocktailStepData> $steps
     * @param array<int, CreateCocktailIngredientData> $ingredients
     * @param array<int, int> $categoryIds
     */
    public function execute(
        Cocktail $cocktail,
        UpdateCocktailData $updateCocktailData,
        array $steps,
        array $ingredients,
        array $categoryIds
    ): Cocktail
    {
        return DB::transaction(function () use($cocktail, $updateCocktailData, $steps, $ingredients, $categoryIds){

            $cocktail->update([
               'name' => $updateCocktailData->name,
               'description' => $updateCocktailData->description,
               'is_public' => $updateCocktailData->isPublic,
           ]);

           $cocktail->steps()->delete();

           $steps = $this->sortByStepNumber($steps)->values();

           foreach ($steps as $index => $step){
               CocktailStep::create([
                   'step_number' => $index + 1,
                   'instruction' => $step->instruction,
                   'cocktail_id' => $cocktail->id
               ]);
           }

           $updatedIngredients = [];
           foreach ($ingredients as $ingredient){

               $updatedIngredients[$ingredient->ingredientId] = [
                   'amount' => $ingredient->amount,
                   'unit' => $ingredient->overwriteUnit ?: $ingredient->defaultUnit,
               ];
           }

           $cocktail->ingredients()->sync($updatedIngredients);

           $cocktail->categories()->sync($categoryIds);

           return $cocktail->fresh()->load(CocktailQueryHelper::allowedRelationShips());
        });
    }

    private function sortByStepNumber(array $steps): Collection
    {
        return collect($steps)->sortBy(fn (CreateCocktailStepData $step) => $step->stepNumber);
    }
}
