<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use DateTimeZone;

class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        // Get user
        $user = $request->user();

        // Get attendance today
        $attendance = $user->attendances()->where('created_at', '>=', Carbon::now()->startOfDay())->first();
        if ($attendance != null) {
            return response()->json([
                'message' => 'You have already clocked in today'
            ], 400);
        }

        // Create new attendance 
        $user->attendances()->create([
            'clock_in' => Carbon::now(new DateTimeZone('Asia/Singapore'))
        ]);

        return response()->json([
            'message' => 'You have clocked in successfully'
        ]);
    }

    public function clockOut(Request $request)
    {
        // Get user
        $user = $request->user();

        // Get attendance today
        $attendance = $user->attendances()->where('created_at', '>=', Carbon::now()->startOfDay())->first();
        if ($attendance != null && $attendance->clock_out !== null) {
            return response()->json([
                'message' => 'You have already clocked out today'
            ], 400);
        }

        // Update clock out
        $attendance->update([
            'clock_out' => Carbon::now(new DateTimeZone('Asia/Singapore')),
        ]);
        return response()->json([
            'message' => 'You have clocked out successfully'
        ]);
    }

    public function getAttendanceOverview(Request $request)
    {
        // Get user
        $user = $request->user();

        // Previous attendance
        $previousAttendances = [];

        // Take latest 7 only
        $attendances = $user->attendances()->get()->sortByDesc('clock_in')->take(7);
        foreach ($attendances as $attendance) {
            $previousAttendances['labels'][] = Carbon::parse($attendance->clock_in)->rawFormat('M d');
            $previousAttendances['data'][] = Carbon::parse($attendance->clock_in)->diffInHours(Carbon::parse($attendance->clock_out));
        }

        return response()->json([
            'previousAttendances' => $previousAttendances,
        ]);
    }
}
