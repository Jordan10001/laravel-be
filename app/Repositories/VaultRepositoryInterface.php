<?php

namespace App\Repositories;

use App\Models\Vault;
use Illuminate\Database\Eloquent\Collection;

interface VaultRepositoryInterface
{
    public function create(array $data): Vault;
    public function findById(string $id): ?Vault;
    public function findByOwner(string $ownerId): Collection;
    public function delete(Vault $vault): bool;
    public function update(Vault $vault, array $data): Vault;
}