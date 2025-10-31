<?php

namespace App\Repositories;

use App\Models\Vault;
use Illuminate\Database\Eloquent\Collection;

class VaultRepository implements VaultRepositoryInterface
{
    public function create(array $data): Vault
    {
        return Vault::create($data);
    }

    public function findById(string $id): ?Vault
    {
        return Vault::find($id);
    }

    public function findByOwner(string $ownerId): Collection
    {
        return Vault::where('owner_user_id', $ownerId)->get();
    }

    public function delete(Vault $vault): bool
    {
        return $vault->delete();
    }

    public function update(Vault $vault, array $data): Vault
    {
        $vault->update($data);
        return $vault;
    }
}