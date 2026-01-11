<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\TestImportService;
use Illuminate\Support\Facades\Log; // ← QO'SHISH KERAK AGAR YO'Q BO'LSA

class TestController extends Controller
{
    /**
     * Display a listing of tests
     */
    public function index(Request $request)
    {
        try {
            $query = Test::with(['creator', 'questions']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('title', 'like', "%{$search}%");
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $query->withCount('questions');
            $query->orderBy('created_at', 'desc');

            $perPage = $request->get('per_page', 10);
            $tests = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tests
            ]);

        } catch (\Exception $e) {
            Log::error('Test index error', ['error' => $e->getMessage()]); // ← TUZATILDI

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created test
     */
    /**
     * Store a newly created test
     */
    /**
     * Store new test
     */
    /**
     * Store new test
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|in:entry,exit',
                'points_per_question' => 'required|numeric|min:0.5',
                'questions_per_attempt' => 'nullable|integer|min:1',
                'duration_minutes' => 'required|integer|min:1',
                'pass_score' => 'required|numeric|min:0|max:100',
                'start_date' => 'nullable|string',
                'end_date' => 'nullable|string',
                'is_active' => 'boolean',
                'allow_retake' => 'boolean',
                'show_results' => 'boolean',
            ]);

            // Convert datetime-local format to MySQL datetime
            // Frontend: 2026-01-11T13:00
            // MySQL: 2026-01-11 13:00:00
            if (!empty($validated['start_date'])) {
                $validated['start_date'] = str_replace('T', ' ', $validated['start_date']) . ':00';
            }

            if (!empty($validated['end_date'])) {
                $validated['end_date'] = str_replace('T', ' ', $validated['end_date']) . ':00';
            }

            $validated['created_by'] = auth()->id();
            $validated['total_points'] = 0;
            $validated['questions_count'] = 0;

            $test = Test::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Test yaratildi',
                'data' => $test
            ]);

        } catch (\Exception $e) {
            \Log::error('Test create error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified test
     */
    /**
     * Get single test
     */
    /**
     * Get single test for editing
     */
    /**
     * Show test
     */
    public function show($id)
    {
        try {
            $test = Test::with(['questions.answers', 'creator'])
                ->findOrFail($id);

            $testData = $test->toArray();

            // Convert MySQL datetime to datetime-local format
            // MySQL: 2026-01-11 13:00:00
            // Frontend: 2026-01-11T13:00
            if (!empty($testData['start_date'])) {
                $testData['start_date'] = substr(str_replace(' ', 'T', $testData['start_date']), 0, 16);
            }

            if (!empty($testData['end_date'])) {
                $testData['end_date'] = substr(str_replace(' ', 'T', $testData['end_date']), 0, 16);
            }

            return response()->json([
                'success' => true,
                'data' => $testData
            ]);

        } catch (\Exception $e) {
            \Log::error('Test show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Test topilmadi'
            ], 404);
        }
    }

    /**
     * Update the specified test
     */
    /**
     * Update test
     */
    /**
     * Update test
     */
    /**
     * Update test
     */
    public function update(Request $request, $id)
    {
        try {
            $test = Test::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|in:entry,exit',
                'points_per_question' => 'required|numeric|min:0.5',
                'questions_per_attempt' => 'nullable|integer|min:1',
                'duration_minutes' => 'required|integer|min:1',
                'pass_score' => 'required|numeric|min:0|max:100',
                'start_date' => 'nullable|string',
                'end_date' => 'nullable|string',
                'is_active' => 'boolean',
                'allow_retake' => 'boolean',
                'show_results' => 'boolean',
            ]);

            // Convert format
            if (!empty($validated['start_date'])) {
                $validated['start_date'] = str_replace('T', ' ', $validated['start_date']) . ':00';
            }

            if (!empty($validated['end_date'])) {
                $validated['end_date'] = str_replace('T', ' ', $validated['end_date']) . ':00';
            }

            $test->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Test yangilandi',
                'data' => $test->fresh(['questions', 'creator'])
            ]);

        } catch (\Exception $e) {
            \Log::error('Test update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }


    /**
     * Remove the specified test
     */
    public function destroy($id)
    {
        try {
            $test = Test::findOrFail($id);

            if ($test->results()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Test natijalari mavjud. O\'chirish mumkin emas.'
                ], 400);
            }

            $test->delete();

            return response()->json([
                'success' => true,
                'message' => 'Test muvaffaqiyatli o\'chirildi'
            ]);

        } catch (\Exception $e) {
            Log::error('Test destroy error', ['error' => $e->getMessage()]); // ← TUZATILDI

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle test status
     */
    public function toggleStatus($id)
    {
        try {
            $test = Test::findOrFail($id);
            $test->is_active = !$test->is_active;
            $test->save();

            return response()->json([
                'success' => true,
                'message' => 'Test holati o\'zgartirildi',
                'data' => $test
            ]);

        } catch (\Exception $e) {
            Log::error('Test toggle status error', ['error' => $e->getMessage()]); // ← TUZATILDI

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add question to test (manual)
     */
    public function addQuestion(Request $request, $id)
    {
        try {
            $test = Test::findOrFail($id);

            $validated = $request->validate([
                'question_text' => 'required|string',
                'question_type' => 'required|in:single,multiple',
                'answers' => 'required|array|min:2',
                'answers.*.text' => 'required|string',
                'answers.*.is_correct' => 'required|boolean'
            ]);

            // Check at least one correct answer
            $correctCount = collect($validated['answers'])->where('is_correct', true)->count();
            if ($correctCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kamida bitta to\'g\'ri javob bo\'lishi kerak'
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Create question
                $question = TestQuestion::create([
                    'test_id' => $test->id,
                    'question_text' => $validated['question_text'],
                    'question_type' => $validated['question_type'],
                    'order' => $test->questions()->count() + 1,
                    'points' => $test->points_per_question
                ]);

                // Create answers
                foreach ($validated['answers'] as $index => $answer) {
                    TestAnswer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answer['text'],
                        'is_correct' => $answer['is_correct'],
                        'order' => $index + 1
                    ]);
                }

                // Update test counts
                $test->questions_count = $test->questions()->count();
                $test->total_points = $test->questions_count * $test->points_per_question;
                $test->save();

                DB::commit();

                $question->load('answers');

                return response()->json([
                    'success' => true,
                    'message' => 'Savol muvaffaqiyatli qo\'shildi',
                    'data' => $question
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Add question error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get test questions
     */
    public function getQuestions($id)
    {
        try {
            $test = Test::findOrFail($id);
            $questions = $test->questions()->with('answers')->orderBy('order')->get();

            return response()->json([
                'success' => true,
                'data' => $questions
            ]);

        } catch (\Exception $e) {
            \Log::error('Get questions error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete question
     */
    public function deleteQuestion($testId, $questionId)
    {
        try {
            $test = Test::findOrFail($testId);
            $question = TestQuestion::where('test_id', $testId)
                ->where('id', $questionId)
                ->firstOrFail();

            $question->delete();

            // Update test counts
            $test->questions_count = $test->questions()->count();
            $test->total_points = $test->questions_count * $test->points_per_question;
            $test->save();

            // Reorder remaining questions
            $test->questions()->orderBy('order')->get()->each(function ($q, $index) {
                $q->update(['order' => $index + 1]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Savol o\'chirildi'
            ]);

        } catch (\Exception $e) {
            \Log::error('Delete question error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Import questions from file
     */
    public function importQuestions(Request $request, $id)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:txt,docx|max:5120' // 5MB
            ]);

            $test = Test::findOrFail($id);

            $importService = new TestImportService();
            $results = $importService->importFromFile($test->id, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Import yakunlandi',
                'data' => $results
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Import questions error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Import xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download question template
     */
    public function downloadTemplate()
    {
        try {
            $template = "1.Alisher Navoiy qachon tavallud topgan?
- 1501
- 1336
# 1441
- 1440

2.O'zbekiston mustaqillikka qachon erishgan?
- 1990
# 1991
- 1992
- 1993

3.Toshkent shahri qachon poytaxt bo'lgan?
- 1920
# 1930
- 1940
- 1950

4.Amir Temur nechanchi yilda tug'ilgan?
- 1336
# 1336
- 1340
- 1350

5.O'zbek tili qaysi oilaga mansub?
- Slavyan
# Turkiy
- Arab
- Fors
";

            return response($template, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="test_questions_template.txt"',
            ]);

        } catch (\Exception $e) {
            \Log::error('Template download error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
