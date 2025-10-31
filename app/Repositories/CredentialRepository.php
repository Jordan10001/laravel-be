<?php

namespace App\Repositories;

use App\Models\Credential;
use Illuminate\Database\Eloquent\Collection;

class CredentialRepository implements CredentialRepositoryInterface
{
    public function create(array $data): Credential
    {
        // Encrypt password before saving
        if (isset($data['password'])) {
            $data['password_encrypted'] = encrypt($data['password']);
            unset($data['password']);
        }
        return Credential::create($data);
    }

    public function findById(string $id): ?Credential
    {
        return Credential::find($id);
    }

    public function findByVault(string $vaultId): Collection
    {
        return Credential::where('vault_id', $vaultId)->get();
    }

    public function update(Credential $credential, array $data): Credential
    {
        if (isset($data['password'])) {
            $data['password_encrypted'] = encrypt($data['password']);
            unset($data['password']);
        }
        $credential->update($data);
        return $credential;
    }

    public function delete(Credential $credential): bool
    {
        return $credential->delete();
    }
}