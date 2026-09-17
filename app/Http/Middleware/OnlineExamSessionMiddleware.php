<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OnlineExamSession;
use App\Enums\ExamSessionStatus;

class OnlineExamSessionMiddleware
{
    /**
     * Handle an incoming request for active student exam routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session()->get('online_exam_session_token') 
            ?: $request->header('X-Exam-Session-Token');

        if (!$token) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated examination session. Please log in.',
                ], 401);
            }

            return redirect()->route('online-exam.login')->withErrors([
                'registration_number' => 'Please log in to access your examination session.',
            ]);
        }

        $session = OnlineExamSession::with(['exam', 'student.school', 'student.category'])
            ->where('session_token', $token)
            ->first();

        if (!$session) {
            $request->session()->forget('online_exam_session_token');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired examination session.',
                ], 401);
            }

            return redirect()->route('online-exam.login')->withErrors([
                'registration_number' => 'Your examination session is invalid or has expired.',
            ]);
        }

        // Check if session has been terminated
        if ($session->status === ExamSessionStatus::TERMINATED) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $session->termination_reason ?: 'Your examination session has been terminated.',
                    'is_terminated' => true,
                ], 403);
            }

            return redirect()->route('online-exam.terminated');
        }

        // Check if session has already been submitted
        if ($session->status === ExamSessionStatus::SUBMITTED || $session->status === ExamSessionStatus::EXPIRED) {
            if ($request->routeIs('online-exam.result')) {
                // Allow viewing result
                $request->attributes->set('exam_session', $session);
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Examination already submitted.',
                    'is_submitted' => true,
                ], 403);
            }

            return redirect()->route('online-exam.result');
        }

        // Attach session to request for controllers
        $request->attributes->set('exam_session', $session);

        return $next($request);
    }
}
