<?php

namespace App\Services\Contracts;

interface AuthServiceInterface
{
    /**
     * Authenticate user with credentials
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(string $email, string $password, bool $remember): array;

    /**
     * Logout authenticated user
     */
    public function logout(\Illuminate\Http\Request $request): void;

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(\Illuminate\Http\Request $request): ?array;
}
