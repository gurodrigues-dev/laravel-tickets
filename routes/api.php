<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Models\User;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes (No authentication required)
|--------------------------------------------------------------------------
*/

Route::post('/register', function (Request $request, UserServiceInterface $userService) {
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = $userService->create($request->all());

    return response()->json([
        'message' => 'Successfully registered',
        'user' => $user,
    ], 201);
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

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication required)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Protected Routes - Session/Token Authentication (Hybrid)
|--------------------------------------------------------------------------
| These routes work with both session cookies (web) and Sanctum tokens (API).
| The 'api' middleware group (from RouteServiceProvider) already includes
| EnsureFrontendRequestsAreStateful middleware which allows session auth.
| The 'auth:sanctum' allows both session and token authentication.
*/
Route::middleware(['auth:sanctum'])->group(function () {
    // Authentication logout
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('api.auth.logout');

    // Get authenticated user information
    Route::get('/auth/user', [AuthController::class, 'me'])
        ->name('api.auth.user');

    /*
    |--------------------------------------------------------------------------
    | Event Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('events')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\EventController::class, 'index'])
            ->name('api.events.index');
        Route::post('/', [\App\Http\Controllers\Api\EventController::class, 'store'])
            ->name('api.events.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Reservation Routes
    |--------------------------------------------------------------------------
    */
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
