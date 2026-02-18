<?php

namespace App\Services\Contracts;

interface EventServiceInterface
{
    public function listEvents(int $perPage = 10, int $page = 1): array;

    public function createEvent(array $data);
}
