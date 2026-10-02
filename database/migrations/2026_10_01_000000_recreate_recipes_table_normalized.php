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
        Schema::dropIfExists('recipes');

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 20)->unique();
            $table->string('source', 32)->default('themealdb');
            $table->string('title');
            $table->string('category', 64)->nullable()->index();
            $table->string('area', 64)->nullable()->index();
            $table->text('instructions')->nullable();
            $table->string('thumb_url')->nullable();
            $table->string('tags')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('source_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->string('data_hash', 32)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('title');
        });

        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('name');
            $table->string('name_normalized')->index();
            $table->string('measure')->nullable();
            $table->timestamps();

            $table->index(['recipe_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('recipes');
    }
};
