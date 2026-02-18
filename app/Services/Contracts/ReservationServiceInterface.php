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

    /**
     * List user reservations with pagination
     *
     * @param  int  $userId  User ID
     * @param  int  $perPage  Number of items per page (default: 10)
     * @param  int  $page  Current page number (default: 1)
     * @return array Paginated response with data, meta, and links
     */
    public function listUserReservations(int $userId, int $perPage = 10, int $page = 1): array;
}
