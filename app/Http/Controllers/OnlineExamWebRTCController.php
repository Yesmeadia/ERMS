<?php

namespace App\Http\Controllers;

use App\Models\OnlineExam;
use App\Models\OnlineExamSession;
use App\Models\OnlineExamWebrtcSignal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnlineExamWebRTCController extends Controller
{
    /**
     * Admin sends a WebRTC signal (offer, candidate, close) to a student session.
     */
    public function sendAdminSignal(Request $request, OnlineExam $online_exam, OnlineExamSession $session)
    {
        if ($session->online_exam_id !== $online_exam->id) {
            return response()->json(['success' => false, 'message' => 'Session does not belong to this exam.'], 404);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:offer,candidate,close,request_stream'],
            'payload' => ['nullable'],
        ]);

        $adminId = Auth::id();

        // If it's a new offer or close, discard previous unconsumed signals of same type from this admin
        if (in_array($validated['type'], ['offer', 'close'])) {
            OnlineExamWebrtcSignal::where('online_exam_session_id', $session->id)
                ->where('admin_id', $adminId)
                ->where('type', $validated['type'])
                ->delete();
        }

        $signal = OnlineExamWebrtcSignal::create([
            'online_exam_session_id' => $session->id,
            'admin_id' => $adminId,
            'sender_type' => 'admin',
            'recipient_type' => 'student',
            'type' => $validated['type'],
            'payload' => $validated['payload'] ?? null,
            'is_consumed' => false,
        ]);

        // Auto-prune signals older than 2 minutes
        OnlineExamWebrtcSignal::pruneStale(2);

        return response()->json([
            'success' => true,
            'signal_id' => $signal->id,
        ]);
    }

    /**
     * Admin polls for WebRTC signals (answer, candidate, close) sent by student.
     */
    public function getAdminSignals(Request $request, OnlineExam $online_exam, OnlineExamSession $session)
    {
        if ($session->online_exam_id !== $online_exam->id) {
            return response()->json(['success' => false, 'message' => 'Session does not belong to this exam.'], 404);
        }

        $adminId = Auth::id();

        $query = OnlineExamWebrtcSignal::where('online_exam_session_id', $session->id)
            ->where('recipient_type', 'admin')
            ->where('is_consumed', false);

        if ($adminId) {
            $query->where(function ($q) use ($adminId) {
                $q->where('admin_id', $adminId)->orWhereNull('admin_id');
            });
        }

        $signals = $query->oldest('id')->get();

        if ($signals->isNotEmpty()) {
            OnlineExamWebrtcSignal::whereIn('id', $signals->pluck('id'))->update(['is_consumed' => true]);
        }

        return response()->json([
            'success' => true,
            'signals' => $signals->map(function ($s) {
                return [
                    'id' => $s->id,
                    'type' => $s->type,
                    'payload' => $s->payload,
                    'created_at' => $s->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Student polls for WebRTC signals (offer, candidate, close) sent by admin.
     */
    public function getStudentSignals(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'Active exam session required.'], 403);
        }

        $signals = OnlineExamWebrtcSignal::where('online_exam_session_id', $session->id)
            ->where('recipient_type', 'student')
            ->where('is_consumed', false)
            ->oldest('id')
            ->get();

        if ($signals->isNotEmpty()) {
            OnlineExamWebrtcSignal::whereIn('id', $signals->pluck('id'))->update(['is_consumed' => true]);
        }

        return response()->json([
            'success' => true,
            'signals' => $signals->map(function ($s) {
                return [
                    'id' => $s->id,
                    'admin_id' => $s->admin_id,
                    'type' => $s->type,
                    'payload' => $s->payload,
                    'created_at' => $s->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Student sends a WebRTC signal (answer, candidate, ready) back to admin.
     */
    public function sendStudentSignal(Request $request)
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        if (! $session) {
            return response()->json(['success' => false, 'message' => 'Active exam session required.'], 403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:answer,candidate,close,ready'],
            'admin_id' => ['nullable', 'integer'],
            'payload' => ['nullable'],
        ]);

        $targetAdminId = $validated['admin_id'] ?? null;
        if ($targetAdminId) {
            $isValidAdmin = User::where('id', $targetAdminId)->exists();

            if (! $isValidAdmin) {
                return response()->json(['success' => false, 'message' => 'Invalid recipient administrator.'], 403);
            }
        }

        $signal = OnlineExamWebrtcSignal::create([
            'online_exam_session_id' => $session->id,
            'admin_id' => $targetAdminId,
            'sender_type' => 'student',
            'recipient_type' => 'admin',
            'type' => $validated['type'],
            'payload' => $validated['payload'] ?? null,
            'is_consumed' => false,
        ]);

        return response()->json([
            'success' => true,
            'signal_id' => $signal->id,
        ]);
    }

    /**
     * Get configured ICE servers (STUN + optional TURN).
     * Delegates to IceServerService for unified provider management and credential caching.
     */
    public static function getIceServersConfig(): array
    {
        return app(\App\Services\WebRTC\IceServerService::class)->getIceServers();
    }

    /**
     * Dedicated secure endpoint for students to retrieve ICE configuration.
     * Protected by student online_exam_session middleware.
     */
    public function getStudentIceServers(Request $request)
    {
        return response()->json([
            'success' => true,
            'iceServers' => self::getIceServersConfig(),
        ]);
    }

    /**
     * Dedicated secure endpoint for admin live monitoring to retrieve ICE configuration.
     * Protected by auth and admin role middleware.
     */
    public function getAdminIceServers(Request $request, OnlineExam $online_exam)
    {
        return response()->json([
            'success' => true,
            'iceServers' => self::getIceServersConfig(),
        ]);
    }
}
