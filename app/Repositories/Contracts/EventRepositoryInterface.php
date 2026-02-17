<?php

namespace App\Repositories\Contracts;

use App\Models\Event;

interface EventRepositoryInterface
{
    public function all();

    public function findById(int $id): ?Event;

    public function create(array $data): Event;

    public function updateWithVersion(
        int $eventId,
        int $expectedVersion,
        int $newAvailableTickets
    ): bool;
}
