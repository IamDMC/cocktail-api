<?php

namespace App\ReadModels\Cocktail;

use App\Models\Cocktail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
class CocktailQuery
{
    /**
     * @param array<string> $relationsToBeLoaded
     * @param string|null $search
     * @param array<int, array{name: string, values: array<int>}> $filters
     * @param array<int, array{attribute: string, direction: string}> $sorting
     * @param int $per_page
     * @param User|null $user
     * @return LengthAwarePaginator
     */
    public function paginate(array $relationsToBeLoaded, ?string $search, array $filters = [], array $sorting = [], int $per_page = 10, ?User $user = null): LengthAwarePaginator
    {
        return $this->baseQuery($relationsToBeLoaded, $search, $filters, $sorting, $user)->paginate($per_page);
    }

    /**
     * @param array<string> $relationsToBeLoaded
     * @param string|null $search
     * @param array<int, array{name: string, values: array<int>}> $filters
     * @param array<int, array{attribute: string, direction: string}> $sorting
     * @param int $limit
     * @param User|null $user
     * @return Collection<int, Cocktail>
     */
    public function limit(array $relationsToBeLoaded, ?string $search, array $filters = [], array $sorting = [], int $limit = 10, ?User $user = null): Collection
    {
        return $this->baseQuery($relationsToBeLoaded, $search, $filters, $sorting, $user)->limit($limit)->get();
    }

    /**
     * @param array<string> $relationsToBeLoaded
     * @param string|null $search
     * @param array<int, array{name: string, values: array<int>}> $filters
     * @param array<int, array{attribute: string, direction: string}> $sorting
     * @param User|null $user
     * @return Builder
     */
    private function baseQuery(array $relationsToBeLoaded, ?string $search, array $filters = [], array $sorting = [], ?User $user = null): Builder
    {
        $query = Cocktail::publicOrOwned($user);

        // Searches through cocktail name and cocktail description
        if (! empty($search)){
            $query = $this->applySearch($query, trim($search));
        }

        if (! empty($filters)){
            foreach ($filters as $filter){
                if(isset($filter['name'], $filter['values'])){
                    $query = $this->applyFilter($query, $filter['name'], $filter['values']);
                }
            }
        }

        if (! empty($relationsToBeLoaded)){
            $query = $this->applyIncludes($query, $relationsToBeLoaded);
        }

        if (!empty($sorting) && isset($sorting[0]['attribute'], $sorting[0]['direction'])) {
            $query = $this->applySorting(
                $query,
                $sorting[0]['attribute'],
                $sorting[0]['direction']
            );
        } else {
            $query = $this->applySorting($query, 'name', 'asc');
        }

       return $query;
    }

    /**
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search){
            $q->where('name', 'like', "%$search%")
                ->orWhere('description', 'like',  "%$search%");
        });
    }

    /**
     * @param Builder $query
     * @param string $attribute
     * @param string $direction
     * @return Builder
     */
    private function applySorting(Builder $query, string $attribute, string $direction): Builder
    {
        return match ($attribute) {
            'name' => $this->applySortByName($query, $direction),
            'created_at' => $this->applySortByCreatedAt($query, $direction),
            default => $this->logUnmatchedSortingName($query, $attribute),
        };
    }

    /**
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    private function applySortByName(Builder $query, string $direction): Builder
    {
        return $query->orderBy('cocktails.name', $direction);
    }

    /**
     * @param Builder $query
     * @param string $direction
     * @return Builder
     */
    private function applySortByCreatedAt(Builder $query, string $direction): Builder
    {
        return $query->orderBy('cocktails.created_at', $direction);
    }

    /**
     * @param Builder $query
     * @param string $filterName
     * @param array $filterValues
     * @return Builder
     */
    private function applyFilter(Builder $query, string $filterName, array $filterValues): Builder
    {
        return match ($filterName) {
            'categories' => $this->applyCategoriesFilter($query, $filterValues),
            'ingredients' => $this->applyIngredientsFilter($query, $filterValues),
            default => $this->logUnmatchedFilterName($query, $filterName),
        };
    }

    /**
     * @param Builder $query
     * @param array<int> $filterValues
     * @return Builder
     */
    private function applyCategoriesFilter(Builder $query, array $filterValues): Builder
    {
        return $query->whereHas('categories', function ($q) use ($filterValues){
            $q->whereIn('id', $filterValues);
        });
    }

    /**
     * @param Builder $query
     * @param array<int> $filterValues
     * @return Builder
     */
    private function applyIngredientsFilter(Builder $query, array $filterValues): Builder
    {
        return $query->whereHas('ingredients', function ($q) use ($filterValues){
            $q->whereIn('ingredients.id', $filterValues);
        });
    }

    /**
     * @param Builder $query
     * @param array<string> $relationsToBeLoaded
     * @return Builder
     */
    private function applyIncludes(Builder $query, array $relationsToBeLoaded): Builder
    {
        return $query->with($relationsToBeLoaded);
    }

    /**
     * @param Builder $query
     * @param string $filterName
     * @return Builder
     */
    private function logUnmatchedFilterName(Builder $query, string $filterName): Builder
    {
        Log::warning('Unknown filter', [
            'filter' => $filterName,
        ]);

        return $query;
    }

    /**
     * @param Builder $query
     * @param string $attribute
     * @return Builder
     */
    private function logUnmatchedSortingName(Builder $query, string $attribute): Builder
    {
        Log::warning('Unknown sorting attribute', [
            'attribute' => $attribute,
        ]);

        return $query;
    }
}
