<?php

use App\Support\Backfills\BackfillSavedRecipeFk;
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
        Schema::table('saved_recipes', function (Blueprint $table) {
            $table->foreignId('recipe_internal_id')
                ->nullable()
                ->after('recipe_id')
                ->constrained('recipes')
                ->nullOnDelete();
        });

        BackfillSavedRecipeFk::run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saved_recipes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipe_internal_id');
        });
    }
};
