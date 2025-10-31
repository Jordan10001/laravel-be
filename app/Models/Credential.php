<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'vault_id',
        'username',
        'password_encrypted',
        'url',
    ];

    protected $hidden = [
        'password_encrypted',
    ];

    public function vault()
    {
        return $this->belongsTo(Vault::class);
    }

    public function getPasswordAttribute()
    {
        if (!$this->password_encrypted) {
            return null;
        }
        return decrypt($this->password_encrypted);
    }
}