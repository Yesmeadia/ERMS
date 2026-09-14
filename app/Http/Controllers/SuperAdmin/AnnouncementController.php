<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements with reads list for Super Admin.
     */
    public function index(): View
    {
        $announcements = Announcement::with(['creator', 'users.school'])
            ->withCount('users as reads_count')
            ->latest()
            ->paginate(15);

        return view('super-admin.announcements.index', compact('announcements'));
    }

    /**
     * Store a newly created announcement for School Admins.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Announcement::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created and published successfully to all active School Admins.');
    }

    /**
     * Toggle the active status of an announcement.
     */
    public function toggleStatus(Announcement $announcement): RedirectResponse
    {
        $announcement->update([
            'is_active' => ! $announcement->is_active,
        ]);

        $status = $announcement->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "Announcement {$status} successfully.");
    }

    /**
     * Delete an announcement.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->back()->with('success', 'Announcement deleted successfully.');
    }

    /**
     * Check for the latest active unread announcement for the current user/school.
     */
    public function checkUnread(): JsonResponse
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json(['announcement' => null]);
        }

        $userId = $user->id;

        $unreadAnnouncement = Announcement::where('is_active', true)
            ->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('announcement_user')
                    ->whereColumn('announcement_user.announcement_id', 'announcements.id')
                    ->where('announcement_user.user_id', $userId);
            })
            ->latest()
            ->first();

        if (! $unreadAnnouncement) {
            return response()->json(['announcement' => null]);
        }

        return response()->json([
            'announcement' => [
                'id' => $unreadAnnouncement->id,
                'title' => $unreadAnnouncement->title,
                'message' => $unreadAnnouncement->message,
                'created_at' => $unreadAnnouncement->created_at ? $unreadAnnouncement->created_at->toIso8601String() : null,
            ],
        ]);
    }

    /**
     * Mark an announcement as read for the current user and their school so it never shows again.
     */
    public function markAsRead(Request $request, $id = null): JsonResponse
    {
        $user = auth()->user();

        $announcementId = 0;
        if (is_object($id)) {
            $announcementId = $id->id;
        } elseif (is_numeric($id) && (int) $id > 0) {
            $announcementId = (int) $id;
        } else {
            $announcementId = (int) $request->input('announcement_id');
        }

        if (! $user) {
            Log::warning('Announcement markAsRead attempted without authenticated session.');

            return response()->json(['success' => false, 'message' => 'Unauthenticated session.'], 401);
        }

        if ($announcementId > 0) {
            $userIds = [$user->id];

            // If user belongs to a school, mark read for all users associated with that school
            if (! empty($user->school_id)) {
                $schoolUserIds = DB::table('users')->where('school_id', $user->school_id)->pluck('id')->toArray();
                $userIds = array_unique(array_merge($userIds, $schoolUserIds));
            }

            foreach ($userIds as $uid) {
                try {
                    DB::table('announcement_user')->upsert(
                        [
                            'announcement_id' => $announcementId,
                            'user_id' => $uid,
                            'read_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        ['announcement_id', 'user_id'],
                        ['read_at', 'updated_at']
                    );
                } catch (\Throwable $e) {
                    // Fallback to update if record already exists
                    DB::table('announcement_user')
                        ->where('announcement_id', $announcementId)
                        ->where('user_id', $uid)
                        ->update(['read_at' => now(), 'updated_at' => now()]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement marked as read for this school.',
                'announcement_id' => $announcementId,
                'count' => count($userIds),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid announcement ID.'], 422);
    }
}
