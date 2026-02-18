<?php

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class);

test('service can create user with valid data', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);
    $userData = [
        'id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ];

    $userMock = Mockery::mock(User::class);
    $userMock->shouldReceive('sendEmailVerificationNotification')->once();
    $userMock->shouldReceive('getAttribute')->andReturnUsing(function ($key) use ($userData) {
        return $userData[$key] ?? null;
    });

    $repositoryMock->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return isset($data['password']) && Hash::check('password123', $data['password']);
        }))
        ->andReturn($userMock);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $user = $service->create($userData);

    expect($user)->toBeInstanceOf(User::class);
});

test('service hashes password when creating user', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);
    $userData = [
        'id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'plainpassword',
    ];

    $userMock = Mockery::mock(User::class);
    $userMock->shouldReceive('sendEmailVerificationNotification')->once();

    $repositoryMock->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return Hash::check('plainpassword', $data['password']);
        }))
        ->andReturn($userMock);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $service->create($userData);

});

test('service can update user profile', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);
    $user = User::factory()->make(['id' => 1, 'name' => 'Original Name']);
    $updateData = ['name' => 'Updated Name'];

    $repositoryMock->shouldReceive('find')
        ->once()
        ->with(1)
        ->andReturn($user);

    $repositoryMock->shouldReceive('update')
        ->once()
        ->with(1, $updateData)
        ->andReturn($user);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $result = $service->update(1, $updateData);

    expect($result)->toBe($user);
});

test('service updates password when provided', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);
    $user = User::factory()->make(['id' => 1]);
    $updateData = ['password' => 'newpassword'];

    $repositoryMock->shouldReceive('find')
        ->once()
        ->with(1)
        ->andReturn($user);

    $repositoryMock->shouldReceive('update')
        ->once()
        ->with(1, Mockery::on(function ($data) {
            return Hash::check('newpassword', $data['password']);
        }))
        ->andReturn($user);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $service->update(1, $updateData);
});

test('service can delete user', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);

    $repositoryMock->shouldReceive('delete')
        ->once()
        ->with(1)
        ->andReturn(true);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $result = $service->delete(1);

    expect($result)->toBeTrue();
});

test('service returns user by id', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);
    $user = User::factory()->make(['id' => 1]);

    $repositoryMock->shouldReceive('find')
        ->once()
        ->with(1)
        ->andReturn($user);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $result = $service->get(1);

    expect($result)->toBe($user);
});

test('service returns list of users', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);
    $users = collect([User::factory()->make()]);

    $repositoryMock->shouldReceive('all')
        ->once()
        ->andReturn($users);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $result = $service->list();

    expect($result)->toBe($users);
});

test('service resets email verification when email is changed', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);

    $user = Mockery::mock(User::class)->makePartial();
    $user->id = 1;
    $user->email = 'old@example.com';
    $user->email_verified_at = now();

    $user->shouldReceive('sendEmailVerificationNotification')->once();
    $user->shouldReceive('save')->once();
    $user->shouldReceive('getAttribute')->with('email')->andReturn('old@example.com');

    $repositoryMock->shouldReceive('find')
        ->once()
        ->with(1)
        ->andReturn($user);

    $updateData = ['email' => 'new@example.com'];

    $repositoryMock->shouldReceive('update')
        ->once()
        ->with(1, $updateData)
        ->andReturn($user);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $result = $service->update(1, $updateData);

    expect($result->email_verified_at)->toBeNull();
});

test('service keeps email verification when email is not changed', function () {
    $repositoryMock = Mockery::mock(UserRepositoryInterface::class);
    $user = User::factory()->make([
        'id' => 1,
        'email' => 'same@example.com',
        'email_verified_at' => now(),
    ]);

    $repositoryMock->shouldReceive('find')
        ->once()
        ->with(1)
        ->andReturn($user);

    $updateData = ['name' => 'New Name'];

    $repositoryMock->shouldReceive('update')
        ->once()
        ->with(1, $updateData)
        ->andReturn($user);

    app()->instance(UserRepositoryInterface::class, $repositoryMock);

    $service = new UserService($repositoryMock);
    $result = $service->update(1, $updateData);

    expect($result->email_verified_at)->not->toBeNull();
});
