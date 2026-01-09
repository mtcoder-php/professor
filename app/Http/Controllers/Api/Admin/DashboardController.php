<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\User;
use App\Models\Test;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'faculties' => Faculty::where('is_active', true)->count(),
                'departments' => Department::where('is_active', true)->count(),
                'teachers' => User::whereHas('roles', function($q) {
                    $q->where('name', 'teacher');
                })->count(),
                'tests' => Test::where('is_active', true)->count(),

                // Qo'shimcha ma'lumotlar
                'active_tests' => Test::where('is_active', true)->count(),
                'entry_tests' => Test::where('type', 'entry')->where('is_active', true)->count(),
                'exit_tests' => Test::where('type', 'exit')->where('is_active', true)->count(),
                'total_users' => User::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin stats error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }
}
