<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    private const RECIPES = [
        [
            'external_id' => '52772',
            'title' => 'Teriyaki Chicken Casserole',
            'category' => 'Chicken',
            'area' => 'Japanese',
            'instructions' => 'Preheat oven to 350F. Combine soy sauce, water, brown sugar and ginger in a saucepan and bring to a boil. Stir in the chicken and bake until golden.',
            'thumb_url' => 'https://www.themealdb.com/images/media/meals/wvpsxx1468256321.jpg',
            'tags' => 'Chicken,GlutenFree',
            'youtube_url' => 'https://www.youtube.com/watch?v=4aZRs2gxvKk',
            'ingredients' => [
                ['soy sauce', '3/4 cup'],
                ['water', '1/2 cup'],
                ['brown sugar', '1/4 cup'],
                ['chicken', '2 lbs'],
                ['broccoli', '2 cups'],
            ],
        ],
        [
            'external_id' => '52819',
            'title' => 'Chicken Handi',
            'category' => 'Chicken',
            'area' => 'Indian',
            'instructions' => 'Fry onions in oil until golden, add chicken and spices, simmer until the sauce thickens.',
            'thumb_url' => 'https://www.themealdb.com/images/media/meals/wyxwsp1486978272.jpg',
            'tags' => 'Chicken,Curry',
            'youtube_url' => null,
            'ingredients' => [
                ['chicken', '1 kg'],
                ['onion', '2 large'],
                ['tomato puree', '1 cup'],
                ['yogurt', '1 cup'],
                ['ginger', '1 tbsp'],
            ],
        ],
        [
            'external_id' => '900001',
            'title' => 'Chicken Katsu Curry',
            'category' => 'Chicken',
            'area' => 'Japanese',
            'instructions' => 'Coat the chicken in panko breadcrumbs and fry until crisp, then serve with curry sauce and rice.',
            'thumb_url' => 'https://www.themealdb.com/images/media/meals/1548772325.jpg',
            'tags' => 'Chicken,Fried',
            'youtube_url' => null,
            'ingredients' => [
                ['chicken breast', '2 fillets'],
                ['panko breadcrumbs', '1 cup'],
                ['curry sauce', '500ml'],
                ['rice', '2 cups'],
            ],
        ],
        [
            'external_id' => '900002',
            'title' => 'Chicken Alfredo Pasta',
            'category' => 'Chicken',
            'area' => 'Italian',
            'instructions' => 'Pan-fry the chicken, toss with cooked fettuccine, cream and parmesan until silky.',
            'thumb_url' => 'https://www.themealdb.com/images/media/meals/1548772326.jpg',
            'tags' => 'Chicken,Pasta',
            'youtube_url' => null,
            'ingredients' => [
                ['chicken', '300g'],
                ['fettuccine', '400g'],
                ['parmesan', '1 cup'],
                ['double cream', '300ml'],
                ['garlic', '2 cloves'],
            ],
        ],
        [
            'external_id' => '900003',
            'title' => 'Tomato Bruschetta',
            'category' => 'Starter',
            'area' => 'Italian',
            'instructions' => 'Toast the bread, top with marinated diced tomato and fresh basil.',
            'thumb_url' => 'https://www.themealdb.com/images/media/meals/1548772327.jpg',
            'tags' => 'Starter,Vegan',
            'youtube_url' => null,
            'ingredients' => [
                ['tomato', '4 large'],
                ['basil', '1 bunch'],
                ['bread', '1 baguette'],
                ['olive oil', '3 tbsp'],
            ],
        ],
        [
            'external_id' => '900004',
            'title' => 'Vegetable Samosas',
            'category' => 'Vegetarian',
            'area' => 'Indian',
            'instructions' => 'Fill the pastry with spiced potato and peas, fold and deep-fry until golden.',
            'thumb_url' => 'https://www.themealdb.com/images/media/meals/1548772328.jpg',
            'tags' => 'Vegetarian,StreetFood',
            'youtube_url' => null,
            'ingredients' => [
                ['potato', '4 large'],
                ['peas', '1 cup'],
                ['pastry', '12 sheets'],
                ['cumin seeds', '1 tsp'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::RECIPES as $data) {
            $ingredients = $data['ingredients'];
            unset($data['ingredients']);

            $recipe = Recipe::factory()->create($data);

            $recipe->ingredients()->createMany(collect($ingredients)
                ->map(fn ($ingredient, $index) => [
                    'sort_order' => $index + 1,
                    'name' => $ingredient[0],
                    'measure' => $ingredient[1],
                ])
                ->all());
        }
    }
}
