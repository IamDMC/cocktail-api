<?php

namespace Database\Seeders;

use App\Models\Cocktail;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();
        $cocktails = Cocktail::query()->get();

        foreach ($users as $user){
            foreach ($cocktails as $cocktail){
                Rating::create([
                    'rating' => rand(1,5),
                    'comment' => fake()->sentence(10),
                    'user_id' => $user->id,
                    'cocktail_id' => $cocktail->id,
                ]);
            }
        }
    }
}
