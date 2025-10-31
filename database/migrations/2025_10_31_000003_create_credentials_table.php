<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vault_id');
            $table->string('username');
            $table->longText('password_encrypted'); // encrypted password
            $table->string('url')->nullable();
            $table->timestamps();

            $table->foreign('vault_id')->references('id')->on('vaults')->onDelete('cascade');
            $table->index('vault_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};