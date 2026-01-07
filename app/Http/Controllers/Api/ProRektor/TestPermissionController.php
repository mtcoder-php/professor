<?php

namespace App\Http\Controllers\Api\ProRektor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Test;
use App\Models\UserTestPermission;

class TestPermissionController extends Controller
{
    /**
     * Get all teachers with their test permissions
     */
    public function index(Request $request)
    {
        try {
            $query = User::role('teacher')
                ->with(['faculty', 'department']);

            // Filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('full_name', 'like', "%{$search}%");
            }

            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            $perPage = $request->get('per_page', 15);
            $teachers = $query->paginate($perPage);

            // Load test permissions for each teacher
            $teachers->getCollection()->transform(function ($teacher) {
                $teacher->test_permissions = UserTestPermission::where('user_id', $teacher->id)
                    ->with('test')
                    ->get();
                return $teacher;
            });

            return response()->json([
                'success' => true,
                'data' => $teachers
            ]);

        } catch (\Exception $e) {
            \Log::error('Test permissions index error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all tests
     */
    public function getTests()
    {
        try {
            $tests = Test::select('id', 'title', 'type', 'is_active')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tests
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grant or revoke test permission
     */
    public function togglePermission(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'test_id' => 'required|exists:tests,id',
                'is_allowed' => 'required|boolean'
            ]);

            $permission = UserTestPermission::updateOrCreate(
                [
                    'user_id' => $validated['user_id'],
                    'test_id' => $validated['test_id']
                ],
                [
                    'is_allowed' => $validated['is_allowed'],
                    'allowed_by' => Auth::id(),
                    'allowed_at' => $validated['is_allowed'] ? now() : null
                ]
            );

            $permission->load('test');

            return response()->json([
                'success' => true,
                'message' => $validated['is_allowed'] ? 'Ruxsat berildi' : 'Ruxsat bekor qilindi',
                'data' => $permission
            ]);

        } catch (\Exception $e) {
            \Log::error('Toggle permission error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grant permission to multiple users
     */
    public function bulkGrant(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
                'test_id' => 'required|exists:tests,id'
            ]);

            $count = 0;
            foreach ($validated['user_ids'] as $userId) {
                UserTestPermission::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'test_id' => $validated['test_id']
                    ],
                    [
                        'is_allowed' => true,
                        'allowed_by' => Auth::id(),
                        'allowed_at' => now()
                    ]
                );
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "{$count} ta o'qituvchiga ruxsat berildi"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
