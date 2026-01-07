<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'requires_period',
        'max_points',
        'order',
        'is_active'
    ];

    protected $casts = [
        'requires_period' => 'boolean',
        'is_active' => 'boolean',
        'max_points' => 'decimal:2'
    ];

    /**
     * Category has many files
     */
    public function files()
    {
        return $this->hasMany(PortfolioFile::class, 'category_id');
    }

    /**
     * Scope - only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
