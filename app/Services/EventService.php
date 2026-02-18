<?php

namespace App\Services;

use App\Repositories\Contracts\EventRepositoryInterface;
use App\Services\Contracts\EventServiceInterface;

class EventService implements EventServiceInterface
{
    private $repository;

    public function __construct(EventRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List events with pagination
     *
     * @param  int  $perPage  Number of items per page (default: 10)
     * @param  int  $page  Current page number (default: 1)
     * @return array Paginated response with data, meta, and links
     */
    public function listEvents(int $perPage = 10, int $page = 1): array
    {
        $paginator = $this->repository->paginate($perPage, $page);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }

    public function createEvent(array $data)
    {
        $data['available_tickets'] = $data['total_tickets'];
        $data['version'] = 1;

        return $this->repository->create($data);
    }
}
