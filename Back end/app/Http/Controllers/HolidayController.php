<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HolidayController extends Controller
{

    public function index()
    {
        $holidays = Holiday::all();
        return response()->json($holidays);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
        ]);

        $holiday = Holiday::create($validated);

        return response()->json([
            'message' => 'Holiday created successfully.',
            'data' => $holiday
        ], 201);
    }


    public function show(Holiday $holiday)
    {
        return response()->json($holiday);
    }


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


    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return response()->json([
            'message' => 'Holiday deleted successfully.'
        ]);
    }
}