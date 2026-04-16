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

    public static function availableSortingAttributes(): array
    {
        return [
            'name',
            'created_at'
        ];
    }

    public static function availableScopes(): array
    {
        return [
            'public',
            'owned',
            'public_or_owned'
        ];
    }
}
