<?php

namespace App\Repositories\Contracts;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReservationRepositoryInterface
{
    public function findById(int $id): ?Reservation;

    public function findByUser(int $userId);

    public function findByUserPaginated(int $userId, int $perPage = 10, int $page = 1): LengthAwarePaginator;

    public function findByUserAndEvent(int $userId, int $eventId);

    public function create(array $data): Reservation;

    public function update(Reservation $reservation, array $data): bool;
}
