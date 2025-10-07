<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function checkEmailExists(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6', // برای تایید پسورد دوتا فیلد: password و password_confirmation باید باشه
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // اگر از sanctum یا passport استفاده می‌کنی:
         $token = $user->createToken('auth_token')->plainTextToken;




        return response()->json([
            'message' => 'User registered successfully!',
             'token' => $token, // در صورت نیاز
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        $user = Auth::user();
        // اگر از sanctum یا passport استفاده می‌کنی:
         $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
             'token' => $token, // در صورت نیاز
            'user' => $user
        ]);
    }
}


