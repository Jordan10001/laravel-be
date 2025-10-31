<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids;

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