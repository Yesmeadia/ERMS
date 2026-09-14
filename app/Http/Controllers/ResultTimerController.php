<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ResultTimerController extends Controller
{
    /**
     * Show the result timer management page.
     */
    public function index()
    {
        $releaseUtc = AppSetting::resultReleaseDatetime();

        // Convert to IST for display
        $releaseIst = $releaseUtc->copy()->setTimezone('Asia/Kolkata');

        $released = AppSetting::resultsReleased();

        // ISO-8601 string in UTC for the JS countdown
        $releaseIso = $releaseUtc->toIso8601String();

        return view('super-admin.result-timer.index', compact(
            'releaseUtc',
            'releaseIst',
            'released',
            'releaseIso',
        ));
    }

    /**
     * Update the result release datetime.
     * The form sends an IST datetime-local string; we convert to UTC for storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'release_datetime_ist' => ['required', 'string'],
        ]);

        // datetime-local input gives "YYYY-MM-DDTHH:MM" in IST
        $ist = Carbon::createFromFormat('Y-m-d\TH:i', $request->release_datetime_ist, 'Asia/Kolkata');
        $utc = $ist->setTimezone('UTC');

        AppSetting::set('result_release_datetime', $utc->format('Y-m-d H:i:s'));

        activity()->causedBy(auth()->user())->log(
            'Super Admin updated result release time to ' . $ist->setTimezone('Asia/Kolkata')->format('d/m/Y h:i A') . ' IST'
        );

        return back()->with('success', 'Result release time updated to ' . $ist->setTimezone('Asia/Kolkata')->format('d/m/Y h:i A') . ' IST.');
    }

    /**
     * Force-release results immediately.
     */
    public function forceRelease(Request $request)
    {
        AppSetting::set('result_release_datetime', now('UTC')->subMinute()->format('Y-m-d H:i:s'));

        activity()->causedBy(auth()->user())->log('Super Admin force-released results immediately.');

        return back()->with('success', 'Results have been force-released. The countdown is now expired.');
    }

    /**
     * JSON status endpoint for polling/AJAX checks.
     */
    public function status()
    {
        $releaseUtc = AppSetting::resultReleaseDatetime();

        return response()->json([
            'release_iso'     => $releaseUtc->toIso8601String(),
            'release_ist'     => $releaseUtc->copy()->setTimezone('Asia/Kolkata')->format('d/m/Y h:i A T'),
            'released'        => AppSetting::resultsReleased(),
            'server_time_utc' => now('UTC')->toIso8601String(),
        ]);
    }
}
