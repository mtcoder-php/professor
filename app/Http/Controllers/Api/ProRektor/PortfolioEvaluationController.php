<?php

namespace App\Http\Controllers\Api\ProRektor;

use App\Http\Controllers\Controller;
use App\Models\PortfolioFile;
use App\Models\PortfolioEvaluation;
use App\Models\PortfolioCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class PortfolioEvaluationController extends Controller
{
    /**
     * Get all teachers with portfolio statistics
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['faculty', 'department'])
                ->withCount([
                    'portfolioFiles',
                    'portfolioFiles as pending_files_count' => function($q) {
                        $q->where('status', 'pending');
                    },
                    'portfolioFiles as evaluated_files_count' => function($q) {
                        $q->where('status', 'evaluated');
                    }
                ]);

            // Filters
            if ($request->filled('search')) {
                $query->where('full_name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('faculty_id')) {
                $query->where('faculty_id', $request->faculty_id);
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            $perPage = $request->get('per_page', 15);
            $teachers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $teachers
            ]);

        } catch (\Exception $e) {
            \Log::error('Portfolio evaluations index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher's portfolio for evaluation
     */
    public function getTeacherPortfolio($userId)
    {
        try {
            $teacher = User::with(['faculty', 'department'])->findOrFail($userId);

            $categories = PortfolioCategory::with(['files' => function($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->with('evaluation');
            }])->where('is_active', true)
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'teacher' => $teacher,
                    'categories' => $categories
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get teacher portfolio error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending files for evaluation
     */
    public function getPendingFiles(Request $request)
    {
        try {
            $query = PortfolioFile::with(['user', 'category'])
                ->where('status', 'pending');

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $files = $query->orderBy('uploaded_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $files
            ]);

        } catch (\Exception $e) {
            \Log::error('Get pending files error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Evaluate a portfolio file
     */
    public function evaluate(Request $request, $fileId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'score' => 'required|numeric|min:0|max:100',
                'comment' => 'nullable|string|max:1000',
                'status' => 'required|in:evaluated,rejected'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validatsiya xatosi',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = PortfolioFile::findOrFail($fileId);

            // Create or update evaluation
            $evaluation = PortfolioEvaluation::updateOrCreate(
                ['file_id' => $fileId],
                [
                    'evaluated_by' => auth()->id(),
                    'score' => $request->score,
                    'comment' => $request->comment,
                    'evaluated_at' => now()
                ]
            );

            // Update file status
            $file->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Portfolio baholandi',
                'data' => $evaluation->load('evaluator')
            ]);

        } catch (\Exception $e) {
            \Log::error('Portfolio evaluation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get signed download URL - YANGI METHOD ←
     */
    public function getDownloadUrl($fileId)
    {
        try {
            $file = PortfolioFile::findOrFail($fileId);

            // Create temporary signed URL (valid for 5 minutes)
            $url = URL::temporarySignedRoute(
                'portfolio.download',
                now()->addMinutes(5),
                ['fileId' => $fileId]
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'filename' => $file->file_name
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get download URL error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file (NO AUTH REQUIRED - uses signed URL) - YANGI METHOD ←
     */
    public function download(Request $request, $fileId)
    {
        // Validate signed URL
        if (!$request->hasValidSignature()) {
            return response()->json([
                'success' => false,
                'message' => 'Havola muddati tugagan yoki noto\'g\'ri'
            ], 401);
        }

        try {
            $file = PortfolioFile::findOrFail($fileId);

            $filePath = storage_path('app/public/' . $file->file_path);

            \Log::info('Attempting download: ' . $filePath);

            if (!file_exists($filePath)) {
                \Log::error('File not found: ' . $filePath);

                return response()->json([
                    'success' => false,
                    'message' => 'Fayl topilmadi'
                ], 404);
            }

            // Return file for download
            return response()->download($filePath, $file->file_name, [
                'Content-Type' => $this->getMimeType($file->file_name),
            ]);

        } catch (\Exception $e) {
            \Log::error('File download error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Faylni yuklashda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get portfolio statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_files' => PortfolioFile::count(),
                'pending_files' => PortfolioFile::where('status', 'pending')->count(),
                'evaluated_files' => PortfolioFile::where('status', 'evaluated')->count(),
                'rejected_files' => PortfolioFile::where('status', 'rejected')->count(),
                'average_score' => PortfolioEvaluation::avg('score') ?? 0
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Portfolio statistics error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get MIME type by file extension - HELPER METHOD ←
     */
    private function getMimeType($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
