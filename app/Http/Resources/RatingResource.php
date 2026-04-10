<?php

namespace App\Http\Resources;

use App\Http\Resources\Cocktail\CocktailResource;
use App\Models\Cocktail;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Rating $resource
 */
class RatingResource extends JsonResource
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
            'rating' => $this->resource->rating,
            'comment' => $this->resource->comment,

            'user' => new UserResource(
                $this->whenLoaded('user')
            ),

            'cocktail' => new CocktailResource(
                $this->whenLoaded('cocktails')
            )
        ];
    }
}
