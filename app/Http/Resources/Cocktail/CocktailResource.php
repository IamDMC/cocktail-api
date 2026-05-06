<?php

namespace App\Http\Resources\Cocktail;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ImageResource;
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

            'average_rating' => $this->when(
                isset($this->average_rating),
                (float) $this->resource->average_rating
            ),

            'favored_by_count' => $this->when(
                isset($this->favored_by_count),
                $this->resource->favored_by_count
            ),

            'user' => $this->whenLoaded(
                'user',
                fn () => new UserResource($this->resource->user)
            ),

            'categories' => $this->whenLoaded(
                'categories',
                fn () => CategoryResource::collection($this->resource->categories)
            ),

            'steps' => $this->whenLoaded(
                'steps',
                fn () => CocktailStepResource::collection($this->resource->steps)
            ),

            'ingredients' => $this->whenLoaded(
                'ingredients',
                fn () => CocktailIngredientResource::collection($this->resource->ingredients)
            ),

            'ratings' => $this->whenLoaded(
                'ratings',
                fn () => RatingResource::collection($this->resource->ratings)
            ),

            'favoredBy' => $this->whenLoaded(
                'favoredBy',
                fn () => UserResource::collection($this->resource->favoredBy)
            ),

            'image' => $this->whenLoaded(
                'image',
                fn () => new ImageResource($this->resource->image)
            ),
        ];
    }
}
