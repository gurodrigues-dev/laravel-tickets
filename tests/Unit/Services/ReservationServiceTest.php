<?php

use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Services\ReservationService;
use Exception;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

test('service creates reservation with valid data', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $event = Event::factory()->make([
        'id' => 1,
        'available_tickets' => 100,
        'version' => 1,
        'max_tickets_per_user' => 5,
    ]);

    $reservation = Reservation::factory()->make([
        'id' => 1,
        'event_id' => 1,
        'user_id' => 1,
        'quantity' => 5,
        'status' => 'active',
    ]);
    $reservationWithEvent = Reservation::factory()->make([
        'id' => 1,
        'event_id' => 1,
        'user_id' => 1,
        'quantity' => 5,
        'status' => 'active',
    ]);
    $reservationWithEvent->setRelation('event', $event);

    $eventRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($event);

    $eventRepositoryMock->shouldReceive('updateWithVersion')
        ->once()
        ->with(1, 1, 95)
        ->andReturn(true);

    $reservationRepositoryMock->shouldReceive('create')
        ->once()
        ->with([
            'event_id' => 1,
            'user_id' => 1,
            'quantity' => 5,
            'status' => 'active',
        ])
        ->andReturn($reservation);

    $reservationRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($reservationWithEvent);

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);
    $result = $service->createReservation(1, 1, 5, 1);

    expect($result['id'])->toBe(1)
        ->and($result['event_id'])->toBe(1)
        ->and($result['quantity'])->toBe(5)
        ->and($result['status'])->toBe('active');
});

test('service decrements available tickets', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $event = Event::factory()->make([
        'id' => 1,
        'available_tickets' => 100,
        'version' => 1,
        'max_tickets_per_user' => 10,
    ]);

    $reservation = Reservation::factory()->make(['id' => 1]);
    $reservationWithEvent = Reservation::factory()->make(['id' => 1]);
    $reservationWithEvent->setRelation('event', $event);

    $eventRepositoryMock->shouldReceive('findById')->andReturn($event);
    $eventRepositoryMock->shouldReceive('updateWithVersion')
        ->once()
        ->with(1, 1, 90)
        ->andReturn(true);

    $reservationRepositoryMock->shouldReceive('create')->andReturn($reservation);
    $reservationRepositoryMock->shouldReceive('findById')->andReturn($reservationWithEvent);

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);
    $service->createReservation(1, 1, 10, 1);
});

test('service validates max tickets per user', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $event = Event::factory()->make([
        'id' => 1,
        'available_tickets' => 100,
        'version' => 1,
        'max_tickets_per_user' => 5,
    ]);

    $eventRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($event);

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);

    expect(fn () => $service->createReservation(1, 1, 10, 1))
        ->toThrow(Exception::class, 'You can only reserve a maximum of 5 tickets per event');
});

test('service validates enough tickets available', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $event = Event::factory()->make([
        'id' => 1,
        'available_tickets' => 3,
        'version' => 1,
        'max_tickets_per_user' => 10,
    ]);

    $eventRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($event);

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);

    expect(fn () => $service->createReservation(1, 1, 5, 1))
        ->toThrow(Exception::class, 'Not enough tickets available');
});

test('service handles version conflicts', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $event = Event::factory()->make([
        'id' => 1,
        'available_tickets' => 100,
        'version' => 2,
        'max_tickets_per_user' => 10,
    ]);

    $eventRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($event);

    $eventRepositoryMock->shouldReceive('updateWithVersion')
        ->once()
        ->with(1, 1, 90)
        ->andReturn(false);

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);

    expect(fn () => $service->createReservation(1, 1, 10, 1))
        ->toThrow(Exception::class, 'Version conflict');
});

test('service uses database transactions', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $event = Event::factory()->make([
        'id' => 1,
        'available_tickets' => 100,
        'version' => 1,
        'max_tickets_per_user' => 5,
    ]);

    $reservation = Reservation::factory()->make(['id' => 1]);
    $reservationWithEvent = Reservation::factory()->make(['id' => 1]);
    $reservationWithEvent->setRelation('event', $event);

    $eventRepositoryMock->shouldReceive('findById')->andReturn($event);
    $eventRepositoryMock->shouldReceive('updateWithVersion')->andReturn(true);
    $reservationRepositoryMock->shouldReceive('create')->andReturn($reservation);
    $reservationRepositoryMock->shouldReceive('findById')->andReturn($reservationWithEvent);

    DB::shouldReceive('transaction')
        ->once()
        ->with(Mockery::on(function ($callback) {
            return is_callable($callback);
        }))
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);
    $service->createReservation(1, 1, 5, 1);
});

test('service updates reservation quantity', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $event = Event::factory()->make([
        'id' => 1,
        'available_tickets' => 100,
        'version' => 1,
        'max_tickets_per_user' => 5,
    ]);

    $reservation = Reservation::factory()->make([
        'id' => 1,
        'user_id' => 1,
        'event_id' => 1,
        'quantity' => 2,
        'status' => 'active',
    ]);

    $reservationWithEvent = Reservation::factory()->make([
        'id' => 1,
        'quantity' => 4,
    ]);
    $reservationWithEvent->setRelation('event', $event);

    $reservationRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($reservation);

    $eventRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($event);

    $reservationRepositoryMock->shouldReceive('findByUserAndEvent')
        ->once()
        ->with(1, 1)
        ->andReturn(collect([$reservation]));

    $eventRepositoryMock->shouldReceive('updateWithVersion')
        ->once()
        ->with(1, 1, 98)
        ->andReturn(true);

    $reservationRepositoryMock->shouldReceive('update')
        ->once()
        ->with($reservation, ['quantity' => 4])
        ->andReturn(true);

    $reservationRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($reservationWithEvent);

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);
    $result = $service->updateReservation(1, 4, 1, 1);

    expect($result['quantity'])->toBe(4);
});

test('service cancels reservation and returns tickets', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $event = Event::factory()->make([
        'id' => 1,
        'available_tickets' => 100,
        'version' => 1,
    ]);

    $reservation = Reservation::factory()->make([
        'id' => 1,
        'event_id' => 1,
        'quantity' => 5,
        'status' => 'active',
    ]);

    $reservationRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($reservation);

    $eventRepositoryMock->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($event);

    $eventRepositoryMock->shouldReceive('updateWithVersion')
        ->once()
        ->with(1, 1, 105)
        ->andReturn(true);

    $reservationRepositoryMock->shouldReceive('update')
        ->once()
        ->with($reservation, ['status' => 'cancelled'])
        ->andReturn(true);

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);
    $service->cancelReservation(1);

});

test('service returns paginated user reservations', function () {
    $eventRepositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $reservationRepositoryMock = Mockery::mock(ReservationRepositoryInterface::class);

    $paginatorMock = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    $paginatorMock->shouldReceive('items')->andReturn([]);
    $paginatorMock->shouldReceive('currentPage')->andReturn(1);
    $paginatorMock->shouldReceive('lastPage')->andReturn(1);
    $paginatorMock->shouldReceive('perPage')->andReturn(10);
    $paginatorMock->shouldReceive('total')->andReturn(5);
    $paginatorMock->shouldReceive('firstItem')->andReturn(1);
    $paginatorMock->shouldReceive('lastItem')->andReturn(5);
    $paginatorMock->shouldReceive('url')->andReturn('/api/reservations?page=1');
    $paginatorMock->shouldReceive('previousPageUrl')->andReturn(null);
    $paginatorMock->shouldReceive('nextPageUrl')->andReturn(null);

    $reservationRepositoryMock->shouldReceive('findByUserPaginated')
        ->once()
        ->with(1, 10, 1)
        ->andReturn($paginatorMock);

    app()->instance(EventRepositoryInterface::class, $eventRepositoryMock);
    app()->instance(ReservationRepositoryInterface::class, $reservationRepositoryMock);

    $service = new ReservationService($eventRepositoryMock, $reservationRepositoryMock);
    $result = $service->listUserReservations(1, 10, 1);

    expect($result['meta']['total'])->toBe(5);
});
