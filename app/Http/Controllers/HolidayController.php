<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Models\Attendence;
use Carbon\Carbon;

class HolidayController extends Controller
{
    // Get all holidays
    public function index()
    {
        $holidays = Holiday::all();
        return response()->json([
            'message' => 'Holidays retrieved successfully.',
            'data' => $holidays
        ]);
    }

    // Store a new holiday
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string',
        ]);

        // Convert date to date-only format (Y-m-d)
        $date = Carbon::parse($request->date)->toDateString();

        // Check if any attendance record exists on this date
        $attendanceExists = Attendence::whereDate('date', $date)->exists();

        if ($attendanceExists) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot register this day as a holiday because attendance has already been recorded for it.'
            ], 400);
        }

        // No attendance recorded, proceed to create holiday
        $holiday = Holiday::create([
            'date' => $date,
            'name' => $request->name,
        ]);

        return response()->json($holiday, 201);
    }

    // Show a specific holiday
    public function show(Holiday $holiday)
    {
        return response()->json([
            'message' => 'Holiday retrieved successfully.',
            'data' => $holiday
        ]);
    }
    
    

    // Update a holiday
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
        ]);

        $holiday->update($validated);

        return response()->json([
            'message' => 'Holiday updated successfully.',
            'data' => $holiday
        ]);
    }

    // Delete a holiday
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return response()->json([
            'message' => 'Holiday deleted successfully.'
        ]);
    }
}
