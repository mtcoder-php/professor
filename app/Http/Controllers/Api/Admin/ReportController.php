<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\PortfolioFile;
use App\Models\PortfolioCategory;
use App\Exports\TeachersReportExport;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get overall statistics
     */
    public function getOverallStats()
    {
        try {
            $stats = [
                'total_faculties' => Faculty::count(),
                'total_departments' => Department::count(),
                'total_teachers' => User::whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                })->count(),
                'total_tests' => Test::count(),
                'total_test_results' => TestResult::whereNotNull('finished_at')->count(),
                'total_portfolios' => PortfolioFile::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Overall stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Get teachers report by department
     */
    public function getTeachersByDepartment(Request $request)
    {
        try {
            $query = User::whereHas('roles', function($q) {
                $q->where('name', 'teacher');
            })->with(['faculty', 'department']);

            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            $teachers = $query->get();

            $entryTests = Test::where('type', 'entry')
                ->where('is_active', true)
                ->orderBy('created_at')
                ->get();

            $exitTests = Test::where('type', 'exit')
                ->where('is_active', true)
                ->orderBy('created_at')
                ->get();

            $report = $teachers->map(function($teacher) use ($entryTests, $exitTests) {
                $entryTestResults = $entryTests->map(function($test) use ($teacher) {
                    $result = TestResult::where('user_id', $teacher->id)
                        ->where('test_id', $test->id)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc')
                        ->first();

                    if ($result) {
                        $selectedCount = is_array($result->selected_questions)
                            ? count($result->selected_questions)
                            : $test->questions_count;

                        $totalPoints = $selectedCount * $test->points_per_question;

                        return [
                            'score' => $result->score,
                            'total_points' => $totalPoints,
                            'percentage' => $result->percentage,
                            'passed' => $result->passed,
                            'correct_answers' => $this->getCorrectAnswersCount($result),
                            'total_questions' => $selectedCount,
                        ];
                    }

                    return [
                        'score' => null,
                        'total_points' => null,
                        'percentage' => null,
                        'passed' => null,
                        'correct_answers' => null,
                        'total_questions' => null,
                    ];
                });

                $exitTestResults = $exitTests->map(function($test) use ($teacher) {
                    $result = TestResult::where('user_id', $teacher->id)
                        ->where('test_id', $test->id)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc')
                        ->first();

                    if ($result) {
                        $selectedCount = is_array($result->selected_questions)
                            ? count($result->selected_questions)
                            : $test->questions_count;

                        $totalPoints = $selectedCount * $test->points_per_question;

                        return [
                            'score' => $result->score,
                            'total_points' => $totalPoints,
                            'percentage' => $result->percentage,
                            'passed' => $result->passed,
                            'correct_answers' => $this->getCorrectAnswersCount($result),
                            'total_questions' => $selectedCount,
                        ];
                    }

                    return [
                        'score' => null,
                        'total_points' => null,
                        'percentage' => null,
                        'passed' => null,
                        'correct_answers' => null,
                        'total_questions' => null,
                    ];
                });

                $portfolioTotal = PortfolioFile::where('user_id', $teacher->id)
                    ->where('status', 'evaluated')
                    ->with('evaluation')
                    ->get()
                    ->sum(function($file) {
                        return $file->evaluation ? $file->evaluation->score : 0;
                    });

                return [
                    'id' => $teacher->id,
                    'full_name' => $teacher->full_name,
                    'scientific_degree' => $teacher->scientific_degree,
                    'faculty' => $teacher->faculty?->name ?? '—',
                    'department' => $teacher->department?->name ?? '—',
                    'entry_tests' => $entryTestResults,
                    'exit_tests' => $exitTestResults,
                    'portfolio_total' => $portfolioTotal,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'teachers' => $report,
                    'entry_tests' => $entryTests,
                    'exit_tests' => $exitTests,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Teachers report error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $filters = $request->only(['faculty_id', 'department_id']);

            return Excel::download(
                new TeachersReportExport($filters),
                'teachers-report-' . now()->format('Y-m-d') . '.xlsx'
            );

        } catch (\Exception $e) {
            \Log::error('Excel export error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Excel yaratishda xatolik'
            ], 500);
        }
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = User::with(['faculty', 'department'])
                ->whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                });

            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            $teachers = $query->get();
            $entryTests = Test::where('type', 'entry')->where('is_active', true)->get();
            $exitTests = Test::where('type', 'exit')->where('is_active', true)->get();

            $teachersData = [];

            foreach ($teachers as $teacher) {
                $entryResults = [];
                $exitResults = [];

                // Entry test results
                foreach ($entryTests as $test) {
                    $result = TestResult::where('user_id', $teacher->id)
                        ->where('test_id', $test->id)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc')
                        ->first();

                    if ($result) {
                        $correctAnswers = $this->getCorrectAnswersCount($result);
                        $selectedCount = is_array($result->selected_questions)
                            ? count($result->selected_questions)
                            : $test->questions_count;
                        $totalPoints = $selectedCount * $test->points_per_question;

                        $entryResults[] = "{$correctAnswers}/{$selectedCount}\n{$result->score}/{$totalPoints}";
                    } else {
                        $entryResults[] = '—';
                    }
                }

                // Exit test results
                foreach ($exitTests as $test) {
                    $result = TestResult::where('user_id', $teacher->id)
                        ->where('test_id', $test->id)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc')
                        ->first();

                    if ($result) {
                        $correctAnswers = $this->getCorrectAnswersCount($result);
                        $selectedCount = is_array($result->selected_questions)
                            ? count($result->selected_questions)
                            : $test->questions_count;
                        $totalPoints = $selectedCount * $test->points_per_question;

                        $exitResults[] = "{$correctAnswers}/{$selectedCount}\n{$result->score}/{$totalPoints}";
                    } else {
                        $exitResults[] = '—';
                    }
                }

                // Portfolio
                $portfolioTotal = PortfolioFile::where('user_id', $teacher->id)
                    ->where('status', 'evaluated')
                    ->with('evaluation')
                    ->get()
                    ->sum(function($file) {
                        return $file->evaluation ? $file->evaluation->score : 0;
                    });

                $teachersData[] = [
                    'full_name' => $teacher->full_name,
                    'scientific_degree' => $teacher->scientific_degree ?? '—',
                    'faculty' => $teacher->faculty?->name ?? '—',
                    'department' => $teacher->department?->name ?? '—',
                    'entry_results' => $entryResults,
                    'exit_results' => $exitResults,
                    'portfolio_score' => $portfolioTotal,
                ];
            }

            $pdf = PDF::loadView('reports.teachers', [
                'teachers' => $teachersData,
                'entryTests' => $entryTests,
                'exitTests' => $exitTests,
                'date' => now()->format('d.m.Y H:i'),
            ]);

            $pdf->setPaper('A4', 'landscape');

            return $pdf->download('teachers-report-' . now()->format('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            \Log::error('PDF export error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'PDF yaratishda xatolik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate correct answers count from test result
     *
     * PRIVATE METHOD ← MUHIM
     */
    private function getCorrectAnswersCount($result)
    {
        if (!$result || !$result->answers || !is_array($result->answers)) {
            return 0;
        }

        $test = $result->test;
        if (!$test) return 0;

        $correctCount = 0;

        foreach ($result->answers as $answer) {
            $question = $test->questions()->find($answer['question_id']);

            if (!$question) continue;

            $correctAnswerIds = $question->answers()
                ->where('is_correct', true)
                ->pluck('id')
                ->toArray();

            $userAnswerIds = $answer['answer_ids'] ?? [];

            sort($correctAnswerIds);
            sort($userAnswerIds);

            if ($correctAnswerIds === $userAnswerIds) {
                $correctCount++;
            }
        }

        return $correctCount;
    }
}
