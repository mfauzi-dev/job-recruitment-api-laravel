<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    
    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'curriculum_vitae_url' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $filename = 'curriculumVitae-' . $request->name . "-" . Str::random(5) . '.' . $request->file('curriculum_vitae_url')->getClientOriginalExtension();

        $user = User::create([
            'curriculum_vitae_url' => $request->file('curriculum_vitae_url')->storeAs(
                'assets/curriculum_vitae',
                $filename,
                'public'
            ),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_as' => 'user'
        ]);

        
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
            ]
        ], 201);
    }

    public function registerCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_as' => 'company'
        ]);

        
        return response()->json([
            'success' => true,
            'message' => 'Company registered successfully',
            'data' => [
                'user' => $user,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email dan Password anda salah.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'role' => $user->role_as,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        // Delete current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    public function logoutAll(Request $request)
    {
        // Delete all tokens (logout from all devices)
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices successfully'
        ], 200);
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }
}