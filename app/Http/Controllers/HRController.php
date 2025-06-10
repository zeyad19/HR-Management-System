<?php

namespace App\Http\Controllers;
use App\Models\HR;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;


use Illuminate\Http\Request;

class HRController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:hrs',
            'password' => 'required|min:6'
        ]);

        $hr = HR::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $hr->createToken('hr-token')->plainTextToken;

        return response()->json(['token' => $token], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $hr = HR::where('email', $request->email)->first();

        if (!$hr || !Hash::check($request->password, $hr->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $hr->createToken('hr-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

   
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    
}
