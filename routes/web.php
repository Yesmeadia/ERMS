<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\HallTicketController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExamCentreController;


// Public Homepage Route (accessible to all — guests and authenticated users)
Route::get('/', function () {
    $activeExam = \App\Models\Examination::whereIn('status', ['Registration Started', 'Registartion closed', 'Examination Ongoing', 'result published'])->latest()->first()
        ?? \App\Models\Examination::latest()->first();

    // Top 3 Pass students per category, from published result exams
    $winners = \App\Models\StudentResult::with(['student.school', 'student.category', 'examination'])
        ->where('status', 'Pass')
        ->whereHas('examination', fn($q) => $q->where('status', 'result published'))
        ->orderByDesc('marks_obtained')
        ->get()
        ->groupBy(fn($r) => optional($r->student->category)->name ?? 'General')
        ->map(fn($group) => $group->take(3)->values());

    return view('welcome', compact('activeExam', 'winners'));
})->name('home');

// 1. Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');
});

// Public Gallery (accessible to all — guests and authenticated users)
// Note: URL is /photo-gallery (not /gallery) because public/gallery/ is a real
// asset directory and PHP's built-in server would intercept /gallery as a static path.
Route::get('/photo-gallery', function () {
    return view('gallery');
})->name('gallery');

// MFA Verification Routes (Accessible by authenticated users before completing MFA verification)
Route::get('/login/mfa', [AuthController::class, 'showMfaVerification'])->name('login.mfa');
Route::post('/login/mfa', [AuthController::class, 'verifyMfa'])->name('login.mfa.verify')->middleware('throttle:mfa');

// 2. Public Verification Route (QR Verification Portal)
// Rate-limited to 10 lookups/min per IP to prevent hall ticket enumeration (CWE-330).
Route::get('/verify/hall-ticket/{number}', [VerificationController::class, 'verifyPublic'])
    ->middleware('throttle:verification')
    ->name('verification.hall-ticket');

// Public Results Portal
// Rate-limited to 10 req/min per IP to prevent student enumeration via the /results/{student}/marksheet route.
Route::get('/results/check', [ResultController::class, 'showPublicCheckForm'])->name('results.check-form');
Route::post('/results/check', [ResultController::class, 'checkPublicResult'])
    ->middleware('throttle:verification')
    ->name('results.check-submit');
Route::get('/results/{student}/marksheet', [ResultController::class, 'showPublicResult'])
    ->middleware('throttle:verification')
    ->name('results.marksheet');

// Cashfree Webhook (Public, signature verified inside controller)
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

// 3. SECURE AUTHENTICATED ROUTES
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.update');

    // ============================================
    // SUPER ADMIN (BOARD) ROUTES
    // ============================================
    Route::middleware('role:super-admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'superAdmin'])->name('dashboard');
        Route::get('/activity-logs', [DashboardController::class, 'activityLogs'])->name('activity-logs');

        // MFA Setup
        Route::get('/mfa/setup', [AuthController::class, 'showMfaSetup'])->name('mfa.setup');
        Route::post('/mfa/enable', [AuthController::class, 'enableMfa'])->name('mfa.enable');
        Route::post('/mfa/disable', [AuthController::class, 'disableMfa'])->name('mfa.disable');

        // Staff Management
        Route::resource('staff', StaffController::class);

        // Super Admin Management
        Route::resource('admins', SuperAdminController::class)->except(['show']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Schools Management
        Route::post('/schools/{school}/toggle-status', [SchoolController::class, 'toggleStatus'])->name('schools.toggle-status');
        Route::post('/schools/{school}/assign-admin', [SchoolController::class, 'assignAdmin'])->name('schools.assign-admin');
        Route::post('/schools/{school}/reset-password', [SchoolController::class, 'resetPassword'])->name('schools.reset-password');
        Route::resource('schools', SchoolController::class);

        // Class Master Management
        Route::post('/classes/{class}/toggle-status', [ClassController::class, 'toggleStatus'])->name('classes.toggle-status');
        Route::resource('classes', ClassController::class)->except(['show']);

        // Category Master Management
        Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Examination Management
        Route::post('/examinations/{examination}/status', [ExaminationController::class, 'updateStatus'])->name('examinations.update-status');
        Route::resource('examinations', ExaminationController::class);

        // Verification & Approval Management
        Route::get('/verification', [VerificationController::class, 'index'])->name('verification.index');
        Route::post('/verification/bulk-verify', [VerificationController::class, 'bulkVerify'])->name('verification.bulk-verify');
        Route::get('/verification/{student}', [VerificationController::class, 'show'])->name('verification.show');
        Route::post('/verification/{student}/verify', [VerificationController::class, 'verify'])->name('verification.verify');

        // Students Management (Super Admin)
        Route::get('/students', [StudentController::class, 'adminIndex'])->name('students.index');
        Route::get('/students/{student}', [StudentController::class, 'adminShow'])->name('students.show');
        Route::get('/students/{student}/edit', [StudentController::class, 'adminEdit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'adminUpdate'])->name('students.update');
        Route::post('/students/{student}/issue-registration', [StudentController::class, 'adminIssueRegistration'])->name('students.issue-registration');

        // Hall Ticket Management
        Route::get('/hall-tickets', [HallTicketController::class, 'adminIndex'])->name('hall-tickets.index');
        Route::post('/hall-tickets/{student}/generate', [HallTicketController::class, 'generateSingle'])->name('hall-tickets.generate-single');
        Route::post('/hall-tickets/generate-bulk', [HallTicketController::class, 'generateBulk'])->name('hall-tickets.generate-bulk');
        Route::get('/hall-tickets/{student}/print', [HallTicketController::class, 'printSingle'])->name('hall-tickets.print-single');
        Route::get('/hall-tickets/print-bulk', [HallTicketController::class, 'printBulk'])->name('hall-tickets.print-bulk');

        // Exam Centres Management
        Route::prefix('exam-centres')->name('exam-centres.')->group(function () {
            Route::get('/', [ExamCentreController::class, 'index'])->name('index');
            Route::post('/{school}/toggle', [ExamCentreController::class, 'toggle'])->name('toggle');
            Route::post('/assign', [ExamCentreController::class, 'assignCentres'])->name('assign');
            Route::post('/unassign/{student}', [ExamCentreController::class, 'unassignCentre'])->name('unassign');
            Route::post('/assign-single/{student}', [ExamCentreController::class, 'assignSingle'])->name('assign-single');
        });

        // Reports
        Route::get('/reports', [ReportController::class, 'adminIndex'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'adminExport'])->name('reports.export');

        // Payments & Payouts Report
        Route::get('/payments', [PaymentController::class, 'adminIndex'])->name('payments.index');
        Route::get('/payments/export', [PaymentController::class, 'adminExport'])->name('payments.export');

        // Attendance Management
        Route::get('/attendance', [AttendanceController::class, 'adminAttendanceIndex'])->name('attendance.index');
        Route::post('/attendance/mark', [AttendanceController::class, 'adminAttendanceMark'])->name('attendance.mark');

        // Results Management
        Route::get('/results', [ResultController::class, 'adminIndex'])->name('results.index');
        Route::get('/results/create/{student}', [ResultController::class, 'create'])->name('results.create');
        Route::post('/results', [ResultController::class, 'store'])->name('results.store');
        Route::get('/results/{result}/edit', [ResultController::class, 'edit'])->name('results.edit');
        Route::put('/results/{result}', [ResultController::class, 'update'])->name('results.update');
        Route::delete('/results/{result}', [ResultController::class, 'destroy'])->name('results.destroy');
        Route::get('/results/import', [ResultController::class, 'showImportForm'])->name('results.import-form');
        Route::post('/results/import', [ResultController::class, 'import'])->name('results.import');
    });

    // ============================================
    // SCHOOL ADMIN ROUTES
    // ============================================
    Route::middleware('role:school-admin')->prefix('school')->name('school.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'schoolAdmin'])->name('dashboard');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Student Registration
        Route::get('/students-import/template', [StudentController::class, 'downloadTemplate'])->name('students.import.template');
        Route::post('/students-import', [StudentController::class, 'importExcel'])->name('students.import');
        Route::post('/students/{student}/submit', [StudentController::class, 'submitStudent'])->name('students.submit');
        Route::resource('students', StudentController::class);

        // Hall Ticket Download
        Route::get('/hall-tickets', [HallTicketController::class, 'schoolIndex'])->name('hall-tickets.index');
        Route::get('/hall-tickets/{student}/download', [HallTicketController::class, 'downloadSingle'])->name('hall-tickets.download-single');
        Route::get('/hall-tickets/download-bulk', [HallTicketController::class, 'downloadBulk'])->name('hall-tickets.download-bulk');

        // Reports
        Route::get('/reports', [ReportController::class, 'schoolIndex'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'schoolExport'])->name('reports.export');

        // Payments & Balance Sheet
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/transactions', [PaymentController::class, 'transactions'])->name('payments.transactions');
        Route::post('/payments/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout')->middleware('throttle:5,1');
        Route::post('/payments/initiate', [PaymentController::class, 'initiate'])->name('payments.initiate')->middleware('throttle:5,1');
        Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

        // Attendance Report
        Route::get('/attendance', [AttendanceController::class, 'schoolAttendanceIndex'])->name('attendance.index');

        // Results
        Route::get('/results', [ResultController::class, 'schoolIndex'])->name('results.index');
        Route::get('/results/{student}/marksheet', [ResultController::class, 'schoolMarksheet'])->name('results.marksheet');
    });

    // ============================================
    // INVIGILATOR ROUTES
    // ============================================
    Route::middleware('role:invigilator')->prefix('invigilator')->name('invigilator.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    // ============================================
    // ATTENDANCE & SCANNER ROUTES (Invigilator / Super Admin)
    // ============================================
    Route::middleware('role:invigilator|super-admin')->group(function () {
        Route::get('/attendance/scanner', [AttendanceController::class, 'scanner'])->name('attendance.scanner');
        Route::post('/attendance/verify-scan', [AttendanceController::class, 'verifyScan'])->name('attendance.verify-scan');
        Route::post('/attendance/mark-present', [AttendanceController::class, 'markPresent'])->name('attendance.mark-present');
        Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
        Route::get('/attendance/count', [AttendanceController::class, 'scanCount'])->name('attendance.count'); // F1: Lightweight counter API
    });
});