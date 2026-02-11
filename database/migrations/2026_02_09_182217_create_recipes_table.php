<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            // Primary key matching TheMealDB idMeal
            $table->string('idMeal')->primary();
            
            // Core recipe information
            $table->string('strMeal');
            $table->string('strDrinkAlternate')->nullable();
            $table->string('strCategory')->nullable();
            $table->string('strArea')->nullable();
            $table->text('strInstructions')->nullable();
            $table->string('strMealThumb')->nullable(); // URL to image
            $table->string('strTags')->nullable();
            $table->string('strYoutube')->nullable(); // URL to YouTube video
            $table->string('strSource')->nullable(); // URL to source
            $table->string('strImageSource')->nullable(); // URL to image source
            $table->string('strCreativeCommonsConfirmed')->nullable();
            $table->timestamp('dateModified')->nullable();
            
            // Ingredients (strIngredient1 through strIngredient20)
            for ($i = 1; $i <= 20; $i++) {
                $table->string("strIngredient{$i}")->nullable();
            }
            
            // Measures (strMeasure1 through strMeasure20)
            for ($i = 1; $i <= 20; $i++) {
                $table->string("strMeasure{$i}")->nullable();
            }
            
            // Laravel timestamps
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('strCategory');
            $table->index('strArea');
            $table->index('strMeal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
