<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\CredentialRepositoryInterface;
use App\Repositories\VaultRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CredentialController extends Controller
{
    protected $credentialRepository;
    protected $vaultRepository;

    public function __construct(
        CredentialRepositoryInterface $credentialRepository,
        VaultRepositoryInterface $vaultRepository
    ) {
        $this->credentialRepository = $credentialRepository;
        $this->vaultRepository = $vaultRepository;
    }

    /**
     * POST /api/v1/credentials
     * Create a new credential
     * Response MUST match Go backend format exactly
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vault_id' => 'required|string|exists:vaults,id',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'url' => 'nullable|string|max:255',
        ]);

        // Get vault (no auth check - same as VaultController pattern)
        $vault = $this->vaultRepository->findById($validated['vault_id']);

        $credential = $this->credentialRepository->create([
            'id' => Str::uuid(),
            'vault_id' => $validated['vault_id'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'url' => $validated['url'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Credential created successfully',
            'data' => [
                'id' => $credential->id,
                'vault_id' => $credential->vault_id,
                'username' => $credential->username,
                'password' => decrypt($credential->password_encrypted),
                'url' => $credential->url,
                'created_at' => $credential->created_at->toIso8601String(),
                'updated_at' => $credential->updated_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * GET /api/v1/vaults/:vault_id/credentials
     * List credentials by vault
     */
    public function index(string $vaultId): JsonResponse
    {
        $vault = $this->vaultRepository->findById($vaultId);

        if (!$vault) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vault not found',
            ], 404);
        }

        // No auth check - same as VaultController pattern
        $credentials = $this->credentialRepository->findByVault($vaultId);

        return response()->json([
            'status' => 'success',
            'message' => 'ok',
            'data' => $credentials->map(fn($cred) => [
                'id' => $cred->id,
                'vault_id' => $cred->vault_id,
                'username' => $cred->username,
                'password' => decrypt($cred->password_encrypted),
                'url' => $cred->url,
                'created_at' => $cred->created_at->toIso8601String(),
                'updated_at' => $cred->updated_at->toIso8601String(),
            ])->toArray(),
        ]);
    }

    /**
     * GET /api/v1/credentials/:id
     * Get credential by ID
     */
    public function show(string $id): JsonResponse
    {
        $credential = $this->credentialRepository->findById($id);

        if (!$credential) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credential not found',
            ], 404);
        }

        // No auth check - same as VaultController pattern

        return response()->json([
            'status' => 'success',
            'message' => 'ok',
            'data' => [
                'id' => $credential->id,
                'vault_id' => $credential->vault_id,
                'username' => $credential->username,
                'password' => decrypt($credential->password_encrypted),
                'url' => $credential->url,
                'created_at' => $credential->created_at->toIso8601String(),
                'updated_at' => $credential->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * PUT /api/v1/credentials/:id
     * Update credential
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $credential = $this->credentialRepository->findById($id);

        if (!$credential) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credential not found',
            ], 404);
        }

        // No auth check - same as VaultController pattern

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255',
            'password' => 'sometimes|string',
            'url' => 'nullable|string|max:255',
        ]);

        $credential = $this->credentialRepository->update($credential, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Credential updated successfully',
            'data' => [
                'id' => $credential->id,
                'vault_id' => $credential->vault_id,
                'username' => $credential->username,
                'password' => decrypt($credential->password_encrypted),
                'url' => $credential->url,
                'created_at' => $credential->created_at->toIso8601String(),
                'updated_at' => $credential->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * DELETE /api/v1/credentials/:id
     * Delete credential
     */
    public function destroy(string $id): JsonResponse
    {
        $credential = $this->credentialRepository->findById($id);

        if (!$credential) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credential not found',
            ], 404);
        }

        // No auth check - same as VaultController pattern
        $this->credentialRepository->delete($credential);

        return response()->json([
            'status' => 'success',
            'message' => 'Credential deleted successfully',
        ]);
    }
}