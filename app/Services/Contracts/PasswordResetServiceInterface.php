<?php

namespace App\Services\Contracts;

interface PasswordResetServiceInterface
{
    /**
     * Send password reset link
     */
    public function sendResetLink(string $email): string;

    /**
     * Reset password with token
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetPassword(
        string $email,
        string $password,
        string $passwordConfirmation,
        string $token
    ): bool;
}
