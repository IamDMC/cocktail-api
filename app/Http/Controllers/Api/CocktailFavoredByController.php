<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cocktail\ToggleFavoriteCocktailAction;
use App\Http\Controllers\Controller;
use App\Models\Cocktail;
use App\Models\User;

use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Cocktail-Favorites', description: 'Allows the user to add or remove cocktails from favorites')]
class CocktailFavoredByController extends Controller
{
    #[UrlParam('cocktail', 'int', 'The ID of the cocktail', example: 1)]
    #[Response(status: 204, description: 'Cocktail added to favorites')]
    #[Response(status: 404, description: 'Cocktail not found')]
    public function store(Request $request, Cocktail $cocktail)
    {
        /**
         * @var User $user
         */
        $user = $request->user();

        app(ToggleFavoriteCocktailAction::class)->add($cocktail, $user);

        return response()->noContent();
    }

    #[UrlParam('cocktail', 'int', 'The ID of the cocktail', example: 1)]
    #[Response(status: 204, description: 'Cocktail removed from favorites')]
    #[Response(status: 404, description: 'Cocktail not found')]
    public function destroy(Request $request, Cocktail $cocktail)
    {
        /**
         * @var User $user
         */
        $user = $request->user();

        app(ToggleFavoriteCocktailAction::class)->remove($cocktail, $user);

        return response()->noContent();
    }
}
