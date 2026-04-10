<?php

namespace App\Http\Resources\Cocktail;

use App\Models\CocktailStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CocktailStep $resource
 */
class CocktailStepResource extends JsonResource
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
            'step_number' => $this->resource->step_number,
            'instruction' => $this->resource->instruction,
        ];
    }
}
