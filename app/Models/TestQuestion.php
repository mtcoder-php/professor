<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'question_text',
        'question_type',
        'order',
        'points'
    ];

    protected $casts = [
        'points' => 'decimal:2'
    ];

    /**
     * Question belongs to test
     */
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Question has many answers
     */
    public function answers()
    {
        return $this->hasMany(TestAnswer::class, 'question_id')->orderBy('order');
    }

    /**
     * Get correct answers
     */
    public function correctAnswers()
    {
        return $this->hasMany(TestAnswer::class, 'question_id')->where('is_correct', true);
    }
}
