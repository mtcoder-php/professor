<?php

namespace App\Http\Controllers\Api\ProRektor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\PortfolioFile;
use App\Models\UserTestPermission;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get ProRektor dashboard statistics
     */
    public function index()
    {
        try {
            \Log::info('📊 ProRektor dashboard requested');

            $stats = [
                // Total teachers
                'total_teachers' => User::whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                })->count(),

                // Total tests
                'total_tests' => Test::where('is_active', true)->count(),

                // Teachers who completed tests
                'completed_tests' => TestResult::whereNotNull('finished_at')
                    ->distinct('user_id')
                    ->count('user_id'),

                // Pending permissions
                'pending_permissions' => UserTestPermission::where('is_allowed', false)
                    ->count(),

                // Portfolios pending evaluation
                'pending_portfolios' => PortfolioFile::where('status', 'pending')
                    ->count(),

                // Evaluated portfolios
                'evaluated_portfolios' => PortfolioFile::where('status', 'evaluated')
                    ->count(),

                // Test pass rate
                'passed_tests' => TestResult::where('passed', true)
                    ->whereNotNull('finished_at')
                    ->count(),

                'failed_tests' => TestResult::where('passed', false)
                    ->whereNotNull('finished_at')
                    ->count(),
            ];

            // Calculate pass rate
            $totalCompletedTests = $stats['passed_tests'] + $stats['failed_tests'];
            $stats['pass_rate'] = $totalCompletedTests > 0
                ? round(($stats['passed_tests'] / $totalCompletedTests) * 100, 1)
                : 0;

            \Log::info('✅ ProRektor stats calculated', $stats);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ProRektor dashboard error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities for ProRektor
     */
    public function getRecentActivity()
    {
        try {
            $activities = collect();

            // Recent test permissions (last 2)
            $recentPermissions = UserTestPermission::with(['user', 'test'])
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get()
                ->map(function($permission) {
                    return [
                        'type' => 'permission_granted',
                        'icon' => 'check',
                        'title' => 'Test ruxsati berildi',
                        'description' => $permission->user->full_name . ' - ' . $permission->test->title,
                        'timestamp' => $permission->created_at,
                        'color' => 'green'
                    ];
                });

            $activities = $activities->merge($recentPermissions);

            // Recent test submissions (last 2)
            $recentTests = TestResult::with(['user', 'test'])
                ->whereNotNull('finished_at')
                ->orderBy('finished_at', 'desc')
                ->limit(2)
                ->get()
                ->map(function($result) {
                    return [
                        'type' => 'test_completed',
                        'icon' => 'clipboard',
                        'title' => 'Test topshirildi',
                        'description' => $result->user->full_name . ' - ' . $result->test->title . ' (' . $result->percentage . '%)',
                        'timestamp' => $result->finished_at,
                        'color' => $result->passed ? 'green' : 'red'
                    ];
                });

            $activities = $activities->merge($recentTests);

            // Recent portfolio uploads (last 2)
            $recentPortfolios = PortfolioFile::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get()
                ->map(function($file) {
                    return [
                        'type' => 'portfolio_uploaded',
                        'icon' => 'folder',
                        'title' => 'Portfolio yuklandi',
                        'description' => $file->user->full_name ?? 'Unknown',
                        'timestamp' => $file->created_at,
                        'color' => 'purple'
                    ];
                });

            $activities = $activities->merge($recentPortfolios);

            // Sort by timestamp and take 4
            $activities = $activities
                ->sortByDesc('timestamp')
                ->take(4)
                ->values();

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            \Log::error('ProRektor recent activity error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'data' => []
            ], 200);
        }
    }
}
