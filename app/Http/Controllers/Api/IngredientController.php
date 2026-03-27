<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ingredient\IngredientIndexRequest;
use App\Http\Requests\Ingredient\IngredientStoreRequest;
use App\Http\Requests\Ingredient\IngredientUpdateRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    /**
     * List ingredients.
     *
     * @queryParam per_page int optional Number of items per page (pagination). Example: 10
     * @queryParam limit int optional Limit the number of returned results. Used only if per_page is not set. Example: 5
     *
     * Behavior:
     * - it prioritises per_page over limit
     * - if neither per_page nor limit is provided, all categories are returned
     *
     */
    public function index(IngredientIndexRequest $request)
    {
        $query = Ingredient::query();

        if ($request->filled('per_page')){
            $result = $query->paginate($request->integer('per_page',10));
        } elseif ($request->filled('limit')){
            $result = $query->limit($request->integer('limit', 10))->get();
        } else {
            $result = $query->get();
        }

        return IngredientResource::collection($result);
    }

    /**
     * Create an ingredient.
     *
     * @bodyParam name string required The name of the ingredient. Example: White rum
     * @bodyParam description string required Description of the ingredient. Example: Light rum
     * @bodyParam default_unit string required Default unit of the ingredient. Example: cl
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "White rum",
     *     "description": "Light rum",
     *     "default_unit": "cl",
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."]
     *   }
     * }
     */
    public function store(IngredientStoreRequest $request)
    {
        $ingredient = Ingredient::create($request->validated());

        return (new IngredientResource($ingredient))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show an ingredient.
     *
     * @urlParam category int required The ID of the ingredient. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "White rum",
     *     "description": "Light rum"
     *     "default_unit": "cl"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "No query results for model"
     * }
     */
    public function show(Ingredient $ingredient)
    {
        return new IngredientResource($ingredient);
    }

    /**
     * Update an ingredient.
     *
     * @urlParam ingredient int required The ID of the ingredient. Example: 1
     *
     * @bodyParam name string The name of the ingredient. Example: White rum
     * @bodyParam description string optional Description of the ingredient. Example: Light rum
     * @bodyParam default_unit string Default unit of the ingredient. Example: cl
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "White rum",
     *     "description": "Light rum"
     *     "description": "cl"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid."
     * }
     */
    public function update(IngredientUpdateRequest $request, Ingredient $ingredient)
    {
        $ingredient->update($request->validated());

        return new IngredientResource($ingredient);
    }

    /**
     * Delete an ingredient.
     *
     * @urlParam category int required The ID of the ingredient. Example: 1
     *
     * @response 204
     */
    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->noContent();
    }
}
