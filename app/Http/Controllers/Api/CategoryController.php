<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryIndexRequest;
use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('Categories', description: 'Manage cocktail categories')]
class CategoryController extends Controller
{
    #[QueryParam('per_page', 'int', 'Number of items per page (pagination)', required: false, example: 10)]
    #[QueryParam('limit', 'int', 'Limit results if per_page is not set', required: false, example: 5)]
    public function index(CategoryIndexRequest $request)
    {
        $query = Category::query();

        if ($request->filled('per_page')){
            $result = $query->paginate($request->integer('per_page', 10));
        } elseif ($request->filled('limit')){
            $result = $query->limit($request->integer('limit', 10))->get();
        } else {
            $result = $query->get();
        }

        return CategoryResource::collection($result);
    }

    #[BodyParam('name', 'string', 'The name of the category', example: 'Cocktails')]
    #[BodyParam('description', 'string', 'Description of the category', example: 'Alcoholic drinks')]
    #[ResponseFromApiResource(CategoryResource::class, Category::class, status: 201)]
    public function store(CategoryStoreRequest $request)
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    #[UrlParam('category', 'int', 'The ID of the category', example: 1)]
    #[ResponseFromApiResource(CategoryResource::class, Category::class)]
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    #[UrlParam('category', 'int', 'The ID of the category', example: 1)]
    #[BodyParam('name', 'string', 'The name of the category', required: false, example: 'Cocktails')]
    #[BodyParam('description', 'string', 'Description of the category', required: false, example: 'Alcoholic drinks')]
    #[ResponseFromApiResource(CategoryResource::class, Category::class)]
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    #[UrlParam('category', 'int', 'The ID of the category', example: 1)]
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
