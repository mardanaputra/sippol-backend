<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle admin login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            /** @var User $user */
            $user = Auth::user();
            // Create Sanctum token
            $token = $user->createToken('admin-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau Password yang Anda masukkan salah.'
        ], 401);
    }

    /**
     * Handle admin logout request.
     */
    public function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        if ($user) {
            // Revoke current token
            $user->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak terotentikasi.'
        ], 401);
    }

    /**
     * Get authenticated user info.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
            ]
        ], 200);
    }
}
