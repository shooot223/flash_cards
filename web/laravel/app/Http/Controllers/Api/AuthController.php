<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードが正しくありません。',
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('quiz-api-token')->plainTextToken;

        return response()->json([
            'message' => 'ログインしました。',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'ログアウトしました。',
        ]);
    }
}
