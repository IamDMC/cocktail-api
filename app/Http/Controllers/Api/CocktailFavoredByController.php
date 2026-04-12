<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cocktail\ToggleFavoriteCocktailAction;
use App\Http\Controllers\Controller;
use App\Models\Cocktail;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Cocktail-Favorites', description: 'Allows the user to add or remove cocktails from favorites')]
class CocktailFavoredByController extends Controller
{
    #[UrlParam('cocktail', 'int', 'The ID of the cocktail', example: 1)]
    #[Response(status: 204, description: 'Cocktail added to favorites')]
    #[Response(status: 404, description: 'Cocktail not found')]
    public function store(Cocktail $cocktail)
    {
        app(ToggleFavoriteCocktailAction::class)->add($cocktail);

        return response()->noContent();
    }

    #[UrlParam('cocktail', 'int', 'The ID of the cocktail', example: 1)]
    #[Response(status: 204, description: 'Cocktail removed from favorites')]
    #[Response(status: 404, description: 'Cocktail not found')]
    public function destroy(Cocktail $cocktail)
    {
        app(ToggleFavoriteCocktailAction::class)->remove($cocktail);

        return response()->noContent();
    }
}
