<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Google\Client;
use Google\Exception;
use Illuminate\Support\Facades\Log;

class GoogleAuthService
{
    protected $userRepository;
    protected $googleClient;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->googleClient = new Client();
        $this->googleClient->setClientId(config('services.google.client_id'));
        $this->googleClient->setClientSecret(config('services.google.client_secret'));
        $this->googleClient->setRedirectUri(config('services.google.redirect_uri'));
        // Required scopes for Google Sign-In
        // Without these, Google returns: "Missing required parameter: scope"
        $this->googleClient->setScopes([
            'openid',
            'email',
            'profile',
        ]);
        // Recommended UX options
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setIncludeGrantedScopes(true);
        // Make sure user can choose account and consent when needed
        $this->googleClient->setPrompt('select_account consent');
    }

    /**
     * Get Google OAuth URL
     */
    public function getAuthUrl(): string
    {
        return $this->googleClient->createAuthUrl();
    }

    /**
     * Handle callback from Google and authenticate user
     * Returns array with user and Google access token (matches Go backend)
     * 
     * @return array{user: User, token: string}|null
     */
    public function handleCallback(string $code): ?array
    {
        try {
            $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);
            
            // Check if token fetch was successful
            if (isset($token['error'])) {
                Log::error('Google OAuth token error: ' . json_encode($token));
                return null;
            }
            
            if (empty($token['access_token'])) {
                Log::error('Google OAuth: No access token received');
                return null;
            }
            
            $this->googleClient->setAccessToken($token);

            $oauth = new \Google\Service\Oauth2($this->googleClient);
            $userInfo = $oauth->userinfo->get();

            // Prefer provider-based lookup (uuid for our users will be BE-generated)
            $providerId = $userInfo->id ?? null; // Google's 'sub'
            $providerName = 'google';
            $user = null;
            if ($providerId) {
                $user = $this->userRepository->findByProvider($providerId, $providerName);
            }
            if (!$user && !empty($userInfo->email)) {
                // Fallback by email only to avoid duplicates on first login
                $user = $this->userRepository->findByEmail($userInfo->email);
            }

            if (!$user) {
                $user = $this->userRepository->create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'email' => $userInfo->email,
                    'name' => $userInfo->name,
                    'picture_url' => $userInfo->picture,
                    'google_id' => $providerId,
                    'provider_id' => $providerId,
                    'provider_name' => $providerName,
                ]);
            } else {
                // Update user info if needed
                $this->userRepository->update($user, [
                    'name' => $userInfo->name,
                    'picture_url' => $userInfo->picture,
                    'provider_id' => $providerId,
                    'provider_name' => $providerName,
                ]);
            }

            // Return both user and Google's access token (matches Go backend)
            return [
                'user' => $user,
                'token' => $token['access_token'], // Google's access token (temporary)
            ];
        } catch (Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify Google ID Token (alternative method for SPA)
     */
    public function verifyIdToken(string $idToken): ?User
    {
        try {
            $payload = $this->googleClient->verifyIdToken($idToken);

            if ($payload) {
                $providerId = $payload['sub'] ?? null;
                $providerName = 'google';
                $email = $payload['email'] ?? null;

                $user = null;
                if ($providerId) {
                    $user = $this->userRepository->findByProvider($providerId, $providerName);
                }
                if (!$user && $email) {
                    $user = $this->userRepository->findByEmail($email);
                }

                if (!$user) {
                    $user = $this->userRepository->create([
                        'id' => \Illuminate\Support\Str::uuid(),
                        'email' => $email,
                        'name' => $payload['name'] ?? null,
                        'picture_url' => $payload['picture'] ?? null,
                        'google_id' => $providerId,
                        'provider_id' => $providerId,
                        'provider_name' => $providerName,
                    ]);
                }

                return $user;
            }

            return null;
        } catch (Exception $e) {
            Log::error('ID Token Verification Error: ' . $e->getMessage());
            return null;
        }
    }
}