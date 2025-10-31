<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * GET /auth/google
     * Redirect to Google OAuth login
     */
    public function googleLogin(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $authUrl = $this->googleAuthService->getAuthUrl();
        return redirect($authUrl);
    }

    /**
     * GET /auth/google/callback
     * Google OAuth callback endpoint
     */
    public function googleCallback(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $code = $request->query('code');
        $state = $request->query('state');

        if (!$code) {
            return redirect(config('app.frontend_url') . '?error=no_code');
        }

        $user = $this->googleAuthService->handleCallback($code);

        if (!$user) {
            return redirect(config('app.frontend_url') . '?error=auth_failed');
        }

        // Create API token
        $token = $user->createToken('api-token')->plainTextToken;

        // Redirect to frontend with user_id and token
        $redirectUrl = config('app.frontend_url') 
            . '?user_id=' . $user->id 
            . '&token=' . $token;

        return redirect($redirectUrl);
    }

    /**
     * POST /api/v1/auth/verify-token
     * Verify Google ID Token (for SPA)
     */
    public function verifyToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => 'required|string',
        ]);

        $user = $this->googleAuthService->verifyIdToken($validated['id_token']);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token',
            ], 401);
        }

        // Create API token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Token verified',
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'picture_url' => $user->picture_url,
                'token' => $token,
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}