<?php

namespace App\Data\Cocktail\Create;

final readonly class CreateCocktailData
{
    /**
     * @param string $name
     * @param string|null $description
     * @param bool $isPublic
     * @param int $userId
     */
    public function __construct(
        public string $name,
        public ?string $description,
        public bool $isPublic,
        public int $userId,
    ){}

}
