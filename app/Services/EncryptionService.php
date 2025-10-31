<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    /**
     * Encrypt password
     */
    public function encrypt(string $password): string
    {
        return Crypt::encryptString($password);
    }

    /**
     * Decrypt password
     */
    public function decrypt(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }
}