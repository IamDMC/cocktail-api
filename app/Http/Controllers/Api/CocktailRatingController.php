<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cocktail\RateCocktailAction;
use App\Data\Rating\CreateRatingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rating\CocktailRatingRequest;
use App\Http\Resources\IngredientResource;
use App\Http\Resources\RatingResource;
use App\Models\Cocktail;
use App\Models\Ingredient;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;
#[Group('Cocktail-Rating', description: 'Create or update a user rating for a cocktail')]
class CocktailRatingController extends Controller
{
    #[UrlParam('cocktail', 'int', 'The ID of the cocktail', example: 1)]
    #[BodyParam('rating', 'int', 'The rating of the cocktail', required: true, example: 1)]
    #[BodyParam('comment', 'string', 'Comment of the rating', required: false, example: 'Difficult to prepare')]
    #[ResponseFromApiResource(RatingResource::class, status: 201)]
    #[Response(status: 422, description: 'Validation error')]
    #[Response(status: 404, description: 'Cocktail not found')]
    public function store(CocktailRatingRequest $request, Cocktail $cocktail)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $ratingDto = new CreateRatingData(
            rating: $request->integer('rating', 1),
            comment: $request->filled('comment')
                ? $request->string('comment')
                : null,
            user_id: $user->id,
            cocktail_id: $cocktail->id
        );

        $rating = app(RateCocktailAction::class)->execute($ratingDto);

        return (new RatingResource($rating))
            ->response()->setStatusCode(201);
    }
    #[UrlParam('cocktail', 'int', 'The ID of the cocktail', example: 1)]
    #[BodyParam('rating', 'int', 'The rating of the cocktail', required: true, example: 1)]
    #[BodyParam('comment', 'string', 'Comment of the rating', required: false, example: 'Difficult to prepare')]
    #[ResponseFromApiResource(RatingResource::class, status:200)]
    #[Response(status: 422, description: 'Validation error')]
    #[Response(status: 404, description: 'Cocktail not found')]
    public function update(CocktailRatingRequest $request, Cocktail $cocktail)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $ratingDto = new CreateRatingData(
            rating: $request->integer('rating', 1),
            comment: $request->filled('comment')
                ? $request->string('comment')
                : null,
            user_id: $user->id,
            cocktail_id: $cocktail->id
        );

        $rating = app(RateCocktailAction::class)->execute($ratingDto);

        return new RatingResource($rating);
    }
}
