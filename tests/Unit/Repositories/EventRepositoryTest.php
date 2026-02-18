<?php

use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('event can be created', function () {
    $repository = new EventRepository;
    $eventData = [
        'name' => 'Test Event',
        'description' => 'A test event description',
        'event_date' => now()->addDays(7),
        'total_tickets' => 100,
        'available_tickets' => 100,
        'version' => 1,
    ];

    $event = $repository->create($eventData);

    expect($event)->toBeInstanceOf(Event::class)
        ->and($event->name)->toBe('Test Event')
        ->and($event->total_tickets)->toBe(100)
        ->and($event->available_tickets)->toBe(100)
        ->and($event->version)->toBe(1);
});

test('event can be found by id', function () {
    $repository = new EventRepository;
    $event = Event::factory()->create();

    $foundEvent = $repository->findById($event->id);

    expect($foundEvent)->not->toBeNull()
        ->and($foundEvent->id)->toBe($event->id)
        ->and($foundEvent->name)->toBe($event->name);
});

test('event can be found by id returns null when not found', function () {
    $repository = new EventRepository;

    $foundEvent = $repository->findById(99999);

    expect($foundEvent)->toBeNull();
});

test('event returns all events', function () {
    $repository = new EventRepository;
    Event::factory()->count(10)->create();

    $events = $repository->all();

    expect($events)->toHaveCount(10)
        ->and($events->first())->toBeInstanceOf(Event::class);
});

test('event paginates events', function () {
    $repository = new EventRepository;
    Event::factory()->count(25)->create();

    $paginator = $repository->paginate(perPage: 10, page: 1);

    expect($paginator->total())->toBe(25)
        ->and($paginator->currentPage())->toBe(1)
        ->and($paginator->perPage())->toBe(10)
        ->and($paginator->items())->toHaveCount(10);
});

test('event pagination returns second page', function () {
    $repository = new EventRepository;
    Event::factory()->count(25)->create();

    $paginator = $repository->paginate(perPage: 10, page: 2);

    expect($paginator->currentPage())->toBe(2)
        ->and($paginator->items())->toHaveCount(10);
});

test('event updates available tickets with version', function () {
    $repository = new EventRepository;
    $event = Event::factory()->create([
        'available_tickets' => 100,
        'version' => 1,
    ]);

    $updated = $repository->updateWithVersion(
        $event->id,
        1,
        90
    );

    expect($updated)->toBeTrue();

    $event->refresh();
    expect($event->available_tickets)->toBe(90)
        ->and($event->version)->toBe(2);
});

test('event update with version fails when version mismatch', function () {
    $repository = new EventRepository;
    $event = Event::factory()->create([
        'available_tickets' => 100,
        'version' => 1,
    ]);

    $updated = $repository->updateWithVersion(
        $event->id,
        2,
        90
    );

    expect($updated)->toBeFalse();

    $event->refresh();
    expect($event->available_tickets)->toBe(100)
        ->and($event->version)->toBe(1);
});
