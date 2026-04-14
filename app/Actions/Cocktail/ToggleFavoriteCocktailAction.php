<?php

namespace App\Actions\Cocktail;

use App\Models\Cocktail;
use App\Models\User;

class ToggleFavoriteCocktailAction
{
    /**
     * @param Cocktail $cocktail
     * @return void
     */
    public function add(Cocktail $cocktail, User $user): void
    {
        $cocktail->favoredBy()->attach([$user->id]);
    }

    /**
     * @param Cocktail $cocktail
     * @return void
     */
    public function remove(Cocktail $cocktail, User $user): void
    {
        $cocktail->favoredBy()->detach([$user->id]);
    }
}
