<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cocktail\CreateCocktailAction;
use App\Actions\Cocktail\UpdateCocktailAction;
use App\Data\Cocktail\CocktailDtoHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cocktail\CocktailIndexRequest;
use App\Http\Requests\Cocktail\CocktailStoreRequest;
use App\Http\Requests\Cocktail\CocktailShowRequest;
use App\Http\Requests\Cocktail\CocktailUpdateRequest;
use App\Http\Resources\Cocktail\CocktailResource;
use App\Models\Cocktail;
use App\ReadModels\Cocktail\CocktailQuery;
use Illuminate\Auth\Access\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Cocktails', description: 'Manage cocktails')]
class CocktailController extends Controller
{
    use AuthorizesRequests;

    #[QueryParam('include', 'array', 'Relations to include (user, categories, steps, ingredients, ratings.user, favoredBy)', required: false)]
    #[QueryParam('search', 'string', 'Search term for name/description', required: false)]
    #[QueryParam('filter', 'array', 'Filter options', required: false)]
    #[QueryParam('sorting', 'array', 'Sorting options', required: false)]
    #[QueryParam('per_page', 'int', 'Paginate results', required: false)]
    #[QueryParam('limit', 'int', 'Limit results (if not paginated)', required: false)]
    public function index(CocktailIndexRequest $request)
    {
        $relationsToBeLoaded = $request->validated('include', []);
        $search = $request->validated('search', '');
        $scope = $request->validated('scope', 'public_or_owned');;
        $filter = $request->validated('filter', []);
        $sorting = $request->validated('sorting', []);
        $user = $request->user();

        $baseQuery = (new CocktailQuery())
            ->forScope($scope, $user)
            ->search($search)
            ->filter($filter)
            ->withRelations($relationsToBeLoaded)
            ->withStats()
            ->sort($sorting);

        if ($request->filled('per_page')){
            $result = $baseQuery->paginate($request->integer('per_page', 10));
        } else {
            $result = $baseQuery->limit($request->integer('limit', 10));
        }

        return CocktailResource::collection($result);
    }


    #[BodyParam('name', 'string', 'Name of the cocktail', example: 'Mojito')]
    #[BodyParam('description', 'string', 'Description', required: false, example: 'Fresh cocktail with mint')]
    #[BodyParam('isPublic', 'boolean', 'Is cocktail public', example: true)]
    #[BodyParam('steps', 'array', 'Preparation steps')]
    #[BodyParam('steps[].stepNumber', 'int', 'Step order', example: 1)]
    #[BodyParam('steps[].instruction', 'string', 'Instruction text', example: 'Mix ingredients')]
    #[BodyParam('ingredients', 'array', 'Ingredients list')]
    #[BodyParam('ingredients[].id', 'int', 'Ingredient ID', example: 1)]
    #[BodyParam('ingredients[].amount', 'float', 'Amount', example: 2)]
    #[BodyParam('ingredients[].overwriteUnit', 'string', 'Optional unit override', required: false, example: 'ml')]
    #[BodyParam('categoryIds', 'array', 'Category IDs')]
    #[BodyParam('categoryIds[]', 'int', 'Category ID', example: 1)]
    public function store(CocktailStoreRequest $request)
    {
        $data = CocktailDtoHelper::toCreateDto($request);

        $cocktail = app(CreateCocktailAction::class)->execute(
            cocktailData: $data['cocktailDto'],
            steps: $data['stepsDto'],
            categoryIds: $data['categoriesArray'],
            ingredients: $data['ingredientsDto']
        );

        return (new CocktailResource($cocktail))
            ->response()->setStatusCode(201);
    }

    #[QueryParam('include', 'array', 'Relations to include (user, categories, steps, ingredients, ratings.user, favoredBy)', required: false)]
    public function show(CocktailShowRequest $request, Cocktail $cocktail)
    {
        $result = $cocktail->load($request->validated('include', []));

        return new CocktailResource($result);
    }

    #[BodyParam('name', 'string', 'Name of the cocktail', example: 'Updated Mojito')]
    #[BodyParam('description', 'string', 'Description', required: false)]
    #[BodyParam('isPublic', 'boolean', 'Is cocktail public')]
    #[BodyParam('steps', 'array', 'Steps')]
    #[BodyParam('ingredients', 'array', 'Ingredients')]
    #[BodyParam('categoryIds', 'array', 'Category IDs')]
    public function update(CocktailUpdateRequest $request, Cocktail $cocktail)
    {
        $data = CocktailDtoHelper::toUpdateDto($request);

        $cocktail = app(UpdateCocktailAction::class)->execute(
            cocktail: $cocktail,
            updateCocktailData: $data['cocktailDto'],
            steps: $data['stepsDto'],
            categoryIds: $data['categoriesArray'],
            ingredients: $data['ingredientsDto']
        );

        return new CocktailResource($cocktail);
    }

    #[UrlParam('cocktail', 'int', 'The ID of the cocktail', example: 1)]
    public function destroy(Cocktail $cocktail)
    {
        $this->authorize('delete', $cocktail);

        $cocktail->delete();

        return response()->noContent();
    }

}
