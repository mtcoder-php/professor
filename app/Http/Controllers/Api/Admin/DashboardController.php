<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\Test;
use App\Models\TestResult;
use App\Models\PortfolioFile;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_faculties' => Faculty::count(),
                'total_departments' => Department::count(),
                'total_teachers' => User::whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                })->count(),
                'total_tests' => Test::count(),
                'active_tests' => Test::where('is_active', true)->count(),
                'completed_tests' => TestResult::whereNotNull('finished_at')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Get recent activity (last 10 activities)
     */
    /**
     * Get recent activity (last 4 activities)
     */
    public function getRecentActivity()
    {
        try {
            $activities = collect();

            // Recent users (last 2)
            $recentUsers = User::whereHas('roles', function($q) {
                $q->where('name', 'teacher');
            })
                ->orderBy('created_at', 'desc')
                ->limit(2) // ← 5 dan 2 ga
                ->get()
                ->map(function($user) {
                    return [
                        'type' => 'user_created',
                        'icon' => 'user',
                        'title' => 'Yangi o\'qituvchi qo\'shildi',
                        'description' => $user->full_name,
                        'timestamp' => $user->created_at,
                        'color' => 'green'
                    ];
                });

            $activities = $activities->merge($recentUsers);

            // Recent tests (last 2)
            $recentTests = Test::orderBy('created_at', 'desc')
                ->limit(2) // ← 5 dan 2 ga
                ->get()
                ->map(function($test) {
                    return [
                        'type' => 'test_created',
                        'icon' => 'clipboard',
                        'title' => 'Test yaratildi',
                        'description' => $test->title,
                        'timestamp' => $test->created_at,
                        'color' => 'blue'
                    ];
                });

            $activities = $activities->merge($recentTests);

            // Recent portfolio uploads (last 2)
            $recentPortfolios = PortfolioFile::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(2) // ← 5 dan 2 ga
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

            // Sort by timestamp and take only 4 most recent ←
            $activities = $activities
                ->sortByDesc('timestamp')
                ->take(4) // ← 10 dan 4 ga
                ->values();

            \Log::info('Recent activity loaded', [
                'count' => $activities->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            \Log::error('Recent activity error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'data' => []
            ], 200);
        }
    }
}
