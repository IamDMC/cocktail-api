<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = [
        'rating',
        'comment',
        'user_id',
        'cocktail_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cocktail(): BelongsTo
    {
        return $this->belongsTo(Cocktail::class);
    }
}
