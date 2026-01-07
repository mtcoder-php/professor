<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FacultyController extends Controller
{
    /**
     * Display a listing of faculties
     * GET /api/admin/faculties
     */
    /**
     * Display a listing of faculties
     */
    public function index(Request $request)
    {
        try {
            $query = Faculty::query();

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('dean_name', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Count departments
            $query->withCount('departments');

            // Sort
            $query->orderBy('created_at', 'desc');

            // Paginate
            $perPage = $request->get('per_page', 10);
            $faculties = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $faculties
            ]);

        } catch (\Exception $e) {
            \Log::error('Faculty index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }




    }
    /**
     * Get all active faculties (for dropdowns/selects) - YANGI ←
     */
    public function getAllFaculties()
    {
        try {
            $faculties = Faculty::where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $faculties
            ]);

        } catch (\Exception $e) {
            \Log::error('Get all faculties error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Store a newly created faculty
     * POST /api/admin/faculties
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:faculties,name',
            'code' => 'required|string|max:10|unique:faculties,code|alpha_dash',
            'dean_name' => 'nullable|string|max:255',
            'phone' => 'nullable|regex:/^998[0-9]{9}$/',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Fakultet nomini kiriting',
            'name.unique' => 'Bu fakultet allaqachon mavjud',
            'code.required' => 'Fakultet kodini kiriting',
            'code.unique' => 'Bu kod allaqachon ishlatilgan',
            'code.alpha_dash' => 'Kod faqat harflar va tire bo\'lishi kerak',
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
            $faculty = Faculty::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'dean_name' => $request->dean_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_active' => $request->get('is_active', true)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fakultet muvaffaqiyatli yaratildi',
                'data' => $faculty
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultet yaratishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified faculty
     * GET /api/admin/faculties/{id}
     */
    public function show($id)
    {
        $faculty = Faculty::with('departments')->find($id);

        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultet topilmadi'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $faculty
        ]);
    }

    /**
     * Update the specified faculty
     * PUT/PATCH /api/admin/faculties/{id}
     */
    public function update(Request $request, $id)
    {
        $faculty = Faculty::find($id);

        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultet topilmadi'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:faculties,name,' . $id,
            'code' => 'required|string|max:10|unique:faculties,code,' . $id . '|alpha_dash',
            'dean_name' => 'nullable|string|max:255',
            'phone' => 'nullable|regex:/^998[0-9]{9}$/',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Fakultet nomini kiriting',
            'name.unique' => 'Bu fakultet allaqachon mavjud',
            'code.required' => 'Fakultet kodini kiriting',
            'code.unique' => 'Bu kod allaqachon ishlatilgan',
            'code.alpha_dash' => 'Kod faqat harflar va tire bo\'lishi kerak',
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

        try {
            $faculty->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'dean_name' => $request->dean_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'is_active' => $request->get('is_active', $faculty->is_active)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fakultet muvaffaqiyatli yangilandi',
                'data' => $faculty
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultet yangilashda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified faculty
     * DELETE /api/admin/faculties/{id}
     */
    public function destroy($id)
    {
        $faculty = Faculty::find($id);

        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultet topilmadi'
            ], 404);
        }

        // Kafedralar bog'langan bo'lsa o'chirishga ruxsat bermaslik
        if ($faculty->departments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu fakultetda kafedralar mavjud. Avval kafedralarni o\'chiring.'
            ], 400);
        }

        try {
            $faculty->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fakultet muvaffaqiyatli o\'chirildi'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultet o\'chirishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle faculty active status
     * PATCH /api/admin/faculties/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        $faculty = Faculty::find($id);

        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultet topilmadi'
            ], 404);
        }

        $faculty->is_active = !$faculty->is_active;
        $faculty->save();

        return response()->json([
            'success' => true,
            'message' => 'Holat o\'zgartirildi',
            'data' => $faculty
        ]);
    }

    /**
     * Get all active faculties (for dropdowns)
     * GET /api/faculties
     */
    public function active()
    {
        $faculties = Faculty::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'success' => true,
            'data' => $faculties
        ]);
    }
}
