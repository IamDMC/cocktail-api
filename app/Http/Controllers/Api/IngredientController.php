<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ingredient\IngredientIndexRequest;
use App\Http\Requests\Ingredient\IngredientStoreRequest;
use App\Http\Requests\Ingredient\IngredientUpdateRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Ingredients', description: 'Manage cocktail ingredients')]
class IngredientController extends Controller
{
    use AuthorizesRequests;

    #[QueryParam('per_page', 'int', 'Number of items per page (pagination)', required: false, example: 10)]
    #[QueryParam('limit', 'int', 'Limit results if per_page is not set', required: false, example: 5)]
    public function index(IngredientIndexRequest $request)
    {
        $query = Ingredient::query();

        if ($request->filled('per_page')){
            $result = $query->paginate($request->integer('per_page', 10));
        } elseif ($request->filled('limit')){
            $result = $query->limit($request->integer('limit', 10))->get();
        } else {
            $result = $query->get();
        }

        return IngredientResource::collection($result);
    }

    #[BodyParam('name', 'string', 'The name of the ingredient', example: 'White rum')]
    #[BodyParam('description', 'string', 'Description of the ingredient', example: 'Light rum')]
    #[BodyParam('default_unit', 'string', 'Default unit of the ingredient', example: 'cl')]
    #[ResponseFromApiResource(IngredientResource::class, Ingredient::class, status: 201)]
    public function store(IngredientStoreRequest $request)
    {
        $ingredient = Ingredient::create($request->validated());

        return (new IngredientResource($ingredient))
            ->response()
            ->setStatusCode(201);
    }

    #[UrlParam('ingredient', 'int', 'The ID of the ingredient', example: 1)]
    #[ResponseFromApiResource(IngredientResource::class, Ingredient::class)]
    public function show(Ingredient $ingredient)
    {
        return new IngredientResource($ingredient);
    }

    #[UrlParam('ingredient', 'int', 'The ID of the ingredient', example: 1)]
    #[BodyParam('name', 'string', 'The name of the ingredient', required: false, example: 'White rum')]
    #[BodyParam('description', 'string', 'Description of the ingredient', required: false, example: 'Light rum')]
    #[BodyParam('default_unit', 'string', 'Default unit of the ingredient', required: false, example: 'cl')]
    #[ResponseFromApiResource(IngredientResource::class, Ingredient::class)]
    public function update(IngredientUpdateRequest $request, Ingredient $ingredient)
    {
        $ingredient->update($request->validated());

        return new IngredientResource($ingredient);
    }

    #[UrlParam('ingredient', 'int', 'The ID of the ingredient', example: 1)]
    public function destroy(Ingredient $ingredient)
    {
        $this->authorize('delete', $ingredient);

        $ingredient->delete();

        return response()->noContent();
    }
}
