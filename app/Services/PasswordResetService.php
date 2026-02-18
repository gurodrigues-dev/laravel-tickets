<?php

namespace App\Services;

use App\Services\Contracts\PasswordResetServiceInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService implements PasswordResetServiceInterface
{
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(
        string $email,
        string $password,
        string $passwordConfirmation,
        string $token
    ): bool {
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
                'token' => $token,
            ],
            function ($user) use ($password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return true;
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
