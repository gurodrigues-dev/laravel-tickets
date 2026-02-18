<?php

use App\Models\Event;
use App\Services\Contracts\EventServiceInterface;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

test('controller returns paginated events', function () {
    $serviceMock = Mockery::mock(EventServiceInterface::class);

    $responseData = [
        'data' => [
            ['id' => 1, 'name' => 'Event 1'],
            ['id' => 2, 'name' => 'Event 2'],
        ],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 10,
            'total' => 2,
            'from' => 1,
            'to' => 2,
        ],
        'links' => [
            'first' => '/api/events?page=1',
            'last' => '/api/events?page=1',
            'prev' => null,
            'next' => null,
        ],
    ];

    $serviceMock->shouldReceive('listEvents')
        ->once()
        ->with(10, 1)
        ->andReturn($responseData);

    app()->instance(EventServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/events', 'GET', ['page' => 1, 'per_page' => 10]);

    $controller = new \App\Http\Controllers\Api\EventController($serviceMock);
    $response = $controller->index($request);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['data'])->toHaveCount(2);
});

test('controller validates pagination parameters', function () {
    $serviceMock = Mockery::mock(EventServiceInterface::class);

    $responseData = [
        'data' => [],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 1,
            'total' => 0,
            'from' => null,
            'to' => null,
        ],
        'links' => [
            'first' => '/api/events?page=1',
            'last' => '/api/events?page=1',
            'prev' => null,
            'next' => null,
        ],
    ];

    $serviceMock->shouldReceive('listEvents')
        ->once()
        ->with(1, 1)
        ->andReturn($responseData);

    app()->instance(EventServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/events', 'GET', ['page' => 0, 'per_page' => 0]);

    $controller = new \App\Http\Controllers\Api\EventController($serviceMock);
    $response = $controller->index($request);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['meta']['per_page'])->toBe(1);
});

test('controller respects max per page limit', function () {
    $serviceMock = Mockery::mock(EventServiceInterface::class);

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
            'first' => '/api/events?page=1',
            'last' => '/api/events?page=1',
            'prev' => null,
            'next' => null,
        ],
    ];

    $serviceMock->shouldReceive('listEvents')
        ->once()
        ->with(100, 1)
        ->andReturn($responseData);

    app()->instance(EventServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/events', 'GET', ['page' => 1, 'per_page' => 200]);

    $controller = new \App\Http\Controllers\Api\EventController($serviceMock);
    $response = $controller->index($request);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['meta']['per_page'])->toBe(100);
});

test('controller creates event with valid data', function () {
    $serviceMock = Mockery::mock(EventServiceInterface::class);
    $event = Event::factory()->make([
        'id' => 1,
        'name' => 'Test Event',
        'total_tickets' => 100,
    ]);

    $serviceMock->shouldReceive('createEvent')
        ->once()
        ->with([
            'name' => 'Test Event',
            'description' => 'A test event',
            'event_date' => '2024-12-15',
            'total_tickets' => 100,
        ])
        ->andReturn($event);

    app()->instance(EventServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/events', 'POST', [
        'name' => 'Test Event',
        'description' => 'A test event',
        'event_date' => '2024-12-15',
        'total_tickets' => 100,
    ]);

    $controller = new \App\Http\Controllers\Api\EventController($serviceMock);
    $response = $controller->store($request);

    expect($response->status())->toBe(201)
        ->and($response->getData(true)['id'])->toBe(1);
});

test('controller handles pagination with defaults', function () {
    $serviceMock = Mockery::mock(EventServiceInterface::class);

    $responseData = [
        'data' => [],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 10,
            'total' => 0,
            'from' => null,
            'to' => null,
        ],
        'links' => [
            'first' => '/api/events?page=1',
            'last' => '/api/events?page=1',
            'prev' => null,
            'next' => null,
        ],
    ];

    $serviceMock->shouldReceive('listEvents')
        ->once()
        ->with(10, 1)
        ->andReturn($responseData);

    app()->instance(EventServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/events', 'GET');

    $controller = new \App\Http\Controllers\Api\EventController($serviceMock);
    $response = $controller->index($request);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['meta']['per_page'])->toBe(10);
});
