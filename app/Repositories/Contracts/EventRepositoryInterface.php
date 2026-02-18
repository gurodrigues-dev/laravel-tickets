<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    /**
     * Get all events (deprecated - use paginate instead)
     *
     * @deprecated Use paginate() method for better performance
     */
    public function all();

    /**
     * Paginate events
     *
     * @param  int  $perPage  Number of items per page
     * @param  int  $page  Current page number
     */
    public function paginate(int $perPage = 10, int $page = 1): LengthAwarePaginator;

    public function findById(int $id): ?Event;

    public function create(array $data): Event;

    public function updateWithVersion(
        int $eventId,
        int $expectedVersion,
        int $newAvailableTickets
    ): bool;
}
