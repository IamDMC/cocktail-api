<?php

namespace App\Actions\Cocktail;

use App\Data\Rating\CreateRatingData;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;

class RateCocktailAction
{
    public function execute(CreateRatingData $data)
    {
        return DB::transaction(function () use ($data){
            return Rating::updateOrCreate(
                [
                    'user_id' => $data->user_id,
                    'cocktail_id' => $data->cocktail_id
                ],
                [
                    'rating' => $data->rating,
                    'comment' => $data->comment
                ]
            );
        });
    }
}
