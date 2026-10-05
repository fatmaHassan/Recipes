<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'source',
        'title',
        'category',
        'area',
        'instructions',
        'thumb_url',
        'tags',
        'youtube_url',
        'source_url',
        'is_active',
        'data_hash',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }
}
