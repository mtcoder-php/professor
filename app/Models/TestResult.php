<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_id',
        'started_at',
        'finished_at',
        'score',
        'percentage',
        'passed',
        'answers',
        'selected_questions', // ← QOSHILDI
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
        'answers' => 'array',
        'selected_questions' => 'array', // ← QOSHILDI
    ];

    /**
     * Result belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Result belongs to test
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
