<?php

namespace App\Services;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Services\Contracts\ReservationServiceInterface;
use Exception;
use Illuminate\Support\Facades\DB;

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
        if ($quantity < 1) {
            throw new Exception('Quantity must be at least 1', 422);
        }

        return DB::transaction(function () use (
            $eventId,
            $userId,
            $quantity,
            $version
        ) {

            $event = $this->eventRepository->findById($eventId);

            if (! $event) {
                throw new Exception('Event not found', 404);
            }

            if ($quantity > $event->max_tickets_per_user) {
                throw new Exception(
                    "You can only reserve a maximum of {$event->max_tickets_per_user} tickets per event",
                    422
                );
            }

            if ($event->available_tickets < $quantity) {
                throw new Exception('Not enough tickets available', 409);
            }

            $updated = $this->eventRepository->updateWithVersion(
                $eventId,
                $version,
                $event->available_tickets - $quantity
            );

            if (! $updated) {
                throw new Exception('Version conflict. The event may have been modified by another user. Please refresh and try again.', 409);
            }

            $reservation = $this->reservationRepository->create([
                'event_id' => $eventId,
                'user_id' => $userId,
                'quantity' => $quantity,
                'status' => 'active',
            ]);

            $reservationWithEvent = $this->reservationRepository->findById($reservation->id);

            return [
                'id' => $reservationWithEvent->id,
                'event_id' => $reservationWithEvent->event_id,
                'user_id' => $reservationWithEvent->user_id,
                'quantity' => $reservationWithEvent->quantity,
                'status' => $reservationWithEvent->status,
                'event' => $reservationWithEvent->event,
            ];
        });
    }

    public function updateReservation(
        int $reservationId,
        int $quantity,
        int $version,
        int $userId
    ) {
        if ($quantity < 1) {
            throw new Exception('Quantity must be at least 1', 422);
        }

        return DB::transaction(function () use (
            $reservationId,
            $quantity,
            $version,
            $userId
        ) {

            $reservation = $this->reservationRepository->findById($reservationId);
            if (! $reservation) {
                throw new Exception('Reservation not found', 404);
            }

            if ($reservation->user_id !== $userId) {
                throw new Exception('You are not authorized to update this reservation', 403);
            }

            $event = $this->eventRepository->findById($reservation->event_id);
            if (! $event) {
                throw new Exception('Event not found', 404);
            }

            $userReservations = $this->reservationRepository->findByUserAndEvent($userId, $event->id);

            $totalOtherTickets = $userReservations
                ->where('id', '!=', $reservation->id)
                ->sum('quantity');

            if ($quantity > $event->max_tickets_per_user) {
                throw new Exception(
                    "You can only reserve a maximum of {$event->max_tickets_per_user} tickets per event",
                    422
                );
            }

            $totalTickets = $totalOtherTickets + $quantity;
            if ($totalTickets > $event->max_tickets_per_user) {
                throw new Exception(
                    "You already have {$totalOtherTickets} ticket(s) for this event. You can only reserve a maximum of {$event->max_tickets_per_user} tickets per event.",
                    422
                );
            }

            $difference = $quantity - $reservation->quantity;

            if ($difference > 0 && $event->available_tickets < $difference) {
                throw new Exception('Not enough tickets available', 409);
            }

            $updated = $this->eventRepository->updateWithVersion(
                $event->id,
                $version,
                $event->available_tickets - $difference
            );

            if (! $updated) {
                throw new Exception('Version conflict. The event may have been modified by another user. Please refresh and try again.', 409);
            }

            $this->reservationRepository->update($reservation, [
                'quantity' => $quantity,
            ]);

            $updatedReservation = $this->reservationRepository->findById($reservationId);

            return [
                'id' => $updatedReservation->id,
                'event_id' => $updatedReservation->event_id,
                'user_id' => $updatedReservation->user_id,
                'quantity' => $updatedReservation->quantity,
                'status' => $updatedReservation->status,
                'event' => $updatedReservation->event,
            ];
        });
    }

    public function cancelReservation(int $reservationId)
    {
        return DB::transaction(function () use ($reservationId) {

            $reservation = $this->reservationRepository->findById($reservationId);
            if (! $reservation) {
                throw new Exception('Reservation not found', 404);
            }

            $event = $this->eventRepository->findById($reservation->event_id);

            $this->eventRepository->updateWithVersion(
                $event->id,
                $event->version,
                $event->available_tickets + $reservation->quantity
            );

            $this->reservationRepository->update($reservation, [
                'status' => 'cancelled',
            ]);
        });
    }

    public function listUserReservations(int $userId)
    {
        return $this->reservationRepository->findByUser($userId);
    }
}
