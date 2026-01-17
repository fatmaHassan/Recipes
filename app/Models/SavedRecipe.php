<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedRecipe extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'recipe_id',
        'recipe_data',
        'is_favorite',
    ];

    protected function casts(): array
    {
        return [
            'recipe_data' => 'array',
            'is_favorite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
