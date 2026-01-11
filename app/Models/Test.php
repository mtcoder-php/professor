<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
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
        'created_by',
        'questions_per_attempt',
    ];

    protected $casts = [
//        'start_date' => 'datetime',
//        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'allow_retake' => 'boolean',
        'show_results' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function results()
    {
        return $this->hasMany(TestResult::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function permissions()
    {
        return $this->hasMany(UserTestPermission::class);
    }

    /**
     * Get random questions for test attempt
     *
     * @param int $count Number of questions to select
     * @return \Illuminate\Support\Collection
     */
    public function getRandomQuestions($count = null)
    {
        // Use questions_per_attempt if not specified
        $count = $count ?? $this->questions_per_attempt ?? $this->questions_count;

        // Get all question IDs
        $allQuestionIds = $this->questions()->pluck('id')->toArray();

        // Shuffle the array to randomize
        shuffle($allQuestionIds);

        // Take only the required count
        $selectedIds = array_slice($allQuestionIds, 0, $count);

        // Get questions with answers in the shuffled order
        $randomQuestions = $this->questions()
            ->with('answers')
            ->whereIn('id', $selectedIds)
            ->get()
            // Shuffle again to randomize order
            ->shuffle();

        \Log::info('🎲 Random questions generated', [
            'test_id' => $this->id,
            'total_available' => count($allQuestionIds),
            'requested_count' => $count,
            'selected_count' => $randomQuestions->count(),
            'selected_ids' => $randomQuestions->pluck('id')->toArray()
        ]);

        return $randomQuestions;
    }

    /**
     * Get total available questions count
     */
    public function getTotalQuestionsAttribute()
    {
        return $this->questions()->count();
    }
}
