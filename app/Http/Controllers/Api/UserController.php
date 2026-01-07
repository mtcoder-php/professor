<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
class UserController extends Controller
{
    /**
     * Display a listing of users with search and filters
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['faculty', 'department', 'roles']);

            // Search - TUZATILDI ←
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('passport_series', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('scientific_degree', 'like', "%{$search}%");
                });
            }

            // Faculty filter
            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            // Department filter
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Scientific degree filter
            if ($request->filled('scientific_degree')) {
                $query->where('scientific_degree', $request->scientific_degree);
            }

            // Role filter
            if ($request->filled('role')) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination - TUZATILDI ←
            $perPage = $request->get('per_page', 10);

            // Validate per_page
            if (!in_array($perPage, [10, 25, 50, 100])) {
                $perPage = 10;
            }

            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            \Log::error('User index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'faculty_id' => 'required|exists:faculties,id',
                'department_id' => 'required|exists:departments,id',
                'scientific_degree' => 'required|string',
                'passport_series' => 'required|string|unique:users,passport_series|regex:/^[A-Z]{2}[0-9]{7}$/',
                'birth_date' => 'required|date',
                'phone' => 'nullable|string',
                'email' => 'nullable|email|unique:users,email',
                'avatar' => 'nullable|image|max:2048',
                'role' => 'required|string|in:admin,prorektor,teacher'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validatsiya xatosi',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // Create user
            $user = User::create([
                'full_name' => $request->full_name,
                'faculty_id' => $request->faculty_id,
                'department_id' => $request->department_id,
                'scientific_degree' => $request->scientific_degree,
                'passport_series' => strtoupper($request->passport_series),
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'email' => $request->email,
                'avatar' => $avatarPath,
                'password' => Hash::make(str_replace('.', '', date('d.m.Y', strtotime($request->birth_date))))
            ]);

            // Assign role
            $user->assignRole($request->role);

            return response()->json([
                'success' => true,
                'message' => 'O\'qituvchi muvaffaqiyatli qo\'shildi',
                'data' => $user->load(['faculty', 'department', 'roles'])
            ]);

        } catch (\Exception $e) {
            \Log::error('User store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        try {
            $user->load(['faculty', 'department', 'roles']);

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            \Log::error('User show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'faculty_id' => 'required|exists:faculties,id',
                'department_id' => 'required|exists:departments,id',
                'scientific_degree' => 'required|string',
                'passport_series' => 'required|string|regex:/^[A-Z]{2}[0-9]{7}$/|unique:users,passport_series,' . $user->id,
                'birth_date' => 'required|date',
                'phone' => 'nullable|string',
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'avatar' => 'nullable|image|max:2048',
                'role' => 'required|string|in:admin,prorektor,teacher'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validatsiya xatosi',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar) {
                    \Storage::disk('public')->delete($user->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            // Update user
            $user->update([
                'full_name' => $request->full_name,
                'faculty_id' => $request->faculty_id,
                'department_id' => $request->department_id,
                'scientific_degree' => $request->scientific_degree,
                'passport_series' => strtoupper($request->passport_series),
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'email' => $request->email
            ]);

            // Update role
            $user->syncRoles([$request->role]);

            return response()->json([
                'success' => true,
                'message' => 'O\'qituvchi muvaffaqiyatli yangilandi',
                'data' => $user->load(['faculty', 'department', 'roles'])
            ]);

        } catch (\Exception $e) {
            \Log::error('User update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        try {
            // Delete avatar
            if ($user->avatar) {
                \Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'O\'qituvchi muvaffaqiyatli o\'chirildi'
            ]);

        } catch (\Exception $e) {
            \Log::error('User destroy error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'role' => 'required|string|in:admin,prorektor,teacher'
            ]);

            $user->syncRoles([$validated['role']]);
            $user->load('roles');

            return response()->json([
                'success' => true,
                'message' => 'Rol muvaffaqiyatli biriktirildi',
                'data' => $user
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Assign role error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /*#####################################################################*/
    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        try {
            // CSV header ANIQ nomlar
            $headers = [
                'FIO',
                'Kafedra kodi',
                'Ilmiy darajasi',
                'Passport seriya',
                'Tugilgan kun',
                'Telefon',
                'Email'
            ];

            $sampleData = [
                [
                    'Ahmadov Jamshid Karimovich',
                    'TK',
                    'PhD',
                    'AB1234567',
                    '15.03.1985',
                    '998901234567',
                    'ahmadov@university.uz'
                ],
                [
                    'Karimova Dilnoza Akramovna',
                    'MK',
                    'Fan doktori',
                    'AC7654321',
                    '22.07.1978',
                    '998901234568',
                    'karimova@university.uz'
                ],
                [
                    'Rahimov Sherzod Alisher o\'g\'li',
                    'ITK',
                    'Dotsent',
                    'AD9876543',
                    '10.11.1992',
                    '998901234569',
                    'rahimov@university.uz'
                ]
            ];

            // Create CSV content
            $csv = implode(',', $headers) . "\n";
            foreach ($sampleData as $row) {
                $csv .= implode(',', $row) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="users_template.csv"',
            ]);

        } catch (\Exception $e) {
            \Log::error('Template download error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import users from Excel
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120' // 5MB
            ]);

            $import = new UsersImport();
            Excel::import($import, $request->file('file'));

            $results = $import->getResults();

            return response()->json([
                'success' => true,
                'message' => 'Import yakunlandi',
                'data' => $results
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatosi',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Import xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users to Excel
     */
    public function export(Request $request)
    {
        try {
            $query = User::with(['faculty', 'department', 'roles']);

            // Apply filters
            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            $users = $query->get();

            // Create CSV
            $headers = [
                'ID',
                'FIO',
                'Fakultet',
                'Kafedra',
                'Passport',
                'Ilmiy daraja',
                'Telefon',
                'Email',
                'Rol',
                'Yaratilgan'
            ];

            $csv = implode(',', $headers) . "\n";

            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->full_name,
                    $user->faculty?->name ?? '',
                    $user->department?->name ?? '',
                    $user->passport_series,
                    $user->scientific_degree ?? '',
                    $user->phone ?? '',
                    $user->email ?? '',
                    $user->roles?->first()?->name ?? 'teacher',
                    $user->created_at->format('Y-m-d')
                ];
                $csv .= implode(',', $row) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="users_export_' . date('Y-m-d') . '.csv"',
            ]);

        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Export xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}






