<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Repositories\Contracts\ReservationRepositoryInterface;

class ReservationRepository implements ReservationRepositoryInterface
{
    public function findById(int $id): ?Reservation
    {
        return Reservation::with('event')->find($id);
    }

    public function findByUser(int $userId)
    {
        return Reservation::with('event')
            ->where('user_id', $userId)
            ->get();
    }

    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    public function update(Reservation $reservation, array $data): bool
    {
        return $reservation->update($data);
    }
}
