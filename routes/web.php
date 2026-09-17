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
use App\Http\Controllers\ResultTimerController;
use App\Models\AppSetting;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExamCentreController;
use App\Http\Controllers\SuperAdmin\AnnouncementController;
use App\Http\Controllers\StudentOnlineExamController;
use App\Http\Controllers\Admin\OnlineExamDashboardController;
use App\Http\Controllers\Admin\OnlineExamController;
use App\Http\Controllers\Admin\OnlineQuestionBankController;
use App\Http\Controllers\Admin\OnlineExamQuestionController;
use App\Http\Controllers\Admin\OnlineExamStudentController;
use App\Http\Controllers\Admin\OnlineExamLiveMonitoringController;
use App\Http\Controllers\Admin\OnlineExamReportController;
use App\Http\Controllers\OnlineExamWebRTCController;
use App\Http\Controllers\OnlineExamProctoringController;


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

    $releaseUtc = AppSetting::resultReleaseDatetime();
    $released   = AppSetting::resultsReleased();
    $releaseIso = $releaseUtc->toIso8601String();
    $releaseIst = $releaseUtc->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A');

    return view('welcome', compact('activeExam', 'winners', 'releaseUtc', 'released', 'releaseIso', 'releaseIst'));
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

// Public Legal Pages
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/terms-and-conditions', function () {
    return view('terms-and-conditions');
})->name('terms-and-conditions');

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

// ============================================
// ONLINE EXAMINATION - STUDENT PORTAL
// ============================================
Route::prefix('online-exam')->name('online-exam.')->group(function () {
    Route::get('/login', [StudentOnlineExamController::class, 'showLogin'])->name('login');
    Route::post('/login', [StudentOnlineExamController::class, 'login'])->name('login.submit')->middleware('throttle:15,1');
    Route::get('/terminated', [StudentOnlineExamController::class, 'terminated'])->name('terminated');

    // Protected by online exam session token
    Route::middleware(\App\Http\Middleware\OnlineExamSessionMiddleware::class)->group(function () {
        Route::get('/instructions', [StudentOnlineExamController::class, 'instructions'])->name('instructions');
        Route::post('/start', [StudentOnlineExamController::class, 'startExam'])->name('start')->middleware('throttle:30,1');
        Route::get('/take', [StudentOnlineExamController::class, 'take'])->name('take');
        Route::post('/submit-answer', [StudentOnlineExamController::class, 'submitAnswer'])->name('submit-answer')->middleware('throttle:60,1');
        Route::post('/next-question', [StudentOnlineExamController::class, 'nextQuestion'])->name('next-question')->middleware('throttle:60,1');
        Route::post('/previous-question', [StudentOnlineExamController::class, 'previousQuestion'])->name('previous-question')->middleware('throttle:60,1');
        Route::post('/heartbeat', [StudentOnlineExamController::class, 'heartbeat'])->name('heartbeat')->middleware('throttle:60,1');
        Route::post('/event', [StudentOnlineExamController::class, 'recordEvent'])->name('event')->middleware('throttle:60,1');
        Route::match(['get', 'post'], '/finish', [StudentOnlineExamController::class, 'finish'])->name('finish');
        Route::get('/result', [StudentOnlineExamController::class, 'result'])->name('result');

        // WebRTC Signaling
        Route::get('/webrtc/signals', [OnlineExamWebRTCController::class, 'getStudentSignals'])->name('webrtc.signals')->middleware('throttle:120,1');
        Route::post('/webrtc/signal', [OnlineExamWebRTCController::class, 'sendStudentSignal'])->name('webrtc.signal')->middleware('throttle:120,1');

        // Proctoring Video Recording & Live Snapshots
        Route::post('/proctoring/record-chunk', [OnlineExamProctoringController::class, 'recordChunk'])->name('proctoring.record-chunk')->middleware('throttle:30,1');
        Route::post('/proctoring/snapshot', [OnlineExamProctoringController::class, 'snapshot'])->name('proctoring.snapshot')->middleware('throttle:60,1');
    });
});

// 3. SECURE AUTHENTICATED ROUTES
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.update');

    // Global Announcement Dismissal & Polling routes (accessible to any logged-in user)
    Route::get('/announcements/check-unread', [AnnouncementController::class, 'checkUnread'])->name('announcements.check-unread-global');
    Route::post('/announcements/read-direct', [AnnouncementController::class, 'markAsRead'])->name('announcements.read-direct');
    Route::post('/announcements/{id}/read', [AnnouncementController::class, 'markAsRead'])->name('announcements.read-global');

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
        Route::get('/staff/export-pdf', [StaffController::class, 'exportPdf'])->name('staff.export-pdf');
        Route::post('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
        Route::post('/staff/{staff}/reset-password', [StaffController::class, 'sendResetLink'])->name('staff.reset-password');
        Route::resource('staff', StaffController::class);

        // Announcement Management (Broadcasts)
        Route::post('/announcements/{announcement}/toggle', [AnnouncementController::class, 'toggleStatus'])->name('announcements.toggle');
        Route::resource('announcements', AnnouncementController::class)->only(['index', 'store', 'destroy']);

        // Super Admin Management
        Route::resource('admins', SuperAdminController::class)->except(['show']);

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Schools Management
        Route::post('/schools/{school}/toggle-status', [SchoolController::class, 'toggleStatus'])->name('schools.toggle-status');
        Route::post('/schools/{school}/toggle-fine', [SchoolController::class, 'toggleFine'])->name('schools.toggle-fine');
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
        Route::match(['get', 'post'], '/hall-tickets/print-bulk', [HallTicketController::class, 'printBulk'])->name('hall-tickets.print-bulk');
        Route::get('/hall-tickets/batches/{batch}', [HallTicketController::class, 'showAdminBatch'])->name('hall-tickets.batches.show');
        Route::get('/hall-tickets/batches/{batch}/status', [HallTicketController::class, 'batchStatus'])->name('hall-tickets.batches.status');
        Route::post('/hall-tickets/batches/{batch}/retry', [HallTicketController::class, 'retryBatch'])->name('hall-tickets.batches.retry');
        Route::get('/hall-tickets/parts/{part}/download', [HallTicketController::class, 'downloadPart'])->name('hall-tickets.parts.download');

        // Exam Centres Management (Super Admin Actions)
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
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'adminReceipt'])->name('payments.receipt');

        // Attendance Management
        Route::get('/attendance', [AttendanceController::class, 'adminAttendanceIndex'])->name('attendance.index');
        Route::post('/attendance/mark', [AttendanceController::class, 'adminAttendanceMark'])->name('attendance.mark');

        // Results Management
        Route::get('/results/import/template', [ResultController::class, 'downloadTemplate'])->name('results.import.template');
        Route::get('/results/import', [ResultController::class, 'showImportForm'])->name('results.import-form');
        Route::post('/results/import', [ResultController::class, 'import'])->name('results.import');
        Route::match(['get', 'post'], '/results/pdf', [ResultController::class, 'adminExportPdf'])->name('results.pdf');
        Route::get('/results/batches/{batch}', [ResultController::class, 'showAdminBatch'])->name('results.batches.show');
        Route::get('/results/batches/{batch}/status', [ResultController::class, 'batchStatus'])->name('results.batches.status');
        Route::post('/results/batches/{batch}/retry', [ResultController::class, 'retryBatch'])->name('results.batches.retry');
        Route::get('/results/parts/{part}/download', [ResultController::class, 'downloadPart'])->name('results.parts.download');
        Route::get('/results', [ResultController::class, 'adminIndex'])->name('results.index');
        Route::get('/results/create/{student}', [ResultController::class, 'create'])->name('results.create');
        Route::post('/results', [ResultController::class, 'store'])->name('results.store');
        Route::get('/results/{result}/edit', [ResultController::class, 'edit'])->name('results.edit');
        Route::put('/results/{result}', [ResultController::class, 'update'])->name('results.update');
        Route::delete('/results/{result}', [ResultController::class, 'destroy'])->name('results.destroy');

        // Result Timer Management
        Route::get('/result-timer', [ResultTimerController::class, 'index'])->name('result-timer.index');
        Route::post('/result-timer', [ResultTimerController::class, 'update'])->name('result-timer.update');
        Route::post('/result-timer/force-release', [ResultTimerController::class, 'forceRelease'])->name('result-timer.force-release');
        Route::get('/result-timer/status', [ResultTimerController::class, 'status'])->name('result-timer.status');
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
        Route::match(['get', 'post'], '/hall-tickets/download-bulk', [HallTicketController::class, 'downloadBulk'])->name('hall-tickets.download-bulk');
        Route::get('/hall-tickets/batches/{batch}', [HallTicketController::class, 'showSchoolBatch'])->name('hall-tickets.batches.show');
        Route::get('/hall-tickets/batches/{batch}/status', [HallTicketController::class, 'batchStatus'])->name('hall-tickets.batches.status');
        Route::post('/hall-tickets/batches/{batch}/retry', [HallTicketController::class, 'retryBatch'])->name('hall-tickets.batches.retry');
        Route::get('/hall-tickets/parts/{part}/download', [HallTicketController::class, 'downloadPart'])->name('hall-tickets.parts.download');

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
        Route::match(['get', 'post'], '/results/pdf', [ResultController::class, 'schoolExportPdf'])->name('results.pdf');
        Route::get('/results/batches/{batch}', [ResultController::class, 'showSchoolBatch'])->name('results.batches.show');
        Route::get('/results/batches/{batch}/status', [ResultController::class, 'batchStatus'])->name('results.batches.status');
        Route::post('/results/batches/{batch}/retry', [ResultController::class, 'retryBatch'])->name('results.batches.retry');
        Route::get('/results/parts/{part}/download', [ResultController::class, 'downloadPart'])->name('results.parts.download');
        Route::get('/results', [ResultController::class, 'schoolIndex'])->name('results.index');
        Route::get('/results/{student}/marksheet', [ResultController::class, 'schoolMarksheet'])->name('results.marksheet');

        // Exam Centre Venue Management & Seat Planner (if school is a designated centre)
        Route::get('/exam-centre', [ExamCentreController::class, 'schoolShow'])->name('exam-centre.show');
        Route::get('/exam-centre/students-pdf', [ExamCentreController::class, 'schoolStudentsPdf'])->name('exam-centre.students-pdf');
        Route::get('/exam-centre/seat-planner-pdf', [ExamCentreController::class, 'schoolSeatPlannerPdf'])->name('exam-centre.seat-planner-pdf');

        // Announcement Dismissal & Polling
        Route::get('/announcements/check-unread', [AnnouncementController::class, 'checkUnread'])->name('announcements.check-unread');
        Route::post('/announcements/{id}/read', [AnnouncementController::class, 'markAsRead'])->name('announcements.read');
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
        Route::get('/attendance/count', [AttendanceController::class, 'scanCount'])->name('attendance.count');
    });

    // ============================================
    // EXAM CENTRE VENUE & SEAT PLANNER (Super Admin & School Admin)
    // ============================================
    Route::middleware('role:super-admin|school-admin')->group(function () {
        Route::get('/admin/exam-centres/{school}', [ExamCentreController::class, 'show'])->name('admin.exam-centres.show');
        Route::get('/admin/exam-centres/{school}/students-pdf', [ExamCentreController::class, 'downloadStudentsPdf'])->name('admin.exam-centres.students-pdf');
        Route::get('/admin/exam-centres/{school}/seat-planner-pdf', [ExamCentreController::class, 'downloadSeatPlannerPdf'])->name('admin.exam-centres.seat-planner-pdf');
    });

    // ============================================
    // ONLINE EXAMINATION - ADMIN MANAGEMENT (Super Admin & Exam Admin)
    // ============================================
    Route::middleware('role:super-admin|exam-admin')->group(function () {
        // Question Bank
        Route::prefix('admin/online-questions')->name('admin.online-questions.')->group(function () {
            Route::get('/', [OnlineQuestionBankController::class, 'index'])->name('index');
            Route::get('/create', [OnlineQuestionBankController::class, 'create'])->name('create');
            Route::post('/', [OnlineQuestionBankController::class, 'store'])->name('store');
            Route::get('/{online_question}/edit', [OnlineQuestionBankController::class, 'edit'])->name('edit');
            Route::put('/{online_question}', [OnlineQuestionBankController::class, 'update'])->name('update');
            Route::delete('/{online_question}', [OnlineQuestionBankController::class, 'destroy'])->name('destroy');
            Route::post('/{online_question}/toggle-status', [OnlineQuestionBankController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Online Exams
        Route::prefix('admin/online-exams')->name('admin.online-exams.')->group(function () {
            Route::get('/dashboard', [OnlineExamDashboardController::class, 'index'])->name('dashboard');

            // Exam CRUD
            Route::get('/', [OnlineExamController::class, 'index'])->name('index');
            Route::get('/create', [OnlineExamController::class, 'create'])->name('create');
            Route::post('/', [OnlineExamController::class, 'store'])->name('store');
            Route::get('/{online_exam}', [OnlineExamController::class, 'show'])->name('show');
            Route::get('/{online_exam}/edit', [OnlineExamController::class, 'edit'])->name('edit');
            Route::put('/{online_exam}', [OnlineExamController::class, 'update'])->name('update');
            Route::delete('/{online_exam}', [OnlineExamController::class, 'destroy'])->name('destroy');
            Route::post('/{online_exam}/publish', [OnlineExamController::class, 'publish'])->name('publish');
            Route::post('/{online_exam}/unpublish', [OnlineExamController::class, 'unpublish'])->name('unpublish');
            Route::get('/{online_exam}/preview', [OnlineExamController::class, 'preview'])->name('preview');

            // Question Assignment
            Route::get('/{online_exam}/questions', [OnlineExamQuestionController::class, 'index'])->name('questions.index');
            Route::post('/{online_exam}/questions/assign', [OnlineExamQuestionController::class, 'assign'])->name('questions.assign');
            Route::post('/{online_exam}/questions/settings', [OnlineExamQuestionController::class, 'updateSettings'])->name('questions.update-settings');
            Route::delete('/{online_exam}/questions/{question}', [OnlineExamQuestionController::class, 'remove'])->name('questions.remove');

            // Student Enrollment
            Route::get('/{online_exam}/students', [OnlineExamStudentController::class, 'index'])->name('students.index');
            Route::post('/{online_exam}/students/enroll', [OnlineExamStudentController::class, 'enroll'])->name('students.enroll');
            Route::delete('/{online_exam}/students/{student}', [OnlineExamStudentController::class, 'remove'])->name('students.remove');

            // Live Proctoring
            Route::get('/{online_exam}/live', [OnlineExamLiveMonitoringController::class, 'show'])->name('live');
            Route::get('/{online_exam}/live/poll', [OnlineExamLiveMonitoringController::class, 'poll'])->name('live.poll');
            Route::post('/{online_exam}/live/terminate/{session}', [OnlineExamLiveMonitoringController::class, 'terminateSession'])->name('live.terminate');
            Route::get('/{online_exam}/live/events/{session}', [OnlineExamLiveMonitoringController::class, 'sessionEvents'])->name('live.events');
            Route::post('/{online_exam}/live/webrtc/{session}/signal', [OnlineExamWebRTCController::class, 'sendAdminSignal'])->name('live.webrtc.signal');
            Route::get('/{online_exam}/live/webrtc/{session}/signals', [OnlineExamWebRTCController::class, 'getAdminSignals'])->name('live.webrtc.signals');
            Route::get('/{online_exam}/live/snapshot/{session}', [OnlineExamLiveMonitoringController::class, 'streamSnapshot'])->name('live.snapshot');
            Route::get('/{online_exam}/live/recordings/{session}', [OnlineExamLiveMonitoringController::class, 'recordingsList'])->name('live.recordings');
            Route::get('/{online_exam}/live/recordings/{session}/{recording}', [OnlineExamLiveMonitoringController::class, 'streamRecording'])->name('live.recording.stream');

            // Results & Reports
            Route::get('/{online_exam}/results', [OnlineExamReportController::class, 'index'])->name('results');
            Route::post('/{online_exam}/results/recalculate', [OnlineExamReportController::class, 'recalculateRanks'])->name('results.recalculate');
            Route::get('/{online_exam}/results/export-csv', [OnlineExamReportController::class, 'exportCsv'])->name('results.csv');
            Route::get('/{online_exam}/results/export-pdf', [OnlineExamReportController::class, 'exportPdf'])->name('results.pdf');
        });
    });
});