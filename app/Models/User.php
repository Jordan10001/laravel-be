<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * User model
 * NOTE: We DO NOT use HasApiTokens/Sanctum because we don't store tokens in DB
 * We use Google's access token directly (matches Go backend behavior)
 */
class User extends Authenticatable
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'email',
        'name',
        'picture_url',
        'google_id',
        'provider_id',
        'provider_name',
    ];

    protected $hidden = [
        'google_id',
    ];

    public function vaults()
    {
        return $this->hasMany(Vault::class, 'owner_user_id');
    }
}