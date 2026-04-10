<?php

namespace App\Support\Cocktail;

class CocktailQueryHelper
{
    public static function allowedRelationShips(): array{
        return [
            'user',
            'categories',
            'steps',
            'ingredients',
            'ratings.user',
            'favoredBy',
        ];
    }

    public static function availableFilters(): array
    {
        return [
            'categories',
            'ingredients'
        ];
    }
}
