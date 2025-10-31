<?php

namespace App\Repositories;

use App\Models\Credential;
use Illuminate\Database\Eloquent\Collection;

interface CredentialRepositoryInterface
{
    public function create(array $data): Credential;
    public function findById(string $id): ?Credential;
    public function findByVault(string $vaultId): Collection;
    public function update(Credential $credential, array $data): Credential;
    public function delete(Credential $credential): bool;
}