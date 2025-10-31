<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Google\Client;
use Google\Exception;

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
     */
    public function handleCallback(string $code): ?User
    {
        try {
            $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);
            $this->googleClient->setAccessToken($token);

            $oauth = new \Google\Service\Oauth2($this->googleClient);
            $userInfo = $oauth->userinfo->get();

            // Find or create user
            $user = $this->userRepository->findByGoogleId($userInfo->id);

            if (!$user) {
                $user = $this->userRepository->create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'email' => $userInfo->email,
                    'name' => $userInfo->name,
                    'picture_url' => $userInfo->picture,
                    'google_id' => $userInfo->id,
                ]);
            } else {
                // Update user info if needed
                $this->userRepository->update($user, [
                    'name' => $userInfo->name,
                    'picture_url' => $userInfo->picture,
                ]);
            }

            return $user;
        } catch (Exception $e) {
            \Log::error('Google Auth Error: ' . $e->getMessage());
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
                // Find or create user
                $user = $this->userRepository->findByGoogleId($payload['sub']);

                if (!$user) {
                    $user = $this->userRepository->create([
                        'id' => \Illuminate\Support\Str::uuid(),
                        'email' => $payload['email'],
                        'name' => $payload['name'] ?? null,
                        'picture_url' => $payload['picture'] ?? null,
                        'google_id' => $payload['sub'],
                    ]);
                }

                return $user;
            }

            return null;
        } catch (Exception $e) {
            \Log::error('ID Token Verification Error: ' . $e->getMessage());
            return null;
        }
    }
}