<?php

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
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

// User registration
Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    return response()->json([
        'message' => 'Successfully registered',
        'user' => $user,
    ], 201);
})->name('api.register');

// Authentication login
Route::post('/auth/login', [AuthController::class, 'login'])
    ->name('api.auth.login');

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Authentication logout
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('api.auth.logout');

    // Get authenticated user information
    Route::get('/auth/user', [AuthController::class, 'me'])
        ->name('api.auth.user');
});
