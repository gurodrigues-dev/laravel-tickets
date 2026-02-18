<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
    public function all()
    {
        return Event::all();
    }

    public function paginate(int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Event::orderBy('event_date', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(int $id): ?Event
    {
        return Event::find($id);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function updateWithVersion(
        int $eventId,
        int $expectedVersion,
        int $newAvailableTickets
    ): bool {

        return Event::where('id', $eventId)
            ->where('version', $expectedVersion)
            ->update([
                'available_tickets' => $newAvailableTickets,
                'version' => $expectedVersion + 1,
            ]) > 0;
    }
}
