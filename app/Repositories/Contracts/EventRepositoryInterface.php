<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    public function all();

    public function paginate(int $perPage = 10, int $page = 1): LengthAwarePaginator;

    public function findById(int $id): ?Event;

    public function create(array $data): Event;

    public function updateWithVersion(
        int $eventId,
        int $expectedVersion,
        int $newAvailableTickets
    ): bool;
}
