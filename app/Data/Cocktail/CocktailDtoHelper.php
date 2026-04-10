<?php

namespace App\Data\Cocktail;

use App\Data\Cocktail\Create\CreateCocktailData;
use App\Data\Cocktail\Create\CreateCocktailIngredientData;
use App\Data\Cocktail\Create\CreateCocktailStepData;
use App\Data\Cocktail\Update\UpdateCocktailData;
use App\Enums\Unit;
use App\Http\Requests\Cocktail\CocktailStoreRequest;
use App\Http\Requests\Cocktail\CocktailUpdateRequest;
use App\Models\Ingredient;
use App\Models\User;

class CocktailDtoHelper
{
    /**
     * @param CocktailStoreRequest $request
     * @return array{
     *     cocktailDto: CreateCocktailData,
     *     stepsDto: array<int, CreateCocktailStepData>,
     *     ingredientsDto: array<int, CreateCocktailIngredientData>,
     *     categoriesArray: array<int, int>
     * }
     */
    public static function toCreateDto(CocktailStoreRequest $request): array
    {
        $data = $request->validated();

        $cocktailDto = new CreateCocktailData(
            name: $data['name'],
            description: $data['description'],
            isPublic: $data['isPublic'],
            userId: User::query()->first()->id          // currently hard coded user, replace with actual auth user, after implementing jwt
        );

        $stepsDto = self::getStepsDto($data['steps']);

        $ingredientsDto = self::getIngredientsDto($data['ingredients']);

        return [
            'cocktailDto' => $cocktailDto,
            'stepsDto' => $stepsDto,
            'ingredientsDto' => $ingredientsDto,
            'categoriesArray' => $data['categoryIds']
        ];
    }

    /**
     * @param CocktailUpdateRequest $request
     * @return array{
     *     cocktailDto: UpdateCocktailData,
     *     stepsDto: array<int, CreateCocktailStepData>,
     *     ingredientsDto: array<int, CreateCocktailIngredientData>,
     *     categoriesArray: array<int, int>
     * }
     */
    public static function toUpdateDto(CocktailUpdateRequest $request): array
    {
        $data = $request->validated();

        $cocktailDto = new UpdateCocktailData(
            name: $data['name'],
            description: $data['description'],
            isPublic: $data['isPublic'],
        );

        $stepsDto = self::getStepsDto($data['steps']);

        $ingredientsDto = self::getIngredientsDto($data['ingredients']);

        return [
            'cocktailDto' => $cocktailDto,
            'stepsDto' => $stepsDto,
            'ingredientsDto' => $ingredientsDto,
            'categoriesArray' => $data['categoryIds']
        ];
    }

    /**
     * @param array<int, array{
     *     stepNumber: int,
     *     instruction: string
     * }> $steps
     * @return array<int, CreateCocktailStepData>
     */
    private static function getStepsDto(array $steps): array
    {
        $stepsDto = [];
        foreach ($steps as $step){
            $stepsDto[] = new CreateCocktailStepData(
                stepNumber: $step['stepNumber'],
                instruction: $step['instruction']
            );
        }

        return $stepsDto;
    }

    /**
     * @param array<int, array{
     *     id: int,
     *     amount: float|int,
     *     overwriteUnit?: string|null
     * }> $ingredients
     * @return array<int, CreateCocktailIngredientData>
     */
    private static function getIngredientsDto(array $ingredients): array
    {
        $ingredientsDto = [];
        foreach ($ingredients as $ingredient){
            $ingredientsDto[] = new CreateCocktailIngredientData(
                ingredientId: $ingredient['id'],
                amount: $ingredient['amount'],
                defaultUnit:  self::getDefaultUnit($ingredient['id']),
                overwriteUnit: isset($ingredient['overwriteUnit'])
                    ? Unit::tryFrom($ingredient['overwriteUnit'])
                    : null
            );
        }

        return $ingredientsDto;
    }

    /**
     * @param int $ingredientId
     * @return Unit
     */
    private static function getDefaultUnit(int $ingredientId): Unit
    {
        return Ingredient::query()->findOrFail($ingredientId)->default_unit;
    }
}
