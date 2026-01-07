<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Test;
use App\Models\UserTestPermission;
use App\Models\TestResult;
use Carbon\Carbon;

class TestController extends Controller
{
    /**
     * Get available tests (allowed by prorektor)
     */
    public function index()
    {
        try {
            $user = Auth::user();

            $permissions = UserTestPermission::where('user_id', $user->id)
                ->where('is_allowed', true)
                ->with(['test.questions', 'test.results' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])
                ->get();

            $tests = $permissions->map(function($permission) {
                $test = $permission->test;
                $lastResult = $test->results->first();

                return [
                    'permission' => $permission,
                    'test' => $test,
                    'last_result' => $lastResult,
                    'can_take' => $this->canTakeTest($test, $lastResult),
                    'status' => $this->getTestStatus($test, $lastResult)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $tests
            ]);

        } catch (\Exception $e) {
            \Log::error('Teacher tests index error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get test details for taking
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            // Check permission
            $permission = UserTestPermission::where('user_id', $user->id)
                ->where('test_id', $id)
                ->where('is_allowed', true)
                ->first();

            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sizda bu testga ruxsat yo\'q'
                ], 403);
            }

            $test = Test::with('questions.answers')->findOrFail($id);

            // Check if can take test
            $lastResult = TestResult::where('user_id', $user->id)
                ->where('test_id', $id)
                ->latest()
                ->first();

            if (!$this->canTakeTest($test, $lastResult)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siz bu testni allaqachon topshirgansiz'
                ], 400);
            }

            // Randomize answers for each question
            $test->questions->each(function($question) {
                $question->answers = $question->answers->shuffle();
            });

            return response()->json([
                'success' => true,
                'data' => $test
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Submit test
     */
    public function submit(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'started_at' => 'required|date',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:test_questions,id',
                'answers.*.answer_ids' => 'required|array'
            ]);

            // Check permission
            $permission = UserTestPermission::where('user_id', $user->id)
                ->where('test_id', $id)
                ->where('is_allowed', true)
                ->first();

            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sizda bu testga ruxsat yo\'q'
                ], 403);
            }

            $test = Test::with('questions.answers')->findOrFail($id);

            // Calculate score
            $totalScore = 0;
            $correctAnswers = 0;

            foreach ($validated['answers'] as $answer) {
                $question = $test->questions->find($answer['question_id']);

                if (!$question) continue;

                $correctAnswerIds = $question->answers()
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->toArray();

                $userAnswerIds = collect($answer['answer_ids'])
                    ->sort()
                    ->values()
                    ->toArray();

                if ($correctAnswerIds === $userAnswerIds) {
                    $totalScore += $question->points;
                    $correctAnswers++;
                }
            }

            $percentage = ($totalScore / $test->total_points) * 100;
            $passed = $percentage >= $test->pass_score;

            // Save result
            $result = TestResult::create([
                'user_id' => $user->id,
                'test_id' => $test->id,
                'started_at' => $validated['started_at'],
                'finished_at' => now(),
                'score' => $totalScore,
                'percentage' => $percentage,
                'passed' => $passed,
                'answers' => $validated['answers']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test topshirildi',
                'data' => [
                    'result' => $result,
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $test->questions_count,
                    'show_results' => $test->show_results
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Test submit error', ['error' => $e->getMessage()]);

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
            $user = Auth::user();

            $results = TestResult::where('user_id', $user->id)
                ->with('test')
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can take test
     */
    private function canTakeTest($test, $lastResult)
    {
        if (!$lastResult) return true;

        if (!$test->allow_retake) return false;

        return true;
    }

    /**
     * Get test status
     */
    private function getTestStatus($test, $lastResult)
    {
        if (!$lastResult) return 'not_started';

        if ($lastResult->passed) return 'passed';

        return 'failed';
    }
}
