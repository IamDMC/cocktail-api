<?php

namespace App\Models;

use App\Enums\Unit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $cocktail_id
 * @property int $ingredient_id
 * @property float $amount
 * @property Unit $unit
 */
class CocktailIngredient extends Pivot
{
    protected $table = 'cocktail_ingredient';

    protected $fillable = [
        'cocktail_id',
        'ingredient_id',
        'amount',
        'unit',
    ];

    protected $casts = [
        'amount' => 'float',
        'unit' => Unit::class
    ];

    /**
     * @return BelongsTo<Cocktail, $this>
     */
    public function cocktail(): BelongsTo
    {
        return $this->belongsTo(Cocktail::class);
    }

    /**
     * @return BelongsTo<Ingredient, $this>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
