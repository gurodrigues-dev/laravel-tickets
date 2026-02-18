<?php

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use App\Repositories\ReservationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('reservation can be created', function () {
    $repository = new ReservationRepository;
    $event = Event::factory()->create();
    $user = User::factory()->create();
    $reservationData = [
        'event_id' => $event->id,
        'user_id' => $user->id,
        'quantity' => 2,
        'status' => 'active',
    ];

    $reservation = $repository->create($reservationData);

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->event_id)->toBe($event->id)
        ->and($reservation->user_id)->toBe($user->id)
        ->and($reservation->quantity)->toBe(2)
        ->and($reservation->status)->toBe('active');
});

test('reservation can be found by id', function () {
    $repository = new ReservationRepository;
    $reservation = Reservation::factory()->create();

    $foundReservation = $repository->findById($reservation->id);

    expect($foundReservation)->not->toBeNull()
        ->and($foundReservation->id)->toBe($reservation->id);
});

test('reservation can be found by id returns null when not found', function () {
    $repository = new ReservationRepository;

    $foundReservation = $repository->findById(99999);

    expect($foundReservation)->toBeNull();
});

test('reservation loads event relationship', function () {
    $repository = new ReservationRepository;
    $event = Event::factory()->create(['name' => 'Test Event']);
    $reservation = Reservation::factory()->create(['event_id' => $event->id]);

    $foundReservation = $repository->findById($reservation->id);

    expect($foundReservation->event)->not->toBeNull()
        ->and($foundReservation->event->id)->toBe($event->id)
        ->and($foundReservation->event->name)->toBe('Test Event');
});

test('reservation can be found by user', function () {
    $repository = new ReservationRepository;
    $user = User::factory()->create();
    Reservation::factory()->count(3)->create(['user_id' => $user->id]);
    Reservation::factory()->count(2)->create();

    $reservations = $repository->findByUser($user->id);

    expect($reservations)->toHaveCount(3);
});

test('reservation can be found by user and event', function () {
    $repository = new ReservationRepository;
    $user = User::factory()->create();
    $event = Event::factory()->create();

    Reservation::factory()->active()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]);

    Reservation::factory()->cancelled()->create([
        'user_id' => $user->id,
        'event_id' => $event->id,
    ]);

    $reservations = $repository->findByUserAndEvent($user->id, $event->id);

    expect($reservations)->toHaveCount(1)
        ->and($reservations->first()->status)->toBe('active');
});

test('reservation updates status', function () {
    $repository = new ReservationRepository;
    $reservation = Reservation::factory()->create(['status' => 'active']);

    $updated = $repository->update($reservation, ['status' => 'cancelled']);

    expect($updated)->toBeTrue();

    $reservation->refresh();
    expect($reservation->status)->toBe('cancelled');
});

test('reservation updates quantity', function () {
    $repository = new ReservationRepository;
    $reservation = Reservation::factory()->create(['quantity' => 2]);

    $updated = $repository->update($reservation, ['quantity' => 5]);

    expect($updated)->toBeTrue();

    $reservation->refresh();
    expect($reservation->quantity)->toBe(5);
});

test('reservation paginates by user', function () {
    $repository = new ReservationRepository;
    $user = User::factory()->create();
    Reservation::factory()->count(15)->create(['user_id' => $user->id]);

    $paginator = $repository->findByUserPaginated($user->id, perPage: 10, page: 1);

    expect($paginator->total())->toBe(15)
        ->and($paginator->currentPage())->toBe(1)
        ->and($paginator->perPage())->toBe(10)
        ->and($paginator->items())->toHaveCount(10);
});
