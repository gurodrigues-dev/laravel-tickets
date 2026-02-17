<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\Contracts\ReservationServiceInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;

class ReservationService implements ReservationServiceInterface
{
    private $eventRepository;
    private $reservationRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        ReservationRepositoryInterface $reservationRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->reservationRepository = $reservationRepository;
    }

    public function createReservation(
        int $eventId,
        int $userId,
        int $quantity,
        int $version
    ) {
        return DB::transaction(function () use (
            $eventId,
            $userId,
            $quantity,
            $version
        ) {

            $event = $this->eventRepository->findById($eventId);

            if (!$event) throw new Exception("Event not found", 404);

            if ($event->available_tickets < $quantity)
                throw new Exception("Not enough tickets available", 409);

            $updated = $this->eventRepository->updateWithVersion(
                $eventId,
                $version,
                $event->available_tickets - $quantity
            );

            if (!$updated) throw new Exception("Version conflict", 409);

            return $this->reservationRepository->create([
                'event_id' => $eventId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'status' => 'active'
            ]);
        });
    }

    public function updateReservation(
        int $reservationId,
        int $quantity,
        int $version
    ) {
        return DB::transaction(function () use (
            $reservationId,
            $quantity,
            $version
        ) {

            $reservation = $this->reservationRepository->findById($reservationId);
            if (!$reservation) throw new Exception("Reservation not found", 404);

            $event = $this->eventRepository->findById($reservation->event_id);

            $difference = $quantity - $reservation->quantity;

            if ($difference > 0 && $event->available_tickets < $difference)
                throw new Exception("Not enough tickets available", 409);

            $updated = $this->eventRepository->updateWithVersion(
                $event->id,
                $version,
                $event->available_tickets - $difference
            );

            if (!$updated) throw new Exception("Version conflict", 409);

            $this->reservationRepository->update($reservation, [
                'quantity' => $quantity
            ]);
        });
    }

    public function cancelReservation(int $reservationId)
    {
        return DB::transaction(function () use ($reservationId) {

            $reservation = $this->reservationRepository->findById($reservationId);
            if (!$reservation) throw new Exception("Reservation not found", 404);

            $event = $this->eventRepository->findById($reservation->event_id);

            $this->eventRepository->updateWithVersion(
                $event->id,
                $event->version,
                $event->available_tickets + $reservation->quantity
            );

            $this->reservationRepository->update($reservation, [
                'status' => 'cancelled'
            ]);
        });
    }

    public function listUserReservations(int $userId)
    {
        return $this->reservationRepository->findByUser($userId);
    }
}
