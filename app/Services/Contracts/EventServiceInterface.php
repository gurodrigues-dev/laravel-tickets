<?php

namespace App\Services\Contracts;

interface EventServiceInterface
{
    /**
     * List events with pagination
     *
     * @param  int  $perPage  Number of items per page (default: 10)
     * @param  int  $page  Current page number (default: 1)
     * @return array Paginated response with data, meta, and links
     */
    public function listEvents(int $perPage = 10, int $page = 1): array;

    public function createEvent(array $data);
}
