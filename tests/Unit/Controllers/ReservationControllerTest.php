<?php

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Services\Contracts\ReservationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(function () {
        Auth::shouldReceive('id')->andReturn(1);
    });

test('controller returns user reservations paginated', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $responseData = [
        'data' => [
            [
                'id' => 1,
                'event_id' => 1,
                'quantity' => 2,
                'status' => 'active',
            ],
        ],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 10,
            'total' => 1,
            'from' => 1,
            'to' => 1,
        ],
        'links' => [
            'first' => '/api/reservations?page=1',
            'last' => '/api/reservations?page=1',
            'prev' => null,
            'next' => null,
        ],
    ];

    $serviceMock->shouldReceive('listUserReservations')
        ->once()
        ->with(1, 10, 1)
        ->andReturn($responseData);

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/reservations/my-reservations', 'GET', ['page' => 1, 'per_page' => 10]);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->myReservations($request);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['data'])->toHaveCount(1);
});

test('controller creates reservation with valid data', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $reservationData = [
        'id' => 1,
        'event_id' => 1,
        'user_id' => 1,
        'quantity' => 2,
        'status' => 'active',
        'event' => ['id' => 1, 'name' => 'Test Event'],
    ];

    $serviceMock->shouldReceive('createReservation')
        ->once()
        ->with(1, 1, 2, 1)
        ->andReturn($reservationData);

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $request = StoreReservationRequest::create('/api/v1/reservations', 'POST', [
        'event_id' => 1,
        'quantity' => 2,
        'version' => 1,
    ]);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->store($request);

    expect($response->status())->toBe(201)
        ->and($response->getData(true)['id'])->toBe(1);
});

test('controller validates reservation creation', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $serviceMock->shouldReceive('createReservation')
        ->once()
        ->with(1, 1, 0, 1)
        ->andThrow(new \Exception('Quantity must be at least 1', 422));

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $request = StoreReservationRequest::create('/api/v1/reservations', 'POST', [
        'event_id' => 1,
        'quantity' => 0,
        'version' => 1,
    ]);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->store($request);

    expect($response->status())->toBe(422)
        ->and($response->getData(true)['message'])->toBe('Quantity must be at least 1');
});

test('controller updates reservation quantity', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $updatedReservation = [
        'id' => 1,
        'event_id' => 1,
        'user_id' => 1,
        'quantity' => 5,
        'status' => 'active',
    ];

    $serviceMock->shouldReceive('updateReservation')
        ->once()
        ->with(1, 5, 2, 1)
        ->andReturn($updatedReservation);

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $request = UpdateReservationRequest::create('/api/v1/reservations/1', 'PUT', [
        'quantity' => 5,
        'version' => 2,
    ]);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->update($request, 1);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['quantity'])->toBe(5);
});

test('controller validates update data', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $serviceMock->shouldReceive('updateReservation')
        ->once()
        ->with(1, 0, 1, 1)
        ->andThrow(new \Exception('Quantity must be at least 1', 422));

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $request = UpdateReservationRequest::create('/api/v1/reservations/1', 'PUT', [
        'quantity' => 0,
        'version' => 1,
    ]);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->update($request, 1);

    expect($response->status())->toBe(422)
        ->and($response->getData(true)['message'])->toBe('Quantity must be at least 1');
});

test('controller cancels reservation', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $serviceMock->shouldReceive('cancelReservation')
        ->once()
        ->with(1);

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->destroy(1);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['message'])->toBe('Cancelled');
});

test('controller handles reservation not found', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $serviceMock->shouldReceive('cancelReservation')
        ->once()
        ->with(1)
        ->andThrow(new \Exception('Reservation not found', 404));

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->destroy(1);

    expect($response->status())->toBe(404)
        ->and($response->getData(true)['message'])->toBe('Reservation not found');
});

test('controller handles version conflict', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $serviceMock->shouldReceive('createReservation')
        ->once()
        ->with(1, 1, 2, 1)
        ->andThrow(new \Exception('Version conflict', 409));

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $request = StoreReservationRequest::create('/api/v1/reservations', 'POST', [
        'event_id' => 1,
        'quantity' => 2,
        'version' => 1,
    ]);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->store($request);

    expect($response->status())->toBe(409)
        ->and($response->getData(true)['message'])->toBe('Version conflict');
});

test('controller handles pagination limits', function () {
    $serviceMock = Mockery::mock(ReservationServiceInterface::class);

    $responseData = [
        'data' => [],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 100,
            'total' => 0,
            'from' => null,
            'to' => null,
        ],
        'links' => [
            'first' => '/api/reservations?page=1',
            'last' => '/api/reservations?page=1',
            'prev' => null,
            'next' => null,
        ],
    ];

    $serviceMock->shouldReceive('listUserReservations')
        ->once()
        ->with(1, 100, 1)
        ->andReturn($responseData);

    app()->instance(ReservationServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/reservations/my-reservations', 'GET', ['page' => 1, 'per_page' => 200]);

    $controller = new \App\Http\Controllers\Api\ReservationController($serviceMock);
    $response = $controller->myReservations($request);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['meta']['per_page'])->toBe(100);
});
