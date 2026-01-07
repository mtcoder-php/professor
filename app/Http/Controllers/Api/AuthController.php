<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'passport_series' => 'required|string',
            'password' => 'required|string'
        ]);

        $passportSeries = strtoupper($request->passport_series);
        $user = User::where('passport_series', $passportSeries)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Passport yoki parol noto\'g\'ri'
            ], 401);
        }

        // Load roles
        $user->load('roles');

        // Token yaratish
        $token = $user->createToken('auth_token')->plainTextToken;

        // Role aniqlash
        $role = 'admin'; // default
        if ($user->roles->isNotEmpty()) {
            $roleName = $user->roles->first()->name;
            $role = strtolower($roleName); // admin, prorektor, teacher
        }

        return response()->json([
            'user' => $user,
            'token' => $token,
            'role' => $role // ← Frontend uchun
        ]);
    }

    public function user(Request $request)
    {
        // Load roles relationship
        $user = $request->user();
        $user->load('roles');

        return response()->json([
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Muvaffaqiyatli chiqildi'
        ]);
    }
}
