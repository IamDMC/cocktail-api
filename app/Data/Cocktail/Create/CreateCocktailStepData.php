<?php

namespace App\Data\Cocktail\Create;

final readonly class CreateCocktailStepData
{
    /**
     * @param int $stepNumber
     * @param string $instruction
     */
    public function __construct(
        public int $stepNumber,
        public string $instruction,
    ) {}
}
