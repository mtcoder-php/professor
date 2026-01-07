<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'answer_text',
        'is_correct',
        'order'
    ];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    /**
     * Answer belongs to question
     */
    public function question()
    {
        return $this->belongsTo(TestQuestion::class, 'question_id');
    }
}
