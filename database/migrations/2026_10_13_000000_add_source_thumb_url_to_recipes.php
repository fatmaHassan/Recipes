<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->string('source_thumb_url')->nullable()->after('thumb_url');
        });

        // Existing rows were synced with hotlinked CDN URLs: that URL is the
        // image source until mirroring rewrites thumb_url to a stored copy.
        DB::table('recipes')->update(['source_thumb_url' => DB::raw('thumb_url')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('source_thumb_url');
        });
    }
};
