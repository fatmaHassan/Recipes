<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = [
        'recipe_id',
        'sort_order',
        'name',
        'name_normalized',
        'measure',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => [
                'name' => $value,
                'name_normalized' => mb_strtolower(trim($value)),
            ],
        );
    }
}
