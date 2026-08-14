<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Exchange email/password credentials for a JWT access token.
     */
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->tokenResponse($token);
    }

    /**
     * Return the currently authenticated user.
     */
    public function show(): JsonResponse
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * Exchange a still-valid (or within grace period) token for a new one.
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = Auth::guard('api')->refresh();
        } catch (JWTException) {
            throw ValidationException::withMessages([
                'token' => ['The token could not be refreshed.'],
            ]);
        }

        return $this->tokenResponse($token);
    }

    /**
     * Invalidate the token used to authenticate the current request.
     */
    public function destroy(): Response
    {
        Auth::guard('api')->logout();

        return response()->noContent();
    }

    private function tokenResponse(string $token): JsonResponse
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}
