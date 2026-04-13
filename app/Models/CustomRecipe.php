<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomRecipe extends Model
{
    /** @use HasFactory<\Database\Factories\CustomRecipeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'instructions',
        'category',
        'area',
        'image_url',
        'servings',
        'prep_time_minutes',
        'cook_time_minutes',
    ];

    protected function casts(): array
    {
        return [
            'prep_time_minutes' => 'integer',
            'cook_time_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(CustomRecipeIngredient::class)->orderBy('sort_order');
    }
}
