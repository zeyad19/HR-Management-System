<?php

namespace App\Http\Controllers;

use App\Models\HR;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class HRController extends Controller
{
    // Register HR with profile picture upload
    public function register(Request $request)
    {
        // Validate input data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:hrs,email',
            'password' => 'required|string|min:6',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Upload profile picture if provided
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('hrs/profile_pictures', 'public');
            $validatedData['profile_picture'] = $path;
        }

        // Create new HR record with hashed password
        $hr = HR::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'profile_picture' => $validatedData['profile_picture'] ?? null,
        ]);

        // Create API token for the HR user
        $token = $hr->createToken('hr-token')->plainTextToken;

        return response()->json([
            'message' => 'HR registered successfully',
            'token' => $token,
            'hr' => $hr
        ], 201);
    }

    // HR login method
    public function login(Request $request)
    {
        // Validate login credentials
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find HR by email
        $hr = HR::where('email', $validatedData['email'])->first();

        // Check if HR exists and password matches
        if (!$hr || !Hash::check($validatedData['password'], $hr->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate new API token on login
        $token = $hr->createToken('hr-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'hr' => $hr
        ]);
    }

    // HR logout method - revoke all tokens
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    // Update HR details with optional new profile picture upload
    public function update(Request $request, $id)
    {
        $hr = HR::findOrFail($id);

        // Validate update data - fields are optional but if present must follow rules
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:hrs,email,' . $id,
            'password' => 'nullable|string|min:6',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // If new profile picture uploaded, delete old one and store new
        if ($request->hasFile('profile_picture')) {
            if ($hr->profile_picture) {
                Storage::disk('public')->delete($hr->profile_picture);
            }
            $path = $request->file('profile_picture')->store('hrs/profile_pictures', 'public');
            $validatedData['profile_picture'] = $path;
        }

        // Hash password if provided, else remove it from update array
        if (!empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        // Update HR record
        $hr->update($validatedData);

        return response()->json([
            'message' => 'HR updated successfully',
            'hr' => $hr
        ]);
    }
}
