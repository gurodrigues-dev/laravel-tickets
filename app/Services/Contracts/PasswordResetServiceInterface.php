<?php

namespace App\Services\Contracts;

interface PasswordResetServiceInterface
{
    public function sendResetLink(string $email): string;

    public function resetPassword(
        string $email,
        string $password,
        string $passwordConfirmation,
        string $token
    ): bool;
}
