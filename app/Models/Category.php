<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /** @param Builder<Category> $query
     *  @return Builder<Category> */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    /** @param Builder<Category> $query
     *  @return Builder<Category> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
