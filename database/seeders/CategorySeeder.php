<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->categories() as $name => $description){
            Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
    }

    private function categories(): array
    {
        return [
            'alcoholic' => 'Contains alcohol.',
            'non-alcoholic' => 'Does not contain alcohol.',
            'long-drink' => 'Served in a large glass with higher volume.',
            'short-drink' => 'Served in a small glass with lower volume.',
            'aperitif' => 'Usually served before a meal.',
            'digestif' => 'Usually served after a meal.',
            'strong' => 'High alcohol content.',
            'light' => 'Low alcohol content.',
            'hot' => 'Served warm or hot.',
            'cold' => 'Served cold usually with ice.',
        ];
    }
}
