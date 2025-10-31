<?php

namespace App\Providers;

use App\Repositories\CredentialRepository;
use App\Repositories\CredentialRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\VaultRepository;
use App\Repositories\VaultRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository binding
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(VaultRepositoryInterface::class, VaultRepository::class);
        $this->app->bind(CredentialRepositoryInterface::class, CredentialRepository::class);
    }

    public function boot(): void
    {
        //
    }
}