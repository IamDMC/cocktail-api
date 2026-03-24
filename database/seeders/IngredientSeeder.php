<?php

namespace Database\Seeders;

use App\Enums\Unit;
use App\Models\Ingredient;
use Database\Factories\IngredientFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->ingredients() as [$name, $description, $unit]){
            Ingredient::firstOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'unit' => $unit
                ]
            );
        }
    }

    private function ingredients(): array
    {
        return [
            [
                'white rum',
                'Light rum, often used in refreshing cocktails. Example: Bacardi, Havana Club',
                Unit::CL
            ],
            [
                'dark rum',
                'Aged rum with stronger flavor. Example: Myers’s, Captain Morgan Dark',
                Unit::CL
            ],
            [
                'vodka',
                'Neutral spirit used as a base in many cocktails. Example: Absolut, Smirnoff',
                Unit::CL
            ],
            [
                'gin',
                'Juniper-flavored spirit. Example: Bombay Sapphire, Tanqueray',
                Unit::CL
            ],
            [
                'tequila',
                'Agave-based spirit from Mexico. Example: Jose Cuervo, Don Julio',
                Unit::CL
            ],
            [
                'whiskey',
                'Distilled grain spirit. Example: Jameson, Jack Daniel’s',
                Unit::CL
            ],
            [
                'triple sec',
                'Orange-flavored liqueur. Example: Cointreau, Grand Marnier',
                Unit::CL
            ],
            [
                'sparkling water',
                'Carbonated water used as mixer. Example: Soda water',
                Unit::CL
            ],
            [
                'tonic water',
                'Bitter carbonated drink with quinine. Example: Schweppes Tonic',
                Unit::CL],
            [
                'ginger ale',
                'Sweet carbonated ginger-flavored drink. Example: Canada Dry',
                Unit::CL
            ],
            [
                'lemonade',
                'Sweetened lemon drink. Example: homemade or Sprite-style',
                Unit::CL
            ],
            [
                'coconut cream',
                'Thick coconut-based cream. Example: Coco Lopez',
                Unit::CL
            ],
            [
                'pineapple juice',
                'Sweet tropical fruit juice. Example: fresh or packaged juice',
                Unit::CL
            ],
            [
                'apple juice',
                'Sweet fruit juice from apples. Example: cloudy apple juice',
                Unit::CL
            ],
            [
                'lemon juice',
                'Freshly squeezed lemon juice for acidity',
                Unit::CL
            ],
            [
                'sugar',
                'Basic sweetener used in cocktails. Example: white sugar',
                Unit::TSP
            ],
            [
                'cane sugar',
                'Natural sugar from sugar cane. Example: brown sugar',
                Unit::TSP
            ],
            [
                'salt',
                'Enhances flavor, often used on rims. Example: margarita salt rim',
                Unit::TSP
            ],
            [
                'pepper',
                'Adds spice to cocktails. Example: black pepper',
                Unit::TSP
            ],
            [
                'pineapple',
                'Fresh fruit used as garnish or ingredient',
                Unit::SLICE
            ],
            [
                'cucumber',
                'Fresh vegetable used in refreshing drinks. Example: gin cocktails',
                Unit::SLICE
            ],
            [
                'peach',
                'Sweet fruit used in summer cocktails',
                Unit::SLICE
            ],
            [
                'lime',
                'Citrus fruit used for juice or garnish',
                Unit::SLICE
            ],
        ];
    }
}
