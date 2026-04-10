<?php

namespace Database\Seeders;

use App\Models\Cocktail;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserFavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cocktailIds = Cocktail::query()->get()->modelKeys();

        $users = User::query()->get();

        foreach ($users as $user){
            $user->favoriteCocktails()->attach($cocktailIds);
        }
    }
}
