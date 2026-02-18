<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Models\User;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    Route::post('/register', function (Request $request, UserServiceInterface $userService) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = $userService->create($request->all());

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
        ], 200);
    })->name('api.register');

    Route::get('/verify-email/{id}/{hash}', \App\Http\Controllers\Api\VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('api.verification.verify');

    Route::post('/auth/login', [AuthController::class, 'login'])
        ->name('api.auth.login');

    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->name('api.password.email');

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->name('api.password.reset');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])
            ->name('api.auth.logout');

        Route::get('/auth/user', [AuthController::class, 'me'])
            ->name('api.auth.user');

        Route::prefix('events')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\EventController::class, 'index'])
                ->name('api.events.index');
            Route::post('/', [\App\Http\Controllers\Api\EventController::class, 'store'])
                ->name('api.events.store');
        });

        Route::prefix('reservations')->group(function () {
            Route::get('/my-reservations', [\App\Http\Controllers\Api\ReservationController::class, 'myReservations'])
                ->name('api.reservations.my');
            Route::post('/', [\App\Http\Controllers\Api\ReservationController::class, 'store'])
                ->name('api.reservations.store');
            Route::put('/{id}', [\App\Http\Controllers\Api\ReservationController::class, 'update'])
                ->name('api.reservations.update');
            Route::delete('/{id}', [\App\Http\Controllers\Api\ReservationController::class, 'destroy'])
                ->name('api.reservations.destroy');
        });
    });
});
