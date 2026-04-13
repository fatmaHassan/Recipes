<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions');
            $table->string('category')->nullable();
            $table->string('area')->nullable();
            $table->string('image_url')->nullable();
            $table->string('servings')->nullable();
            $table->unsignedSmallInteger('prep_time_minutes')->nullable();
            $table->unsignedSmallInteger('cook_time_minutes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('custom_recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_recipe_id')->constrained('custom_recipes')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('name');
            $table->string('measure')->nullable();
            $table->timestamps();

            $table->index(['custom_recipe_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_recipe_ingredients');
        Schema::dropIfExists('custom_recipes');
    }
};
