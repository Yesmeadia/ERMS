<?php

namespace App\Http\Controllers;

use App\Models\OnlineExamSession;
use App\Services\ProctoringRecordingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnlineExamProctoringController extends Controller
{
    public function __construct(
        protected ProctoringRecordingService $recordingService
    ) {}

    /**
     * Upload and store a video recording chunk from the student client.
     */
    public function recordChunk(Request $request): JsonResponse
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        $request->validate([
            'video_chunk' => 'required|file|max:20480', // max 20MB per chunk (safely handles 15-30s clips even at high res)
            'chunk_index' => 'required|integer|min:0',
            'duration' => 'nullable|numeric|min:0',
            'is_final' => 'nullable|boolean',
        ]);

        try {
            $chunkIndex = (int) $request->input('chunk_index', 0);
            $duration = (float) $request->input('duration', 0.0);
            $isFinal = (bool) $request->boolean('is_final', false);

            $recording = $this->recordingService->storeChunk(
                $session,
                $request->file('video_chunk'),
                $chunkIndex,
                $duration,
                $isFinal
            );

            return response()->json([
                'success' => true,
                'recording_id' => $recording->id,
                'chunk_index' => $recording->chunk_index,
                'sha256' => $recording->sha256_hash,
                'file_size' => $recording->file_size_bytes,
            ]);
        } catch (\Throwable $e) {
            Log::error('[Proctoring Recording Error]', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process proctoring video chunk.',
            ], 500);
        }
    }

    /**
     * Upload a lightweight camera snapshot for the admin live monitoring grid.
     */
    public function snapshot(Request $request): JsonResponse
    {
        /** @var OnlineExamSession $session */
        $session = $request->attributes->get('exam_session');

        $request->validate([
            'snapshot' => 'required|string|max:500000', // max ~350KB base64 string
        ]);

        try {
            $this->recordingService->storeSnapshot(
                $session,
                $request->input('snapshot')
            );

            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('[Proctoring Snapshot Error]', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process snapshot.',
            ], 500);
        }
    }
}
