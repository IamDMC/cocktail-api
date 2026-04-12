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
    public function add(Cocktail $cocktail): void
    {
        /** @var User $user */
        //$user = auth()->user();
        $user = User::query()->first();

        $cocktail->favoredBy()->attach([$user->id]);
    }

    /**
     * @param Cocktail $cocktail
     * @return void
     */
    public function remove(Cocktail $cocktail): void
    {
        /** @var User $user */
        //$user = auth()->user();
        $user = User::query()->first();

        $cocktail->favoredBy()->detach([$user->id]);
    }
}
