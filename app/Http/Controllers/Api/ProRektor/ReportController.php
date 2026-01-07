<?php

namespace App\Http\Controllers\Api\ProRektor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use App\Models\TestResult;
use App\Models\PortfolioFile;
use App\Models\PortfolioEvaluation;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get overall statistics
     */
    public function getOverallStatistics()
    {
        try {
            $stats = [
                // Teachers
                'total_teachers' => User::whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                })->count(),

                // Tests
                'total_test_results' => TestResult::whereNotNull('finished_at')->count(),
                'passed_tests' => TestResult::where('passed', true)->count(),
                'failed_tests' => TestResult::where('passed', false)->count(),
                'average_test_score' => TestResult::whereNotNull('finished_at')->avg('percentage') ?? 0,

                // Portfolio
                'total_portfolio_files' => PortfolioFile::count(),
                'pending_portfolios' => PortfolioFile::where('status', 'pending')->count(),
                'evaluated_portfolios' => PortfolioFile::where('status', 'evaluated')->count(),
                'rejected_portfolios' => PortfolioFile::where('status', 'rejected')->count(),
                'average_portfolio_score' => PortfolioEvaluation::avg('score') ?? 0,

                // By Faculty
                'by_faculty' => Faculty::withCount([
                    'users as teachers_count' => function($q) {
                        $q->whereHas('roles', function($r) {
                            $r->where('name', 'teacher');
                        });
                    },
                    'users as portfolio_files_count' => function($q) {
                        $q->whereHas('portfolioFiles');
                    }
                ])->with(['users' => function($q) {
                    $q->whereHas('roles', function($r) {
                        $r->where('name', 'teacher');
                    })->withCount([
                        'testResults as test_count',
                        'portfolioFiles as portfolio_count'
                    ]);
                }])->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Overall statistics error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher report
     */
    public function getTeacherReport($userId)
    {
        try {
            $teacher = User::with(['faculty', 'department'])
                ->withCount([
                    'testResults as total_tests',
                    'testResults as passed_tests' => function($q) {
                        $q->where('passed', true);
                    },
                    'portfolioFiles as total_files',
                    'portfolioFiles as evaluated_files' => function($q) {
                        $q->where('status', 'evaluated');
                    }
                ])
                ->findOrFail($userId);

            // Test results
            $testResults = TestResult::with('test')
                ->where('user_id', $userId)
                ->whereNotNull('finished_at')
                ->orderBy('finished_at', 'desc')
                ->get();

            $averageTestScore = $testResults->avg('percentage') ?? 0;

            // Portfolio files
            $portfolioFiles = PortfolioFile::with(['category', 'evaluation'])
                ->where('user_id', $userId)
                ->orderBy('uploaded_at', 'desc')
                ->get();

            $averagePortfolioScore = $portfolioFiles
                ->filter(fn($f) => $f->evaluation)
                ->avg('evaluation.score') ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'teacher' => $teacher,
                    'test_stats' => [
                        'total' => $teacher->total_tests,
                        'passed' => $teacher->passed_tests,
                        'average_score' => round($averageTestScore, 2)
                    ],
                    'portfolio_stats' => [
                        'total' => $teacher->total_files,
                        'evaluated' => $teacher->evaluated_files,
                        'average_score' => round($averagePortfolioScore, 2)
                    ],
                    'test_results' => $testResults,
                    'portfolio_files' => $portfolioFiles
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Teacher report error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get faculty report
     */
    public function getFacultyReport($facultyId)
    {
        try {
            $faculty = Faculty::with(['departments'])
                ->withCount([
                    'users as teachers_count' => function($q) {
                        $q->whereHas('roles', function($r) {
                            $r->where('name', 'teacher');
                        });
                    }
                ])
                ->findOrFail($facultyId);

            // Teachers in this faculty
            $teachers = User::where('faculty_id', $facultyId)
                ->whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                })
                ->with(['department'])
                ->withCount([
                    'testResults as total_tests',
                    'testResults as passed_tests' => function($q) {
                        $q->where('passed', true);
                    },
                    'portfolioFiles as total_files',
                    'portfolioFiles as evaluated_files' => function($q) {
                        $q->where('status', 'evaluated');
                    }
                ])
                ->get();

            // Overall stats
            $totalTests = TestResult::whereHas('user', function($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            })->count();

            $passedTests = TestResult::whereHas('user', function($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            })->where('passed', true)->count();

            $averageTestScore = TestResult::whereHas('user', function($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            })->avg('percentage') ?? 0;

            $totalPortfolios = PortfolioFile::whereHas('user', function($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            })->count();

            $evaluatedPortfolios = PortfolioFile::whereHas('user', function($q) use ($facultyId) {
                $q->where('faculty_id', $facultyId);
            })->where('status', 'evaluated')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'faculty' => $faculty,
                    'teachers' => $teachers,
                    'stats' => [
                        'total_teachers' => $faculty->teachers_count,
                        'total_tests' => $totalTests,
                        'passed_tests' => $passedTests,
                        'average_test_score' => round($averageTestScore, 2),
                        'total_portfolios' => $totalPortfolios,
                        'evaluated_portfolios' => $evaluatedPortfolios
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Faculty report error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
