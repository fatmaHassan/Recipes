<?php

namespace Tests\Unit;

use App\Support\IngredientCategories;
use PHPUnit\Framework\TestCase;

class IngredientCategoriesTest extends TestCase
{
    public function test_known_ingredients_map_to_curated_categories(): void
    {
        $expected = [
            'Chicken' => 'Meat & Poultry',
            'Salmon' => 'Seafood',
            'Milk' => 'Dairy & Eggs',
            'Beef tomatoes' => 'Vegetables',
            'Basmati Rice' => 'Grains & Pasta',
            'Kidney Beans' => 'Legumes & Beans',
            'Cashew Nuts' => 'Nuts & Seeds',
            'Smoked Paprika' => 'Herbs & Spices',
            'Soy Sauce' => 'Sauces & Condiments',
            'Olive Oil' => 'Fats & Oils',
            'Chicken Stock' => 'Stocks & Broths',
            'Golden Syrup' => 'Baking & Sweets',
            'Red Wine' => 'Beverages',
            'Hail' => 'Other',
        ];

        foreach ($expected as $name => $category) {
            $this->assertSame($category, IngredientCategories::forName($name), "Failed for {$name}");
        }
    }

    public function test_matching_is_case_insensitive_and_trims_whitespace(): void
    {
        $this->assertSame('Meat & Poultry', IngredientCategories::forName('  chicken '));
        $this->assertSame('Seafood', IngredientCategories::forName('SALMON'));
    }

    public function test_matching_handles_unicode_names(): void
    {
        $this->assertSame('Dairy & Eggs', IngredientCategories::forName('Gruyère'));
        $this->assertSame('Meat & Poultry', IngredientCategories::forName('jamón ibérico'));
    }

    public function test_unknown_names_fall_back_to_rules_then_other(): void
    {
        // Not in the map, but caught by a substring rule.
        $this->assertSame('Seafood', IngredientCategories::forName('Mahi Mahi Fillet'));
        $this->assertSame('Herbs & Spices', IngredientCategories::forName('Asafoetida Powder'));

        // Neither map nor rules match.
        $this->assertSame('Other', IngredientCategories::forName('Mystery Gel'));
    }

    public function test_empty_name_returns_other(): void
    {
        $this->assertSame('Other', IngredientCategories::forName('   '));
    }

    public function test_all_returns_display_order_including_other(): void
    {
        $categories = IngredientCategories::all();

        $this->assertContains('Meat & Poultry', $categories);
        $this->assertContains(IngredientCategories::OTHER, $categories);
        $this->assertSame(IngredientCategories::OTHER, $categories[array_key_last($categories)]);
        $this->assertCount(15, $categories);
    }

    public function test_popular_staples_are_unique_and_never_other(): void
    {
        $names = IngredientCategories::POPULAR_STAPLES;

        $this->assertGreaterThanOrEqual(10, count($names));
        $this->assertSame(count($names), count(array_unique(array_map('mb_strtolower', $names))));

        foreach ($names as $name) {
            $this->assertNotSame(
                IngredientCategories::OTHER,
                IngredientCategories::forName($name),
                "Popular staple \"{$name}\" should not fall into the Other bucket."
            );
        }
    }

    public function test_popular_from_catalog_keeps_curated_order_and_drops_missing(): void
    {
        $catalog = [
            ['name' => 'Salt', 'thumb' => null],
            ['name' => 'Mystery Gel', 'thumb' => null],
            ['name' => 'Eggs', 'thumb' => null],
        ];

        $popular = IngredientCategories::popularFromCatalog($catalog);

        $this->assertSame(['Eggs', 'Salt'], array_column($popular, 'name'));
    }
}
