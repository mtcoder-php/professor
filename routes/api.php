<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacultyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Api\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Api\Teacher\TestController as TeacherTestController;
use App\Http\Controllers\Api\Teacher\PortfolioController as TeacherPortfolioController;
use App\Http\Controllers\Api\ProRektor\DashboardController as ProRektorDashboardController;
use App\Http\Controllers\Api\ProRektor\TestPermissionController;
use App\Http\Controllers\Api\ProRektor\TestResultController;
use App\Http\Controllers\Api\ProRektor\PortfolioEvaluationController;
use App\Http\Controllers\Api\ProRektor\ReportController;

// =====================================================
// AUTH ROUTES - ENG BIRINCHI
// =====================================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// =====================================================
// PUBLIC DOWNLOAD ROUTES - AUTH'DAN KEYIN
// =====================================================
Route::get('/download/portfolio/{fileId}', [PortfolioEvaluationController::class, 'download'])
    ->name('portfolio.download');

Route::get('/download/teacher/portfolio/{fileId}', [TeacherPortfolioController::class, 'download'])
    ->name('teacher.portfolio.download');

// =====================================================
// PUBLIC DATA ROUTES
// =====================================================
Route::get('/faculties/all', [FacultyController::class, 'getAllFaculties']);
Route::get('/faculties', [FacultyController::class, 'index']);
Route::get('/departments', [DepartmentController::class, 'index']);
Route::get('/departments/all', [DepartmentController::class, 'all']);

// =====================================================
// ADMIN ROUTES
// =====================================================
Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    // Faculties
    Route::get('/faculties', [FacultyController::class, 'index']);
    Route::post('/faculties', [FacultyController::class, 'store']);
    Route::get('/faculties/{id}', [FacultyController::class, 'show']);
    Route::put('/faculties/{id}', [FacultyController::class, 'update']);
    Route::delete('/faculties/{id}', [FacultyController::class, 'destroy']);

    // Departments
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::get('/departments/{id}', [DepartmentController::class, 'show']);
    Route::put('/departments/{id}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

    // Users
    Route::get('/users/template/download', [UserController::class, 'downloadTemplate']); // ← SPECIFIC FIRST!
    Route::post('/users/import', [UserController::class, 'import']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);

    // Tests
    Route::get('/tests/template/download', [TestController::class, 'downloadTemplate']); // ← SPECIFIC FIRST!
    Route::get('/tests', [TestController::class, 'index']);
    Route::post('/tests', [TestController::class, 'store']);
    Route::get('/tests/{id}/questions', [TestController::class, 'getQuestions']); // ← SPECIFIC FIRST!
    Route::post('/tests/{id}/questions', [TestController::class, 'addQuestion']);
    Route::post('/tests/{id}/import-questions', [TestController::class, 'importQuestions']);
    Route::delete('/tests/{testId}/questions/{questionId}', [TestController::class, 'deleteQuestion']);
    Route::get('/tests/{id}', [TestController::class, 'show']);
    Route::put('/tests/{id}', [TestController::class, 'update']);
    Route::delete('/tests/{id}', [TestController::class, 'destroy']);
    Route::patch('/tests/{id}/toggle-status', [TestController::class, 'toggleStatus']);
});

// =====================================================
// TEACHER ROUTES
// =====================================================
Route::middleware(['auth:sanctum'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index']);

    // Profile
    Route::get('/profile', [TeacherProfileController::class, 'show']);
    Route::post('/profile', [TeacherProfileController::class, 'update']);
    Route::post('/profile/change-password', [TeacherProfileController::class, 'changePassword']);

    // Tests
    Route::get('/test-results', [TeacherTestController::class, 'results']); // ← SPECIFIC FIRST!
    Route::get('/tests', [TeacherTestController::class, 'index']);
    Route::get('/tests/{id}', [TeacherTestController::class, 'show']);
    Route::post('/tests/{id}/submit', [TeacherTestController::class, 'submit']);

    // Portfolio
    Route::get('/portfolio-statistics', [TeacherPortfolioController::class, 'statistics']); // ← SPECIFIC FIRST!
    Route::get('/portfolio/{id}/download-url', [TeacherPortfolioController::class, 'getDownloadUrl']); // ← SPECIFIC!
    Route::get('/portfolio', [TeacherPortfolioController::class, 'index']);
    Route::post('/portfolio/upload', [TeacherPortfolioController::class, 'upload']);
    Route::get('/portfolio/{id}', [TeacherPortfolioController::class, 'show']);
    Route::put('/portfolio/{id}', [TeacherPortfolioController::class, 'update']);
    Route::delete('/portfolio/{id}', [TeacherPortfolioController::class, 'destroy']);
});

// =====================================================
// PROREKTOR ROUTES
// =====================================================
Route::middleware(['auth:sanctum'])->prefix('prorektor')->group(function () {
    Route::get('/dashboard', [ProRektorDashboardController::class, 'index']);

    // Test Permissions
    Route::get('/tests', [TestPermissionController::class, 'getTests']); // ← SPECIFIC FIRST!
    Route::get('/test-permissions', [TestPermissionController::class, 'index']);
    Route::post('/test-permissions/toggle', [TestPermissionController::class, 'togglePermission']);
    Route::post('/test-permissions/bulk-grant', [TestPermissionController::class, 'bulkGrant']);

    // Test Results
    Route::get('/test-results', [TestResultController::class, 'index']);
    Route::get('/test-results/{id}', [TestResultController::class, 'show']);

    // Portfolio Evaluation
    Route::get('/portfolio-statistics', [PortfolioEvaluationController::class, 'statistics']); // ← SPECIFIC FIRST!
    Route::get('/portfolio-evaluations/pending', [PortfolioEvaluationController::class, 'getPendingFiles']); // ← SPECIFIC!
    Route::get('/portfolio-evaluations/teacher/{userId}', [PortfolioEvaluationController::class, 'getTeacherPortfolio']); // ← SPECIFIC!
    Route::get('/portfolio-evaluations/{fileId}/download-url', [PortfolioEvaluationController::class, 'getDownloadUrl']); // ← SPECIFIC!
    Route::get('/portfolio-evaluations', [PortfolioEvaluationController::class, 'index']); // ← GENERAL LAST!
    Route::post('/portfolio-evaluations/{fileId}/evaluate', [PortfolioEvaluationController::class, 'evaluate']);

    // Reports
    Route::get('/reports/overall', [ReportController::class, 'getOverallStatistics']); // ← SPECIFIC FIRST!
    Route::get('/reports/teacher/{userId}', [ReportController::class, 'getTeacherReport']);
    Route::get('/reports/faculty/{facultyId}', [ReportController::class, 'getFacultyReport']);
});
