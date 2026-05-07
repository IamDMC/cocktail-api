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
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Cocktails', description: 'Manage cocktails')]
class CocktailController extends Controller
{
    use AuthorizesRequests;
    #[Authenticated]

    #[QueryParam('include[]', 'string','Relations to include. Possible values: user, categories, steps, ingredients, ratings.user, favoredBy, image',
        required: false,
        example: 'categories'
    )]

    #[QueryParam('search', 'string', 'Search by cocktail name or description',
        required: false,
        example: 'mojito'
    )]

    #[QueryParam('scope', 'string', 'Visibility scope. Possible values: public, owned, public_or_owned',
        required: false,
        example: 'public_or_owned'
    )]

    #[QueryParam('sorting[0][attribute]', 'string', 'Sort attribute. Possible values: name, created_at',
        required: false,
        example: 'name'
    )]

    #[QueryParam('sorting[0][direction]', 'string', 'Sort direction. Possible values: asc, desc',
        required: false,
        example: 'asc'
    )]

    #[QueryParam('filter[0][name]', 'string', 'Filter name. Possible values: categories, ingredients',
        required: false,
        example: 'categories'
    )]

    #[QueryParam('filter[0][values][]', 'integer', 'Filter values',
        required: false,
        example: 1
    )]

    #[QueryParam('per_page', 'integer', 'Paginate results',
        required: false,
        example: 10
    )]

    #[QueryParam('limit', 'integer', 'Limit results if pagination is not used',
        required: false,
        example: 5
    )]
    public function index(CocktailIndexRequest $request)
    {
        $relationsToBeLoaded = $request->validated('include', []);
        $search = $request->validated('search', '');
        $scope = $request->validated('scope', 'public_or_owned');
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

    #[Authenticated]
    #[BodyParam('name', 'string', 'Cocktail name', example: 'Mojito')]
    #[BodyParam('description', 'string', 'Cocktail description', required: false, example: 'Fresh cocktail with mint')]
    #[BodyParam('isPublic', 'boolean', 'Whether cocktail is public', example: true)]

    #[BodyParam('steps', 'object[]', 'Preparation steps')]
    #[BodyParam('steps[].stepNumber', 'integer', 'Step order', example: 1)]
    #[BodyParam('steps[].instruction', 'string', 'Instruction text', example: 'Mix ingredients')]

    #[BodyParam('ingredients', 'object[]', 'Ingredients list')]
    #[BodyParam('ingredients[].id', 'integer', 'Ingredient ID', example: 1)]
    #[BodyParam('ingredients[].amount', 'number', 'Ingredient amount', example: 2)]
    #[BodyParam('ingredients[].overwriteUnit', 'string', 'Optional unit override', required: false, example: 'ml')]

    #[BodyParam('categoryIds', 'integer[]', 'Category IDs', example: [1, 2])]

    #[BodyParam(
        'image',
        'file',
        'Cocktail image',
        required: false
    )]
    public function store(CocktailStoreRequest $request)
    {
        $data = CocktailDtoHelper::toCreateDto($request);

        $cocktail = app(CreateCocktailAction::class)->execute(
            cocktailData: $data['cocktailDto'],
            steps: $data['stepsDto'],
            categoryIds: $data['categoriesArray'],
            ingredients: $data['ingredientsDto'],
            image: $request->file('image')
        );

        return (new CocktailResource($cocktail))
            ->response()->setStatusCode(201);
    }
    #[Authenticated]
    public function show(CocktailShowRequest $request, Cocktail $cocktail)
    {
        $result = $cocktail->load($request->validated('include', []));

        return new CocktailResource($result);
    }
    #[Authenticated]
    #[UrlParam('cocktail', 'integer', 'Cocktail ID', example: 1)]

    #[BodyParam('name', 'string', 'Cocktail name', required: false, example: 'Updated Mojito')]
    #[BodyParam('description', 'string', 'Cocktail description', required: false, example: 'Updated fresh cocktail with mint')]
    #[BodyParam('isPublic', 'boolean', 'Whether cocktail is public', required: false, example: true)]

    #[BodyParam('steps', 'object[]', 'Preparation steps', required: false)]
    #[BodyParam('steps[].stepNumber', 'integer', 'Step order', example: 1)]
    #[BodyParam('steps[].instruction', 'string', 'Instruction text', example: 'Mix ingredients')]

    #[BodyParam('ingredients', 'object[]', 'Ingredients list', required: false)]
    #[BodyParam('ingredients[].id', 'integer', 'Ingredient ID', example: 1)]
    #[BodyParam('ingredients[].amount', 'number', 'Ingredient amount', example: 2)]
    #[BodyParam('ingredients[].overwriteUnit', 'string', 'Optional unit override', required: false, example: 'ml')]

    #[BodyParam('categoryIds', 'integer[]', 'Category IDs', required: false, example: [1, 2])]

    #[BodyParam(
        'image',
        'file',
        'Cocktail image',
        required: false
    )]
    public function update(CocktailUpdateRequest $request, Cocktail $cocktail)
    {
        $data = CocktailDtoHelper::toUpdateDto($request);

        $cocktail = app(UpdateCocktailAction::class)->execute(
            cocktail: $cocktail,
            updateCocktailData: $data['cocktailDto'],
            steps: $data['stepsDto'],
            categoryIds: $data['categoriesArray'],
            ingredients: $data['ingredientsDto'],
            image: $request->file('image')
        );

        return new CocktailResource($cocktail);
    }

    #[Authenticated]
    public function destroy(Cocktail $cocktail)
    {
        $this->authorize('delete', $cocktail);

        $cocktail->delete();

        return response()->noContent();
    }

}
