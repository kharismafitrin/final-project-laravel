<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
// use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'address' => $request->address,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Register Successfully',
            'data' => $user,
        ], 201);
    }

    // Login user and return the token
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        $credential = request(['email', 'password']);
        if (!$token = auth()->attempt($credential)) {
            return response()->json(['error' => 'User not Registered'], 401);
        }
        return response()->json([
            'status' => 'Success',
            'message' => 'Login Successful',
            'data' => auth()->user(),
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60,
        ], 200);
    }

    public function profile()
    {
        return response()->json([
            'status' => 'Success',
            'message' => 'User profile retrieved successfully',
            'data' => auth()->user()
        ], 200);
    }

    public function logout()
    {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());
        auth()->logout();
        if ($removeToken) {
            return response()->json([
                'status' => 'success',
                'massage' => 'User logged out successfully'
            ], 200);
        }
    }
}
