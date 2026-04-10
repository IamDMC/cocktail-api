<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_public
 * @property int $user_id
 *
 * @property-read float $average_rating
 * @property-read Collection<int, Ingredient> $ingredients
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, CocktailStep> $steps
 * @property-read Collection<int, Rating> $ratings
 * @property-read Collection<int, User> $favoredBy
 *
 * @method static Builder|self public()
 */

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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return HasMany<CocktailStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(CocktailStep::class)
            ->orderBy('step_number');
    }

    /**
     * @return BelongsToMany<Ingredient, $this>
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->using(CocktailIngredient::class)
            ->withPivot('amount', 'unit');
    }

    /**
     * @return HasMany<Rating, $this>
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function favoredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_cocktail');
    }

    // *******************************************
    // * Scopes
    // *******************************************

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    // *******************************************
    // * Accessors
    // *******************************************

    /**
     * @return float
     */
    public function getAverageRatingAttribute(): float
    {
        return (float) $this->ratings()->avg('rating');
    }
}
