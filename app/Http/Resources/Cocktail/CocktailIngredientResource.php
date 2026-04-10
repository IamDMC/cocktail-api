<?php

namespace App\Http\Resources\Cocktail;

use App\Models\CocktailIngredient;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Ingredient $resource
 * @property CocktailIngredient $pivot
 */
class CocktailIngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,

            'amount' => $this->pivot->amount,
            'unit' => $this->pivot->unit,
        ];
    }
}
