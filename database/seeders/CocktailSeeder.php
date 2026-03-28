<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Cocktail;
use App\Models\CocktailStep;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use phpDocumentor\Reflection\Types\Self_;

class CocktailSeeder extends Seeder
{
    private const COCKTAIL_PER_USER = 2;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
         * Create x cocktails for each user
         */
        $users = User::all();

        /*
         *  Fetch base data (ingredients and categories) to create cocktail
         */
        $ingredients = Ingredient::query()->get();
        $categories = Category::query()->get();

        foreach ($users as $user){

            for ($nrCocktails = 1; $nrCocktails <= self::COCKTAIL_PER_USER; $nrCocktails++){

                /*
                 *  Create Cocktail with steps
                 */
                $cocktail = $this->createCocktail($user);
                $nrSteps = rand(1,5);
                $this->createCocktailSteps($cocktail, $nrSteps);


                /*
                 *  Attach categories to cocktail
                 */
                $nrCategories = rand(1,$categories->count());
                $selectedCategories = $this->getSelectedCategories($nrCategories, $categories);
                $this->attachCategoriesToCocktail($cocktail, $selectedCategories);


                /*
                 *  Attach categories to cocktail
                 */
                $nrIngredients = rand(1,$ingredients->count());
                $usedIngredients = $this->getSelectedIngredients($nrIngredients, $ingredients);
                $this->attachIngredientsToCocktail($cocktail, $usedIngredients);
            }
        }
    }

    private function createCocktail(User $user): Cocktail
    {
        return Cocktail::create([
            'name' => fake()->name . "-Cocktail",
            'description' => fake()->sentence(10),
            'is_public' => true,
            'user_id' => $user->id
        ]);
    }

    private function getSelectedCategories(int $nrCategories, Collection $categories): array
    {
        $usedCategories = [];

        for ($i = 1; $i <= $nrCategories; $i++)
        {
            $possibleCategory = $categories->except($usedCategories);

            if ($possibleCategory->isEmpty()) break;

            $category = $possibleCategory->random();

            $usedCategories[] = $category->id;
        }

        return $usedCategories;
    }

    private function createCocktailSteps(Cocktail $cocktail, $nrSteps): void
    {
        for ($i = 1; $i <= $nrSteps ; $i++)
        {
            CocktailStep::create([
                'step_number' => $i,
                'instruction' => fake()->sentence(rand(3,8)),
                'cocktail_id' => $cocktail->id
            ]);
        }
    }

    private function getSelectedIngredients(int $nrIngredients, Collection $ingredients): array
    {
        $usedIngredients = [];

        for ($i = 1; $i <= $nrIngredients; $i++)
        {
            $possibleIngredient = $ingredients->except($usedIngredients);

            if ($possibleIngredient->isEmpty()) break;

            $ingredient = $possibleIngredient->random();

            $usedIngredients[] = $ingredient->id;
        }

        return $usedIngredients;
    }

    private function attachCategoriesToCocktail(Cocktail $cocktail, array $categoryIds): void
    {
        $cocktail->categories()->attach($categoryIds);
    }

    private function attachIngredientsToCocktail(Cocktail $cocktail, array $ingredientIds): void
    {
        $ingredients = Ingredient::query()->whereIn('id', $ingredientIds)->get();

        foreach ($ingredients as $ingredient){
            $cocktail->ingredients()->attach($ingredient->id, [
                'amount' => rand(1,20),
                'unit' => $ingredient->default_unit->value
            ]);
        }
    }
}
