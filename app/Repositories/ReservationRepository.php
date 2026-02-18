<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function findByUserPaginated(int $userId, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return Reservation::with('event')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findByUserAndEvent(int $userId, int $eventId)
    {
        return Reservation::where('user_id', $userId)
            ->where('event_id', $eventId)
            ->where('status', 'active')
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
