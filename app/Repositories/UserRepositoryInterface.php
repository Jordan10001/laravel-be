<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByGoogleId(string $googleId): ?User;
    public function findByProvider(string $providerId, string $providerName): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function findById(string $id): ?User;
}