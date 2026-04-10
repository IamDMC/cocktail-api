<?php

namespace App\Data\Cocktail\Update;
class UpdateCocktailData
{
    /**
     * @param string $name
     * @param string|null $description
     * @param bool $isPublic
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $isPublic,
    ){}
}
