<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cocktail extends Model
{
    /** @use HasFactory<\Database\Factories\CocktailFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_public',
        'user_id',
    ];

    protected $casts = [
        'is_public' => 'bool'
    ];

    // *******************************************
    // * Relationships
    // *******************************************

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(CocktailStep::class)
            ->orderBy('step_number');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot('amount', 'unit');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function favoredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    // *******************************************
    // * Scopes
    // *******************************************

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // *******************************************
    // * Accessors
    // *******************************************

    public function getAverageRatingAttribute(): float
    {
        return (float) $this->ratings()->avg('rating');
    }
}
