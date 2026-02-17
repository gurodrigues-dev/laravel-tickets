<?php

namespace App\Services\Contracts;

interface UserServiceInterface
{
    public function list();
    public function get(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
