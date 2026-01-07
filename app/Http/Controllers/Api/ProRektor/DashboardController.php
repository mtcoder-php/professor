<?php

namespace App\Http\Controllers\Api\ProRektor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Test;
use App\Models\UserTestPermission;
use App\Models\TestResult;
use App\Models\PortfolioFile;

class DashboardController extends Controller
{
    /**
     * Get prorektor dashboard data
     */
    public function index()
    {
        try {
            $user = Auth::user();

            // Statistics
            $totalTeachers = User::role('teacher')->count();
            $totalTests = Test::active()->count();

            $testPermissions = UserTestPermission::where('is_allowed', true)->count();
            $completedTests = TestResult::distinct('user_id')->count('user_id');

            $portfolioStats = [
                'pending' => PortfolioFile::where('status', 'pending')->count(),
                'evaluated' => PortfolioFile::where('status', 'evaluated')->count(),
                'rejected' => PortfolioFile::where('status', 'rejected')->count(),
                'total' => PortfolioFile::count()
            ];

            // Recent test results
            $recentResults = TestResult::with(['user', 'test'])
                ->latest()
                ->take(10)
                ->get();

            // Pending portfolios
            $pendingPortfolios = PortfolioFile::where('status', 'pending')
                ->with(['user', 'category'])
                ->latest()
                ->take(10)
                ->get();

            $data = [
                'user' => $user,
                'statistics' => [
                    'total_teachers' => $totalTeachers,
                    'total_tests' => $totalTests,
                    'test_permissions' => $testPermissions,
                    'completed_tests' => $completedTests,
                    'portfolio' => $portfolioStats
                ],
                'recent_results' => $recentResults,
                'pending_portfolios' => $pendingPortfolios
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('ProRektor Dashboard error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
