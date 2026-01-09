<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\UserTestPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Get available tests for teacher
     */
    /**
     * Get available tests for teacher
     */
    public function index()
    {
        try {
            $userId = auth()->id();

            \Log::info('🔍 Teacher tests request', [
                'user_id' => $userId
            ]);

            $tests = Test::whereHas('permissions', function($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where('is_allowed', true);
            })
                ->where('is_active', true)
                ->with(['results' => function($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc');
                }])
                ->get()
                ->map(function($test) use ($userId) {
                    $latestResult = $test->results->first();
                    $canTake = $this->canTakeTest($test, $userId);

                    \Log::info('Test data', [
                        'test_id' => $test->id,
                        'title' => $test->title,
                        'points_per_question' => $test->points_per_question, // ← TEKSHIRISH
                        'questions_count' => $test->questions_count,
                        'questions_per_attempt' => $test->questions_per_attempt
                    ]);

                    return [
                        'id' => $test->id,
                        'title' => $test->title,
                        'type' => $test->type,
                        'duration_minutes' => $test->duration_minutes,
                        'questions_count' => $test->questions_count,
                        'questions_per_attempt' => $test->questions_per_attempt,
                        'points_per_question' => $test->points_per_question, // ← MUHIM
                        'total_points' => $test->total_points,
                        'pass_score' => $test->pass_score,
                        'is_active' => $test->is_active,
                        'allow_retake' => $test->allow_retake,
                        'show_results' => $test->show_results,
                        'start_date' => $test->start_date,
                        'end_date' => $test->end_date,

                        'latest_result' => $latestResult ? [
                            'score' => $latestResult->score,
                            'percentage' => $latestResult->percentage,
                            'passed' => $latestResult->passed,
                            'finished_at' => $latestResult->finished_at,
                        ] : null,

                        'can_take' => $canTake,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $tests
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Teacher tests index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Check if teacher can take test
     */
    private function canTakeTest($test, $userId)
    {
        // Check if already completed
        $hasCompleted = TestResult::where('user_id', $userId)
            ->where('test_id', $test->id)
            ->whereNotNull('finished_at')
            ->exists();

        \Log::info('Can take test check', [
            'test_id' => $test->id,
            'user_id' => $userId,
            'has_completed' => $hasCompleted,
            'allow_retake' => $test->allow_retake,
            'start_date' => $test->start_date,
            'end_date' => $test->end_date
        ]);

        // If completed and retake not allowed
        if ($hasCompleted && !$test->allow_retake) {
            \Log::info('❌ Cannot retake - already completed');
            return false;
        }

        // Check date range
        $now = now();
        if ($test->start_date && $now->lt($test->start_date)) {
            \Log::info('❌ Not started yet', [
                'now' => $now,
                'start_date' => $test->start_date
            ]);
            return false;
        }

        if ($test->end_date && $now->gt($test->end_date)) {
            \Log::info('❌ Already ended', [
                'now' => $now,
                'end_date' => $test->end_date
            ]);
            return false;
        }

        \Log::info('✅ Can take test');
        return true;
    }

    /**
     * Start test and get random questions
     */
    public function show($id)
    {
        try {
            $userId = auth()->id();
            $test = Test::findOrFail($id);

            // Check permission
            $permission = UserTestPermission::where('user_id', $userId)
                ->where('test_id', $id)
                ->where('is_allowed', true)
                ->first();

            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sizda bu testni topshirish ruxsati yo\'q'
                ], 403);
            }

            // Check if test is active
            if (!$test->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test faol emas'
                ], 403);
            }

            // Check if already completed (if retake not allowed)
            if (!$test->allow_retake) {
                $existingResult = TestResult::where('user_id', $userId)
                    ->where('test_id', $id)
                    ->whereNotNull('finished_at')
                    ->exists();

                if ($existingResult) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siz bu testni allaqachon topshirgansiz'
                    ], 403);
                }
            }

            // Get random questions ← ASOSIY QISM
            $questionsPerAttempt = $test->questions_per_attempt ?? $test->questions_count;
            $randomQuestions = $test->getRandomQuestions($questionsPerAttempt);

            \Log::info('Test started', [
                'user_id' => $userId,
                'test_id' => $id,
                'total_questions' => $test->questions()->count(),
                'selected_count' => $randomQuestions->count(),
                'selected_ids' => $randomQuestions->pluck('id')->toArray()
            ]);

            // Create test result record
            $testResult = TestResult::create([
                'user_id' => $userId,
                'test_id' => $id,
                'started_at' => now(),
                'selected_questions' => $randomQuestions->pluck('id')->toArray(), // ← Store which questions
                'answers' => [],
                'score' => 0,
                'percentage' => 0,
                'passed' => false,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'test' => $test,
                    'questions' => $randomQuestions,
                    'result_id' => $testResult->id,
                    'started_at' => $testResult->started_at,
                    'total_available' => $test->questions()->count(),
                    'questions_shown' => $randomQuestions->count(),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Teacher test show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit test answers
     */
    /**
     * Submit test answers
     */
    /**
     * Submit test answers
     */
    public function submit(Request $request, $id)
    {
        try {
            $userId = auth()->id();

            $request->validate([
                'result_id' => 'required|exists:test_results,id',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:test_questions,id',
                'answers.*.answer_ids' => 'required|array',
            ]);

            $testResult = TestResult::where('id', $request->result_id)
                ->where('user_id', $userId)
                ->where('test_id', $id)
                ->firstOrFail();

            // Check if already submitted
            if ($testResult->finished_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test allaqachon topshirilgan'
                ], 400);
            }

            $test = Test::with('questions.answers')->findOrFail($id);

            // Get selected questions
            $selectedQuestionIds = $testResult->selected_questions;

            \Log::info('Submit test - Initial data', [
                'test_id' => $id,
                'result_id' => $testResult->id,
                'total_questions_in_test' => $test->questions()->count(),
                'selected_questions_count' => is_array($selectedQuestionIds) ? count($selectedQuestionIds) : 0,
                'selected_questions' => $selectedQuestionIds,
                'questions_per_attempt' => $test->questions_per_attempt,
                'points_per_question' => $test->points_per_question
            ]);

            // If selected_questions is null or not array, use all questions
            if (!is_array($selectedQuestionIds)) {
                \Log::warning('Selected questions is not array, using all questions');
                $selectedQuestionIds = $test->questions->pluck('id')->toArray();
            }

            // Calculate score ONLY for selected questions ←
            $score = 0;
            $correctAnswers = 0;
            $totalQuestionsAnswered = count($request->answers);

            foreach ($request->answers as $answer) {
                // Only count if this question was in selected questions
                if (!in_array($answer['question_id'], $selectedQuestionIds)) {
                    \Log::warning('Question not in selected list', [
                        'question_id' => $answer['question_id']
                    ]);
                    continue;
                }

                $question = $test->questions->find($answer['question_id']);

                if (!$question) {
                    \Log::warning('Question not found', ['question_id' => $answer['question_id']]);
                    continue;
                }

                $correctAnswerIds = $question->answers()
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->toArray();

                $userAnswerIds = $answer['answer_ids'];

                sort($correctAnswerIds);
                sort($userAnswerIds);

                if ($correctAnswerIds === $userAnswerIds) {
                    $score += $question->points;
                    $correctAnswers++;

                    \Log::info('Correct answer', [
                        'question_id' => $question->id,
                        'points' => $question->points,
                        'total_score_so_far' => $score
                    ]);
                }
            }

            // MUHIM: Total points faqat SELECTED questions uchun ←
            $selectedQuestionsCount = count($selectedQuestionIds);
            $totalPoints = $selectedQuestionsCount * $test->points_per_question;
            $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;
            $passed = $percentage >= $test->pass_score;

            \Log::info('Test calculation', [
                'selected_questions_count' => $selectedQuestionsCount,
                'points_per_question' => $test->points_per_question,
                'total_possible_points' => $totalPoints,
                'user_score' => $score,
                'correct_answers' => $correctAnswers,
                'percentage' => round($percentage, 2),
                'pass_score_required' => $test->pass_score,
                'passed' => $passed
            ]);

            // Update result
            $testResult->update([
                'finished_at' => now(),
                'answers' => $request->answers,
                'score' => $score,
                'percentage' => round($percentage, 2),
                'passed' => $passed,
            ]);

            \Log::info('✅ Test submitted successfully', [
                'user_id' => $userId,
                'test_id' => $id,
                'result_id' => $testResult->id,
                'final_score' => $score,
                'final_percentage' => round($percentage, 2),
                'final_passed' => $passed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test muvaffaqiyatli topshirildi',
                'data' => [
                    'score' => $score,
                    'total_points' => $totalPoints,
                    'percentage' => round($percentage, 2),
                    'passed' => $passed,
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $selectedQuestionsCount,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Teacher test submit error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get test results
     */
    public function results()
    {
        try {
            $userId = auth()->id();

            $results = TestResult::with(['test', 'user'])
                ->where('user_id', $userId)
                ->whereNotNull('finished_at')
                ->orderBy('finished_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            \Log::error('Teacher test results error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }
}
