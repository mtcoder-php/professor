<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PortfolioFile;
use App\Models\UserTestPermission;
use App\Models\TestResult;

class DashboardController extends Controller
{
    /**
     * Get teacher dashboard data
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $user->load(['faculty', 'department', 'roles']);

            // Portfolio statistics
            $totalCategories = 15;
            $uploadedCategories = PortfolioFile::where('user_id', $user->id)
                ->distinct('category_id')
                ->count('category_id');
            $portfolioPercentage = round(($uploadedCategories / $totalCategories) * 100, 2);

            // Test permissions
            $testPermissions = UserTestPermission::where('user_id', $user->id)
                ->where('is_allowed', true)
                ->with('test')
                ->get();

            // Test results
            $testResults = TestResult::where('user_id', $user->id)
                ->with('test')
                ->latest()
                ->take(5)
                ->get();

            // Portfolio files count
            $portfolioFiles = PortfolioFile::where('user_id', $user->id)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            $data = [
                'user' => $user,
                'statistics' => [
                    'portfolio_percentage' => $portfolioPercentage,
                    'total_categories' => $totalCategories,
                    'uploaded_categories' => $uploadedCategories,
                    'portfolio_files' => [
                        'pending' => $portfolioFiles['pending'] ?? 0,
                        'evaluated' => $portfolioFiles['evaluated'] ?? 0,
                        'rejected' => $portfolioFiles['rejected'] ?? 0,
                        'total' => $portfolioFiles->sum()
                    ],
                    'test_permissions' => $testPermissions->count(),
                    'completed_tests' => $testResults->count()
                ],
                'recent_tests' => $testResults,
                'available_tests' => $testPermissions
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Dashboard error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
