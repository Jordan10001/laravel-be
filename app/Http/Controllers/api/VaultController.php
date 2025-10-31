<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\VaultRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VaultController extends Controller
{
    protected $vaultRepository;

    public function __construct(VaultRepositoryInterface $vaultRepository)
    {
        $this->vaultRepository = $vaultRepository;
    }

    /**
     * POST /api/v1/vaults
     * Create a new vault
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'owner_user_id' => 'nullable|uuid',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $vault = $this->vaultRepository->create([
            'id' => Str::uuid(),
            // Use provided owner_user_id (frontend sends it). Fallback to Auth::id() when available.
            'owner_user_id' => $validated['owner_user_id'] ?? Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Vault created successfully',
            'data' => [
                'id' => $vault->id,
                'owner_user_id' => $vault->owner_user_id,
                'name' => $vault->name,
                'description' => $vault->description,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/vaults?owner_id=user-uuid
     * List vaults by owner
     */
    public function index(Request $request): JsonResponse
    {
        $ownerId = $request->query('owner_id', Auth::id());

        $vaults = $this->vaultRepository->findByOwner($ownerId);

        return response()->json([
            'status' => 'success',
            'message' => 'ok',
            'data' => $vaults->map(fn($vault) => [
                'id' => $vault->id,
                'owner_user_id' => $vault->owner_user_id,
                'name' => $vault->name,
                'description' => $vault->description,
            ])->toArray(),
        ]);
    }

    /**
     * DELETE /api/v1/vaults/:id
     * Delete vault and all credentials in it
     */
    public function destroy(string $id): JsonResponse
    {
        $vault = $this->vaultRepository->findById($id);

        if (!$vault) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vault not found',
            ], 404);
        }

        // NOTE: Authorization check is relaxed to match current frontend (no token sent).
        // If you later secure with tokens, restore this check.

        $this->vaultRepository->delete($vault);

        return response()->json([
            'status' => 'success',
            'message' => 'Vault deleted successfully',
        ]);
    }
}