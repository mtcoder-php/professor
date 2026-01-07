<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'questions_count',
        'points_per_question',
        'total_points',
        'duration_minutes',
        'pass_score',
        'start_date',
        'end_date',
        'is_active',
        'allow_retake',
        'show_results',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_retake' => 'boolean',
        'show_results' => 'boolean',
        'points_per_question' => 'decimal:2',
        'total_points' => 'decimal:2',
        'pass_score' => 'decimal:2'
        // ← start_date va end_date cast OLIB TASHLANDI
    ];

    public function questions()
    {
        return $this->hasMany(TestQuestion::class)->orderBy('order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function results()
    {
        return $this->hasMany(TestResult::class);
    }

    public function permissions()
    {
        return $this->hasMany(UserTestPermission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
