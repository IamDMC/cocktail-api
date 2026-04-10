<?php

namespace App\Http\Resources\Cocktail;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\RatingResource;
use App\Http\Resources\UserResource;
use App\Models\Cocktail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Cocktail $resource
 */
class CocktailResource extends JsonResource
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
            'description' => $this->resource->description,
            'is_public' => $this->resource->is_public,

            'user' => new UserResource(
                $this->whenLoaded('user')
            ),

            'categories' => CategoryResource::collection(
                $this->whenLoaded('categories')
            ),

            'steps' => CocktailStepResource::collection(
                $this->whenLoaded('steps')
            ),

            'ingredients' => CocktailIngredientResource::collection(
                $this->whenLoaded('ingredients')
            ),

            'ratings' => RatingResource::collection(
                $this->whenLoaded('ratings')
            ),

            'favoredBy' => UserResource::collection(
                $this->whenLoaded('favoredBy')
            )
        ];
    }
}
