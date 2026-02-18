<?php

namespace App\Repositories\Contracts;

use App\Models\Reservation;

interface ReservationRepositoryInterface
{
    public function findById(int $id): ?Reservation;

    public function findByUser(int $userId);

    public function findByUserAndEvent(int $userId, int $eventId);

    public function create(array $data): Reservation;

    public function update(Reservation $reservation, array $data): bool;
}
