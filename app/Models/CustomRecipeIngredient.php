<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomRecipeIngredient extends Model
{
    protected $fillable = [
        'custom_recipe_id',
        'sort_order',
        'name',
        'measure',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function customRecipe(): BelongsTo
    {
        return $this->belongsTo(CustomRecipe::class);
    }
}
