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
     * MUST match Go backend behavior: redirect to /login with Google access token and user_id
     * NOTE: Token is Google's access token (temporary, expires in minutes), NOT stored in DB
     */
    public function googleCallback(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $code = $request->query('code');
        $state = $request->query('state');

        if (!$code) {
            return redirect(config('app.frontend_url') . '/login?error=no_code');
        }

        // handleCallback now returns both user and Google token
        $result = $this->googleAuthService->handleCallback($code);

        if (!$result || !isset($result['user']) || !isset($result['token'])) {
            return redirect(config('app.frontend_url') . '/login?error=auth_failed');
        }

        $user = $result['user'];
        $googleToken = $result['token']; // Google's access token

        // IMPORTANT: Redirect to /login (not /vault) with Google token and user_id
        // Token is Google's access token (temporary), matching Go backend
        // Frontend login page will read user_id from URL and store in localStorage
        $frontendUrl = rtrim(config('app.frontend_url'), '/') . '/login';
        $redirectUrl = $frontendUrl . '?token=' . urlencode($googleToken) . '&user_id=' . urlencode($user->id);

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