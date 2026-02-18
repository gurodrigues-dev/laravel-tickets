<?php

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Services\EventService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

test('service can create event with valid data', function () {
    $repositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $eventData = [
        'name' => 'Test Event',
        'description' => 'A test event',
        'event_date' => now()->addDays(7),
        'total_tickets' => 100,
    ];

    $repositoryMock->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['available_tickets'] === $data['total_tickets']
                && $data['version'] === 1;
        }))
        ->andReturn(new Event($eventData));

    app()->instance(EventRepositoryInterface::class, $repositoryMock);

    $service = new EventService($repositoryMock);
    $event = $service->createEvent($eventData);

    expect($event)->toBeInstanceOf(Event::class);
});

test('service initializes available tickets to total', function () {
    $repositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $eventData = [
        'name' => 'Test Event',
        'description' => 'A test event',
        'event_date' => now()->addDays(7),
        'total_tickets' => 100,
    ];

    $repositoryMock->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['available_tickets'] === 100;
        }))
        ->andReturn(new Event($eventData));

    app()->instance(EventRepositoryInterface::class, $repositoryMock);

    $service = new EventService($repositoryMock);
    $service->createEvent($eventData);
});

test('service sets initial version to one', function () {
    $repositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $eventData = [
        'name' => 'Test Event',
        'description' => 'A test event',
        'event_date' => now()->addDays(7),
        'total_tickets' => 100,
    ];

    $repositoryMock->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['version'] === 1;
        }))
        ->andReturn(new Event($eventData));

    app()->instance(EventRepositoryInterface::class, $repositoryMock);

    $service = new EventService($repositoryMock);
    $service->createEvent($eventData);
});

test('service returns paginated events', function () {
    $repositoryMock = Mockery::mock(EventRepositoryInterface::class);
    $event1 = Event::factory()->make(['id' => 1]);
    $event2 = Event::factory()->make(['id' => 2]);

    $paginatorMock = Mockery::mock(LengthAwarePaginator::class);
    $paginatorMock->shouldReceive('items')->andReturn([$event1, $event2]);
    $paginatorMock->shouldReceive('currentPage')->andReturn(1);
    $paginatorMock->shouldReceive('lastPage')->andReturn(1);
    $paginatorMock->shouldReceive('perPage')->andReturn(10);
    $paginatorMock->shouldReceive('total')->andReturn(2);
    $paginatorMock->shouldReceive('firstItem')->andReturn(1);
    $paginatorMock->shouldReceive('lastItem')->andReturn(2);
    $paginatorMock->shouldReceive('url')->with(1)->andReturn('/api/events?page=1');
    $paginatorMock->shouldReceive('previousPageUrl')->andReturn(null);
    $paginatorMock->shouldReceive('nextPageUrl')->andReturn(null);

    $repositoryMock->shouldReceive('paginate')
        ->once()
        ->with(10, 1)
        ->andReturn($paginatorMock);

    app()->instance(EventRepositoryInterface::class, $repositoryMock);

    $service = new EventService($repositoryMock);
    $result = $service->listEvents(10, 1);

    expect($result)->toHaveKey('data')
        ->and($result)->toHaveKey('meta')
        ->and($result)->toHaveKey('links')
        ->and($result['data'])->toHaveCount(2);
});

test('service paginates events with correct meta data', function () {
    $repositoryMock = Mockery::mock(EventRepositoryInterface::class);

    $paginatorMock = Mockery::mock(LengthAwarePaginator::class);
    $paginatorMock->shouldReceive('items')->andReturn([]);
    $paginatorMock->shouldReceive('currentPage')->andReturn(1);
    $paginatorMock->shouldReceive('lastPage')->andReturn(5);
    $paginatorMock->shouldReceive('perPage')->andReturn(10);
    $paginatorMock->shouldReceive('total')->andReturn(50);
    $paginatorMock->shouldReceive('firstItem')->andReturn(1);
    $paginatorMock->shouldReceive('lastItem')->andReturn(10);
    $paginatorMock->shouldReceive('url')->andReturn('/api/events?page=1');
    $paginatorMock->shouldReceive('previousPageUrl')->andReturn(null);
    $paginatorMock->shouldReceive('nextPageUrl')->andReturn('/api/events?page=2');

    $repositoryMock->shouldReceive('paginate')
        ->once()
        ->with(10, 1)
        ->andReturn($paginatorMock);

    app()->instance(EventRepositoryInterface::class, $repositoryMock);

    $service = new EventService($repositoryMock);
    $result = $service->listEvents(10, 1);

    expect($result['meta']['current_page'])->toBe(1)
        ->and($result['meta']['last_page'])->toBe(5)
        ->and($result['meta']['per_page'])->toBe(10)
        ->and($result['meta']['total'])->toBe(50)
        ->and($result['meta']['from'])->toBe(1)
        ->and($result['meta']['to'])->toBe(10);
});

test('service paginates events with correct links', function () {
    $repositoryMock = Mockery::mock(EventRepositoryInterface::class);

    $paginatorMock = Mockery::mock(LengthAwarePaginator::class);
    $paginatorMock->shouldReceive('items')->andReturn([]);
    $paginatorMock->shouldReceive('currentPage')->andReturn(1);
    $paginatorMock->shouldReceive('lastPage')->andReturn(3);
    $paginatorMock->shouldReceive('perPage')->andReturn(10);
    $paginatorMock->shouldReceive('total')->andReturn(25);
    $paginatorMock->shouldReceive('firstItem')->andReturn(1);
    $paginatorMock->shouldReceive('lastItem')->andReturn(10);
    $paginatorMock->shouldReceive('url')->with(1)->andReturn('/api/events?page=1');
    $paginatorMock->shouldReceive('url')->with(3)->andReturn('/api/events?page=3');
    $paginatorMock->shouldReceive('previousPageUrl')->andReturn(null);
    $paginatorMock->shouldReceive('nextPageUrl')->andReturn('/api/events?page=2');

    $repositoryMock->shouldReceive('paginate')
        ->once()
        ->with(10, 1)
        ->andReturn($paginatorMock);

    app()->instance(EventRepositoryInterface::class, $repositoryMock);

    $service = new EventService($repositoryMock);
    $result = $service->listEvents(10, 1);

    expect($result['links']['first'])->toBe('/api/events?page=1')
        ->and($result['links']['last'])->toBe('/api/events?page=3')
        ->and($result['links']['prev'])->toBeNull()
        ->and($result['links']['next'])->toBe('/api/events?page=2');
});
