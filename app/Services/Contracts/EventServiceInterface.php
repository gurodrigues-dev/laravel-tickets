<?php

namespace App\Services\Contracts;

interface EventServiceInterface
{
    public function listEvents();
    public function createEvent(array $data);
}
