<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Support\Facades\Hash;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}

    public function list()
    {
        return $this->repository->all();
    }

    public function get(int $id)
    {
        return $this->repository->find($id);
    }

    public function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $user = $this->repository->create($data);
        $user->sendEmailVerificationNotification();

        return $user;
    }

    public function update(int $id, array $data)
    {
        $user = $this->repository->find($id);
        $emailChanged = isset($data['email']) && $data['email'] !== $user->email;

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $updatedUser = $this->repository->update($id, $data);

        if ($emailChanged) {
            $updatedUser->email_verified_at = null;
            $updatedUser->save();
            $updatedUser->sendEmailVerificationNotification();
        }

        return $updatedUser;
    }

    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }
}
