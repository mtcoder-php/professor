<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Admin uchun - Pagination bilan
     */
    public function index(Request $request)
    {
        try {
            $query = Department::with('faculty');

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('head_name', 'like', "%{$search}%");
                });
            }

            // Faculty filter
            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            // Status filter
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Count users
            $query->withCount('users');

            // Sort
            $query->orderBy('created_at', 'desc');

            // Paginate
            $perPage = $request->get('per_page', 10);
            $departments = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);

        } catch (\Exception $e) {
            \Log::error('Department index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Barcha kafedralar - Pagination'siz (Dropdown uchun)
     */
    public function all(Request $request)
    {
        try {
            $query = Department::with('faculty');

            // Faculty filter
            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            // Active filter
            if ($request->filled('active')) {
                $query->where('is_active', true);
            }

            $departments = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $departments
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
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code|alpha_dash',
            'faculty_id' => 'required|exists:faculties,id',
            'head_name' => 'nullable|string|max:255',
            'phone' => 'nullable|regex:/^998[0-9]{9}$/',
            'email' => 'nullable|email|max:255',
            'room_number' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Kafedra nomini kiriting',
            'code.required' => 'Kafedra kodini kiriting',
            'code.unique' => 'Bu kod allaqachon ishlatilgan',
            'code.alpha_dash' => 'Kod faqat harflar, raqamlar va tire bo\'lishi kerak',
            'faculty_id.required' => 'Fakultetni tanlang',
            'faculty_id.exists' => 'Tanlangan fakultet topilmadi',
            'phone.regex' => 'Telefon raqam 998 bilan boshlanishi kerak (998XXXXXXXXX)',
            'email.email' => 'Noto\'g\'ri email format'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validatsiya xatolik',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $department = Department::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'faculty_id' => $request->faculty_id,
                'head_name' => $request->head_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'room_number' => $request->room_number,
                'is_active' => $request->get('is_active', true)
            ]);

            // Load faculty relation
            $department->load('faculty');

            return response()->json([
                'success' => true,
                'message' => 'Kafedra muvaffaqiyatli yaratildi',
                'data' => $department
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Department create error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Kafedra yaratishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified department
     */
    public function show($id)
    {
        try {
            $department = Department::with(['faculty', 'users'])->find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kafedra topilmadi'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $department
            ]);

        } catch (\Exception $e) {
            \Log::error('Department show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, $id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kafedra topilmadi'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:10|unique:departments,code,' . $id . '|alpha_dash',
                'faculty_id' => 'required|exists:faculties,id',
                'head_name' => 'nullable|string|max:255',
                'phone' => 'nullable|regex:/^998[0-9]{9}$/',
                'email' => 'nullable|email|max:255',
                'room_number' => 'nullable|string|max:50',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Kafedra nomini kiriting',
                'code.required' => 'Kafedra kodini kiriting',
                'code.unique' => 'Bu kod allaqachon ishlatilgan',
                'code.alpha_dash' => 'Kod faqat harflar, raqamlar va tire bo\'lishi kerak',
                'faculty_id.required' => 'Fakultetni tanlang',
                'faculty_id.exists' => 'Tanlangan fakultet topilmadi',
                'phone.regex' => 'Telefon raqam 998 bilan boshlanishi kerak',
                'email.email' => 'Noto\'g\'ri email format'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validatsiya xatolik',
                    'errors' => $validator->errors()
                ], 422);
            }

            $department->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'faculty_id' => $request->faculty_id,
                'head_name' => $request->head_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'room_number' => $request->room_number,
                'is_active' => $request->get('is_active', $department->is_active)
            ]);

            // Load faculty relation
            $department->load('faculty');

            return response()->json([
                'success' => true,
                'message' => 'Kafedra muvaffaqiyatli yangilandi',
                'data' => $department
            ]);

        } catch (\Exception $e) {
            \Log::error('Department update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Kafedra yangilashda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified department
     */
    public function destroy($id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kafedra topilmadi'
                ], 404);
            }

            // Check if department has users
            if ($department->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu kafedrada o\'qituvchilar mavjud. Avval o\'qituvchilarni o\'chiring yoki boshqa kafedralarga o\'tkazing.'
                ], 400);
            }

            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kafedra muvaffaqiyatli o\'chirildi'
            ]);

        } catch (\Exception $e) {
            \Log::error('Department delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Kafedra o\'chirishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle department active status
     */
    public function toggleStatus($id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kafedra topilmadi'
                ], 404);
            }

            $department->is_active = !$department->is_active;
            $department->save();

            // Load faculty relation
            $department->load('faculty');

            return response()->json([
                'success' => true,
                'message' => 'Holat muvaffaqiyatli o\'zgartirildi',
                'data' => $department
            ]);

        } catch (\Exception $e) {
            \Log::error('Department toggle status error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active departments (for dropdowns)
     */
    public function active()
    {
        try {
            $departments = Department::with('faculty')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'faculty_id']);

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);

        } catch (\Exception $e) {
            \Log::error('Department active error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
