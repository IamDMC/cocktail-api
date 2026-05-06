<?php

namespace App\Actions\Cocktail;

use App\Actions\Image\UploadImageAction;
use App\Data\Cocktail\Create\CreateCocktailData;
use App\Data\Cocktail\Create\CreateCocktailStepData;
use App\Data\Cocktail\Create\CreateCocktailIngredientData;
use App\Models\Cocktail;
use App\Models\CocktailStep;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CreateCocktailAction
{
    /**
     * @param CreateCocktailData $cocktailData
     * @param array<int, CreateCocktailStepData> $steps
     * @param array<int, int> $categoryIds
     * @param array<int, CreateCocktailIngredientData> $ingredients
     * @param UploadedFile|null $image
     * @return Cocktail
     */
    public function execute(
        CreateCocktailData $cocktailData,
        array $steps,
        array $categoryIds,
        array $ingredients,
        ?UploadedFile $image = null
    ): Cocktail
    {
        return DB::transaction(function () use ($cocktailData, $steps, $categoryIds, $ingredients, $image){

            $cocktail = Cocktail::create([
                'name' => $cocktailData->name,
                'description' => $cocktailData->description,
                'is_public' => $cocktailData->isPublic,
                'user_id' => $cocktailData->userId
            ]);

            $steps = $this->sortByStepNumber($steps)->values();

            foreach ($steps as $index => $step){
                CocktailStep::create([
                    'step_number' => $index + 1,
                    'instruction' => $step->instruction,
                    'cocktail_id' => $cocktail->id
                ]);
            }

            $cocktail->categories()->attach($categoryIds);

            foreach ($ingredients as $ingredient){
                $cocktail->ingredients()->attach([
                    $ingredient->ingredientId => [
                        'amount' => $ingredient->amount,
                        'unit' => $ingredient->overwriteUnit ?: $ingredient->defaultUnit,
                    ]
                ]);
            }

            if ($image){
                app(UploadImageAction::class)->execute($cocktail, $image);
            }

            return $cocktail->fresh()->load(['steps', 'categories', 'ingredients', 'image']);
        });
    }

    private function sortByStepNumber(array $steps): Collection
    {
       return collect($steps)->sortBy(fn (CreateCocktailStepData $step) => $step->stepNumber);
    }
}
