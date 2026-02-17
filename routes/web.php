<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\EventController;
use App\Http\Controllers\ReservationController;

// API routes for data
Route::middleware(['auth'])->group(function () {
    Route::get('/api/events', [EventController::class, 'index']);
    Route::post('/api/reservations', [ReservationController::class, 'store']);
    Route::get('/api/my-reservations', [ReservationController::class, 'myReservations']);
    Route::put('/api/reservations/{id}', [ReservationController::class, 'update']);
    Route::delete('/api/reservations/{id}', [ReservationController::class, 'destroy']);
});

// Inertia page routes
Route::middleware(['auth'])->group(function () {
    Route::get('/events', function () {
        return Inertia::render('Events/Index');
    })->name('events.index');

    Route::get('/my-reservations', function () {
        return Inertia::render('Reservations/Index');
    })->name('reservations.my');
});

require __DIR__.'/auth.php';
