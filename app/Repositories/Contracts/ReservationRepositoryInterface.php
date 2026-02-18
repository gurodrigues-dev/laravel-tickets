<?php

namespace App\Repositories\Contracts;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReservationRepositoryInterface
{
    public function findById(int $id): ?Reservation;

    /**
     * Find reservations by user (deprecated - use findByUserPaginated instead)
     *
     * @deprecated Use findByUserPaginated() method for better performance
     */
    public function findByUser(int $userId);

    /**
     * Paginate user reservations
     *
     * @param  int  $userId  User ID
     * @param  int  $perPage  Number of items per page
     * @param  int  $page  Current page number
     */
    public function findByUserPaginated(int $userId, int $perPage = 10, int $page = 1): LengthAwarePaginator;

    public function findByUserAndEvent(int $userId, int $eventId);

    public function create(array $data): Reservation;

    public function update(Reservation $reservation, array $data): bool;
}
