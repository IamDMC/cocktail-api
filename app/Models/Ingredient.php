<?php

namespace App\Models;

use App\Enums\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    /** @use HasFactory<\Database\Factories\IngredientFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'default_unit'
    ];

    protected $casts = [
        'default_unit' => Unit::class
    ];

    public function cocktails(): BelongsToMany
    {
        return $this->belongsToMany(Cocktail::class)
            ->withPivot('amount', 'unit');
    }
}
