<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\PortfolioFile;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PortfolioController extends Controller
{
    /**
     * Get teacher's portfolio
     */
    public function index()
    {
        try {
            $categories = PortfolioCategory::with(['files' => function($query) {
                $query->where('user_id', auth()->id())
                    ->with('evaluation.evaluator');
            }])->where('is_active', true)
                ->orderBy('order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            \Log::error('Portfolio index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload portfolio file
     */
    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|exists:portfolio_categories,id',
                'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
                'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
                'description' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validatsiya xatosi',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');

            // Store file
            $path = $file->store('portfolio/' . auth()->id(), 'public');

            // Create portfolio file record
            $portfolioFile = PortfolioFile::create([
                'user_id' => auth()->id(),
                'category_id' => $request->category_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientOriginalExtension(),
                'year' => $request->year,
                'description' => $request->description,
                'status' => 'pending',
                'uploaded_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fayl muvaffaqiyatli yuklandi',
                'data' => $portfolioFile
            ]);

        } catch (\Exception $e) {
            \Log::error('Portfolio upload error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Faylni yuklashda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show specific portfolio file
     */
    public function show($id)
    {
        try {
            $file = PortfolioFile::where('id', $id)
                ->where('user_id', auth()->id())
                ->with(['category', 'evaluation.evaluator'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $file
            ]);

        } catch (\Exception $e) {
            \Log::error('Portfolio show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update portfolio file
     */
    public function update(Request $request, $id)
    {
        try {
            $file = PortfolioFile::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $validator = Validator::make($request->all(), [
                'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
                'description' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validatsiya xatosi',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file->update([
                'year' => $request->year,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fayl yangilandi',
                'data' => $file
            ]);

        } catch (\Exception $e) {
            \Log::error('Portfolio update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete portfolio file
     */
    public function destroy($id)
    {
        try {
            $file = PortfolioFile::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Delete file from storage
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            $file->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fayl o\'chirildi'
            ]);

        } catch (\Exception $e) {
            \Log::error('Portfolio destroy error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get signed download URL - YANGI ←
     */
    public function getDownloadUrl($id)
    {
        try {
            $file = PortfolioFile::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Create temporary signed URL (valid for 5 minutes)
            $url = URL::temporarySignedRoute(
                'teacher.portfolio.download',
                now()->addMinutes(5),
                ['fileId' => $id]
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
     * Download file (NO AUTH REQUIRED - uses signed URL) - YANGI ←
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

            if (!file_exists($filePath)) {
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
                'total_files' => PortfolioFile::where('user_id', auth()->id())->count(),
                'pending_files' => PortfolioFile::where('user_id', auth()->id())->where('status', 'pending')->count(),
                'evaluated_files' => PortfolioFile::where('user_id', auth()->id())->where('status', 'evaluated')->count(),
                'rejected_files' => PortfolioFile::where('user_id', auth()->id())->where('status', 'rejected')->count(),
                'average_score' => PortfolioFile::where('user_id', auth()->id())
                        ->whereHas('evaluation')
                        ->with('evaluation')
                        ->get()
                        ->avg('evaluation.score') ?? 0
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
     * Get MIME type by file extension - HELPER ←
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
