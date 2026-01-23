<?php

namespace Tests\Unit;

use App\Services\RecipeService;
use PHPUnit\Framework\TestCase;
use Mockery;

class RecipeServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that searchIngredients returns exact matches first
     */
    public function test_search_ingredients_returns_exact_matches_first(): void
    {
        // Create a partial mock of RecipeService
        $service = Mockery::mock(RecipeService::class)->makePartial();
        
        // Mock getAllIngredients to return test data
        $testIngredients = ['Chicken', 'Chicken Breast', 'Chicken Thigh', 'Beef', 'Chickpea'];
        
        $service->shouldReceive('getAllIngredients')
            ->once()
            ->andReturn($testIngredients);
        
        // Test searching for "Chicken"
        $results = $service->searchIngredients('Chicken', 10);
        
        // Assert that exact match comes first
        $this->assertNotEmpty($results);
        $this->assertEquals('Chicken', $results[0]);
        
        // Assert that all results contain "chicken" (case-insensitive)
        foreach ($results as $result) {
            $this->assertStringContainsStringIgnoringCase('chicken', $result);
        }
    }
}
