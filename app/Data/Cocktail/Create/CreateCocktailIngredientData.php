<?php

namespace App\Data\Cocktail\Create;

use App\Enums\Unit;

class CreateCocktailIngredientData
{
    /**
     * @param int $ingredientId
     * @param float $amount
     * @param Unit $defaultUnit
     * @param Unit|null $overwriteUnit
     */
    public function __construct(
        public int $ingredientId,
        public float $amount,
        public Unit $defaultUnit,
        public ?Unit $overwriteUnit
    ) {}
}
