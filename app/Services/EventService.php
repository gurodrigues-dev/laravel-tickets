<?php

namespace App\Services;

use App\Services\Contracts\EventServiceInterface;
use App\Repositories\Contracts\EventRepositoryInterface;

class EventService implements EventServiceInterface
{
    private $repository;

    public function __construct(EventRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function listEvents()
    {
        return $this->repository->all();
    }

    public function createEvent(array $data)
    {
        $data['available_tickets'] = $data['total_tickets'];
        $data['version'] = 1;

        return $this->repository->create($data);
    }
}
