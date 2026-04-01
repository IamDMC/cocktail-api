<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $step_number
 * @property string $instruction
 * @property int $cocktail_id
 *
 */
class CocktailStep extends Model
{
    /** @use HasFactory<\Database\Factories\CocktailStepFactory> */
    use HasFactory;

    protected $fillable = [
        'step_number',
        'instruction',
        'cocktail_id',
    ];

    /**
     * @return BelongsTo<Cocktail, $this>
     */
    public function cocktail(): BelongsTo
    {
        return $this->belongsTo(Cocktail::class);
    }
}
