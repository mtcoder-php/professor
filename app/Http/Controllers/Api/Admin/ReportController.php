<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\TestResult;
use App\Models\Test;
use App\Models\PortfolioFile;
use App\Models\PortfolioEvaluation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeachersReportExport;

class ReportController extends Controller
{
    /**
     * Get overall statistics
     */
    public function getOverallStatistics()
    {
        try {
            $stats = [
                'total_faculties' => Faculty::where('is_active', true)->count(),
                'total_departments' => Department::where('is_active', true)->count(),
                'total_teachers' => User::whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                })->count(),
                'total_tests' => Test::where('is_active', true)->count(),
                'total_test_results' => TestResult::whereNotNull('finished_at')->count(),
                'total_portfolios' => PortfolioFile::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Overall statistics error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Get teachers report by department
     */
    /**
     * Get teachers report by department
     */
    /**
     * Get teachers report by department
     */
    public function getTeachersByDepartment(Request $request)
    {
        try {
            $query = User::with(['faculty', 'department'])
                ->whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                });

            // Faculty filter
            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            // Department filter
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            $teachers = $query->get();

            // Get entry tests
            $entryTests = Test::where('type', 'entry')->where('is_active', true)->get();

            // Get exit tests
            $exitTests = Test::where('type', 'exit')->where('is_active', true)->get();

            $report = [];

            foreach ($teachers as $teacher) {
                $teacherData = [
                    'id' => $teacher->id,
                    'full_name' => $teacher->full_name,
                    'faculty' => $teacher->faculty?->name ?? '—',
                    'department' => $teacher->department?->name ?? '—',
                    'scientific_degree' => $teacher->scientific_degree ?? '—',
                    'entry_tests' => [],
                    'exit_tests' => [],
                    'portfolio_total' => 0,
                ];

                // Entry test results
                foreach ($entryTests as $test) {
                    $result = TestResult::where('user_id', $teacher->id)
                        ->where('test_id', $test->id)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc')
                        ->first();

                    $teacherData['entry_tests'][] = [
                        'test_id' => $test->id,
                        'test_name' => $test->title,
                        'score' => $result ? $result->score : null,
                        'total_points' => $test->total_points,
                        'passed' => $result ? $result->passed : null,
                    ];
                }

                // Exit test results
                foreach ($exitTests as $test) {
                    $result = TestResult::where('user_id', $teacher->id)
                        ->where('test_id', $test->id)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc')
                        ->first();

                    $teacherData['exit_tests'][] = [
                        'test_id' => $test->id,
                        'test_name' => $test->title,
                        'score' => $result ? $result->score : null,
                        'total_points' => $test->total_points,
                        'passed' => $result ? $result->passed : null,
                    ];
                }

                // Portfolio total score (sum of all evaluated files)
                $portfolioTotal = PortfolioFile::where('user_id', $teacher->id)
                    ->where('status', 'evaluated')
                    ->with('evaluation')
                    ->get()
                    ->sum(function($file) {
                        return $file->evaluation ? $file->evaluation->score : 0;
                    });

                $teacherData['portfolio_total'] = $portfolioTotal;

                $report[] = $teacherData;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'teachers' => $report,
                    'entry_tests' => $entryTests->map(fn($t) => [
                        'id' => $t->id,
                        'title' => $t->title,
                        'total_points' => $t->total_points
                    ]),
                    'exit_tests' => $exitTests->map(fn($t) => [
                        'id' => $t->id,
                        'title' => $t->title,
                        'total_points' => $t->total_points
                    ]),
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Teachers report error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $filters = [
                'faculty_id' => $request->faculty_id,
                'department_id' => $request->department_id,
            ];

            $fileName = 'teachers_report_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(
                new TeachersReportExport($filters),
                $fileName
            );

        } catch (\Exception $e) {
            \Log::error('Excel export error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Excel yaratishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export to PDF
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

            $report = [];

            foreach ($teachers as $teacher) {
                $teacherData = [
                    'full_name' => $teacher->full_name,
                    'faculty' => $teacher->faculty?->name ?? '—',
                    'department' => $teacher->department?->name ?? '—',
                    'scientific_degree' => $teacher->scientific_degree ?? '—',
                    'entry_results' => [],
                    'exit_results' => [],
                    'portfolio_score' => 0,
                ];

                // Entry tests
                foreach ($entryTests as $test) {
                    $result = TestResult::where('user_id', $teacher->id)
                        ->where('test_id', $test->id)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc')
                        ->first();

                    $teacherData['entry_results'][] = $result
                        ? $result->score . '/' . $test->total_points
                        : '—';
                }

                // Exit tests
                foreach ($exitTests as $test) {
                    $result = TestResult::where('user_id', $teacher->id)
                        ->where('test_id', $test->id)
                        ->whereNotNull('finished_at')
                        ->orderBy('finished_at', 'desc')
                        ->first();

                    $teacherData['exit_results'][] = $result
                        ? $result->score . '/' . $test->total_points
                        : '—';
                }

                // Portfolio total
                $portfolioTotal = PortfolioFile::where('user_id', $teacher->id)
                    ->where('status', 'evaluated')
                    ->with('evaluation')
                    ->get()
                    ->sum(function($file) {
                        return $file->evaluation ? $file->evaluation->score : 0;
                    });

                $teacherData['portfolio_score'] = $portfolioTotal;

                $report[] = $teacherData;
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.teachers', [
                'teachers' => $report,
                'entryTests' => $entryTests,
                'exitTests' => $exitTests,
                'date' => date('d.m.Y H:i')
            ]);

            $pdf->setPaper('a4', 'landscape');

            $fileName = 'teachers_report_' . date('Y-m-d_His') . '.pdf';

            return $pdf->download($fileName);

        } catch (\Exception $e) {
            \Log::error('PDF export error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'PDF yaratishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
