<?php

namespace App\Services\Contracts;

interface ReservationServiceInterface
{
    public function createReservation(
        int $eventId,
        int $userId,
        int $quantity,
        int $version
    );

    public function updateReservation(
        int $reservationId,
        int $quantity,
        int $version,
        int $userId
    );

    public function cancelReservation(int $reservationId);

    public function listUserReservations(int $userId);
}
