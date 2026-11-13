<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_is_public_and_renders_for_guests(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
        $response->assertSee('Fatmah Hassan');
        $response->assertSee('TheMealDB');
        $response->assertSee('https://www.linkedin.com/in/fatmahasan/', false);
    }

    public function test_about_page_links_ingredient_and_custom_recipe_pages_for_logged_in_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/about');

        $response->assertStatus(200);
        $response->assertSee(route('ingredients.index'), false);
        $response->assertSee(route('custom-recipes.index'), false);
    }
}
