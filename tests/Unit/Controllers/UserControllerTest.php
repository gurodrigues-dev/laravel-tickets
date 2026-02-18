<?php

use App\Models\User;
use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);

test('controller returns user profile', function () {
    $serviceMock = Mockery::mock(UserServiceInterface::class);
    $user = User::factory()->make(['id' => 1]);

    $serviceMock->shouldReceive('get')
        ->once()
        ->with(1)
        ->andReturn($user);

    app()->instance(UserServiceInterface::class, $serviceMock);

    $controller = new \App\Http\Controllers\Api\UserController($serviceMock);
    $response = $controller->show(1);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['id'])->toBe(1);
});

test('controller updates user profile', function () {
    $serviceMock = Mockery::mock(UserServiceInterface::class);
    $user = User::factory()->make(['id' => 1, 'name' => 'Updated Name']);

    $serviceMock->shouldReceive('update')
        ->once()
        ->with(1, ['name' => 'Updated Name'])
        ->andReturn($user);

    app()->instance(UserServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/users/1', 'PUT', ['name' => 'Updated Name']);

    $controller = new \App\Http\Controllers\Api\UserController($serviceMock);
    $response = $controller->update($request, 1);

    expect($response->status())->toBe(200)
        ->and($response->getData(true)['name'])->toBe('Updated Name');
});

test('controller deletes user account', function () {
    $serviceMock = Mockery::mock(UserServiceInterface::class);

    $serviceMock->shouldReceive('delete')
        ->once()
        ->with(1)
        ->andReturn(true);

    app()->instance(UserServiceInterface::class, $serviceMock);

    $controller = new \App\Http\Controllers\Api\UserController($serviceMock);
    $response = $controller->destroy(1);

    expect($response->status())->toBe(204);
});

test('controller returns all users', function () {
    $serviceMock = Mockery::mock(UserServiceInterface::class);
    $users = collect([
        User::factory()->make(['id' => 1]),
        User::factory()->make(['id' => 2]),
    ]);

    $serviceMock->shouldReceive('list')
        ->once()
        ->andReturn($users);

    app()->instance(UserServiceInterface::class, $serviceMock);

    $controller = new \App\Http\Controllers\Api\UserController($serviceMock);
    $response = $controller->index();

    expect($response->status())->toBe(200)
        ->and($response->getData(true))->toHaveCount(2);
});

test('controller creates new user', function () {
    $serviceMock = Mockery::mock(UserServiceInterface::class);
    $user = User::factory()->make([
        'id' => 1,
        'name' => 'New User',
        'email' => 'new@example.com',
    ]);

    $serviceMock->shouldReceive('create')
        ->once()
        ->with([
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
        ])
        ->andReturn($user);

    app()->instance(UserServiceInterface::class, $serviceMock);

    $request = Request::create('/api/v1/register', 'POST', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password123',
    ]);

    $controller = new \App\Http\Controllers\Api\UserController($serviceMock);
    $response = $controller->store($request);

    expect($response->status())->toBe(201)
        ->and($response->getData(true)['id'])->toBe(1);
});
