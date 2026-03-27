<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryIndexRequest;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\QueryParam;

class CategoryController extends Controller
{
    /**
     * List categories.
     *
     * @queryParam per_page int optional Number of items per page (pagination). Example: 10
     * @queryParam limit int optional Limit the number of returned results. Used only if per_page is not set. Example: 5
     *
     * Behavior:
     * - it prioritises per_page over limit
     * - if neither per_page nor limit is provided, all categories are returned
     *
     */
    public function index(CategoryIndexRequest $request)
    {
        $query = Category::query();

        if ($request->filled('per_page')){
            $result = $query->paginate($request->integer('per_page', 10));
        }elseif ($request->filled('limit')){
            $result = $query->limit($request->integer('limit', 10))->get();
        }else{
            $result = $query->get();
        }

        return CategoryResource::collection($result);
    }

    /**
     * Create a category.
     *
     * @bodyParam name string required The name of the category. Example: Cocktails
     * @bodyParam description string required Description of the category. Example: Alcoholic drinks
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "name": "Cocktails",
     *     "description": "Alcoholic drinks"
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
    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a category.
     *
     * @urlParam category int required The ID of the category. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Cocktails",
     *     "description": "Alcoholic drinks"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "No query results for model"
     * }
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update a category.
     *
     * @urlParam category int required The ID of the category. Example: 1
     *
     * @bodyParam name string optional The name of the category. Example: Cocktails
     * @bodyParam description string optional Description of the category. Example: Alcoholic drinks
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Cocktails",
     *     "description": "Alcoholic drinks"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid."
     * }
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Delete a category.
     *
     * @urlParam category int required The ID of the category. Example: 1
     *
     * @response 204
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
