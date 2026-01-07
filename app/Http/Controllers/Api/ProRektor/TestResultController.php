<?php

namespace App\Http\Controllers\Api\ProRektor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TestResult;

class TestResultController extends Controller
{
    /**
     * Get all test results
     */
    public function index(Request $request)
    {
        try {
            $query = TestResult::with(['user.faculty', 'user.department', 'test']);

            // Filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('test_id')) {
                $query->where('test_id', $request->test_id);
            }

            if ($request->filled('faculty_id')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('faculty_id', $request->faculty_id);
                });
            }

            if ($request->filled('department_id')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }

            if ($request->filled('passed')) {
                $query->where('passed', $request->passed);
            }

            $query->latest();

            $perPage = $request->get('per_page', 15);
            $results = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            \Log::error('Test results index error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed result
     */
    public function show($id)
    {
        try {
            $result = TestResult::with([
                'user.faculty',           // ← MUHIM!
                'user.department',        // ← MUHIM!
                'test.questions.answers'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Natija topilmadi',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
